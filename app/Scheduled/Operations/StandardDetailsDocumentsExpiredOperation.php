<?php

namespace App\Scheduled\Operations;

use App\Mail\Company\CompanyDocRenewalMulti;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocCategory;
use App\Models\Company\CompanyDocReview;
use App\Models\Misc\Action;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledReportMailer;
use App\Scheduled\ScheduledDynamicRecipientContext;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StandardDetailsDocumentsExpiredOperation implements ScheduledOperationHandler
{
    private const SYSTEM_USER_ID = 1;

    public function __construct(
        private ScheduledReportMailer $mailer,
        private ScheduledDynamicRecipientResolver $recipientResolver,
        private ScheduledDynamicRecipientContext $recipientContext
    ) {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.expired_standard_details',
            'name' => 'Process expired Standard Details',
            'category' => 'documents',
            'description' => 'Adds expired Standard Details documents to the renewal-review cycle, assigns the reviewer ToDo, records the audit Action and emails a renewal summary.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'Reviewer user 465 through the assigned ToDo plus dashboard-configurable renewal-summary To/CC recipients',
            'dynamicRecipients' => [
                ['key' => 'standard_details_reviewer', 'label' => 'Assigned Standard Details Reviewer', 'delivery' => 'to', 'description' => 'The reviewer assigned to the individual Standard Details renewal ToDo.', 'required' => true],
            ],
            'managedRecipientRuleRequired' => true,
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $company = Company::findOrFail(3);
        $reviewer = User::query()->whereKey(465)->where('status', 1)->first(); // Nadia
        if (!$reviewer) throw new RuntimeException('The configured Standard Details reviewer user [465] is missing or inactive.');

        $categoryIds = CompanyDocCategory::query()->where('parent', 22)->pluck('id')->push(22)->unique();
        $documents = CompanyDoc::query()->whereIn('category_id', $categoryIds)->where('status', 1)->whereDate('expiry', '<', Carbon::today())
            ->orderBy('expiry')->orderBy('name')->get();
        $existingReviewDocumentIds = CompanyDocReview::query()->whereIn('doc_id', $documents->pluck('id'))->pluck('doc_id')->map(fn($id) => (int)$id)->flip();
        $newRenewals = collect();

        echo "Expired active Standard Details documents found: {$documents->count()}.\n";

        foreach ($documents as $document) {
            if ($existingReviewDocumentIds->has((int)$document->id)) {
                echo "Already in renewal cycle: [{$document->id}] {$document->name}, expired {$document->expiry->format('d/m/Y')}.\n";
                continue;
            }

            $review = DB::transaction(function () use ($document, $reviewer) {
                $existing = CompanyDocReview::query()->where('doc_id', $document->id)->lockForUpdate()->first();
                if ($existing) return null;

                $review = CompanyDocReview::create([
                    'doc_id' => $document->id, 'name' => $document->name, 'stage' => 1, 'original_doc' => $document->attachment,
                    'status' => 1, 'created_by' => self::SYSTEM_USER_ID, 'updated_by' => self::SYSTEM_USER_ID,
                ]);
                $dynamicRecipients = $this->recipientResolver->user(
                    'standard_details_reviewer',
                    'Assigned Standard Details Reviewer',
                    $reviewer,
                    'to'
                );
                $this->recipientContext->run(
                    $dynamicRecipients,
                    fn() => $review->createAssignToDo($reviewer->id)
                );
                Action::create([
                    'action' => 'Standard Details review initiated', 'table' => 'company_docs_review', 'table_id' => $review->id,
                    'created_by' => self::SYSTEM_USER_ID, 'updated_by' => self::SYSTEM_USER_ID,
                ]);

                return $review;
            });

            if (!$review) {
                echo "Skipped [{$document->id}] {$document->name}: another run already added it to the renewal cycle.\n";
                continue;
            }

            $newRenewals->push($document);
            $existingReviewDocumentIds->put((int)$document->id, true);
            echo "Added to renewal cycle: [{$document->id}] {$document->name}, expired {$document->expiry->format('d/m/Y')}; ToDo assigned to {$reviewer->fullname}.\n";
        }

        if ($newRenewals->isNotEmpty()) {
            $summary = "The following documents have expired and are due for renewal:\r\n\r\n";
            $summary .= $newRenewals->map(fn(CompanyDoc $document) => "{$document->name} - expired {$document->expiry->format('d/m/Y')}")->implode("\r\n");
            $legacyRecipients = $company->reportsTo()?->notificationsUsersEmailType('doc.standard.renew') ?: [];
            $mailable = new CompanyDocRenewalMulti($summary);

            // Always submit the summary so Managed To/CC recipients can receive
            // it even when the legacy notification group is currently empty.
            if ($legacyRecipients) $mailable->to($legacyRecipients);
            $this->mailer->send($mailable);
            echo "Sent the Standard Details renewal summary for {$newRenewals->count()} new document(s).\n";
        } else {
            echo "No new Standard Details renewal reviews or summary email were required.\n";
        }

        echo "New Standard Details renewal reviews created: {$newRenewals->count()}.\n";

        return $newRenewals->count();
    }
}
