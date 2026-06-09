<?php

namespace App\Jobs;

use App\Models\Misc\Attachment;
use App\Models\Site\SiteNote;
use App\Services\FileBank;
use App\Services\Zoho\ZohoCrmService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ZohoCreateVariation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public int $siteNoteId)
    {
    }

    public function handle(ZohoCrmService $zoho): void
    {
        $note = SiteNote::findOrFail($this->siteNoteId);

        if (!in_array($note->category_id, [16, 19, 20])) {
            return;
        }

        if (!$note->site || !$note->site->zoho_job_id) {
            Log::warning('Zoho variation skipped: missing Zoho Job ID', ['site_note_id' => $note->id, 'site_id' => $note->site_id,]);

            return;
        }

        $files = [];
        try {
            /*
             |--------------------------------------------------------------------------
             | 1. Create SiteNote PDF in memory
             |--------------------------------------------------------------------------
             */
            $pdfContents = Pdf::loadView('pdf/site/site-note-variation', ['note' => $note, 'site' => $note->site,])->output();
            $files[] = ['name' => "site-note-{$note->id}.pdf", 'contents' => $pdfContents,];

            /*
             |--------------------------------------------------------------------------
             | 2. Add saved SiteNote attachments from FileBank
             |--------------------------------------------------------------------------
             */
            $attachments = Attachment::where('table', 'site_notes')->where('table_id', $note->id)->get();
            //Log::info('ZohoCreateVariation SiteNote attachments found', ['site_note_id' => $note->id, 'attachment_count' => $attachments->count(), 'attachment_ids' => $attachments->pluck('id')->values()->all(),]);

            foreach ($attachments as $attachment) {
                if (!$attachment->directory || !$attachment->attachment) {
                    Log::warning('SiteNote attachment skipped: missing directory or attachment filename', ['site_note_id' => $note->id, 'attachment_id' => $attachment->id, 'directory' => $attachment->directory, 'attachment' => $attachment->attachment,]);

                    continue;
                }

                $fileBankPath = trim($attachment->directory, '/') . '/' . $attachment->attachment;
                $exists = FileBank::exists($fileBankPath);

                if (!$exists) {
                    Log::warning('SiteNote attachment missing from FileBank', ['site_note_id' => $note->id, 'attachment_id' => $attachment->id, 'filebank_path' => $fileBankPath,]);

                    continue;
                }

                // Add file to array
                $files[] = [
                    'name' => $this->safeFilename($attachment->name ?: $attachment->attachment),
                    'contents' => FileBank::readStream($fileBankPath),
                ];
            }

            /*
             |--------------------------------------------------------------------------
             | 3. Get or create Zoho Product / Variation
             |--------------------------------------------------------------------------
             |
             | Important:
             | If Zoho already created the Variation on a previous failed attempt,
             | we reuse the saved zoho_variation_id and only retry attachments.
             |
             */
            if ($note->zoho_variation_id) {
                $zohoVariationId = $note->zoho_variation_id;

                Log::info('Zoho variation already exists, will upload attachments only', ['site_note_id' => $note->id, 'zoho_variation_id' => $zohoVariationId,]);
            } else {
                $varStatus = ($note->category_id == 19) ? '5-Sent to DC/Super' : '7-Client OK';
                $varDescription = ($note->variation_info ?? '') . "\r\n\r\nTotal Extension Days: " . ($note->variation_days ?? '');

                $data = [
                    'var_type' => 'SV',
                    'job_number' => $note->site->code,
                    'job_name' => $note->site->zoho_job_id, // test job '1976497000011760001',

                    'product_name' => $note->variation_name,
                    'debit_or_credit' => ($note->costing_extra_credit == 'Extra') ? 'DEBIT Variation' : 'CREDIT Variation',
                    'status' => $varStatus,
                    'variation_cost' => $this->moneyToWholeNumber($note->variation_net),
                    'client_price' => $this->moneyToWholeNumber($note->variation_cost),
                    'margin' => 20,
                    'super' => $note->site->super?->initials ?? '',
                    'description' => $varDescription,
                    'RFV8_released' => ($note->category_id == 19) ? null : Carbon::now()->format('Y-m-d'),
                ];

                $zohoResult = $zoho->createVariation($data);
                $zohoVariationId = $zohoResult['zoho_variation_id'] ?? null;

                if (!$zohoVariationId) {
                    throw new \RuntimeException('Zoho variation was created but no Zoho Variation ID was returned.');
                }

                /*
                 |--------------------------------------------------------------------------
                 | Save immediately to prevent duplicate Zoho records on retry
                 |--------------------------------------------------------------------------
                 */
                $note->zoho_variation_id = $zohoVariationId;
                $note->save();

                //Log::info('Zoho variation created and saved to SiteNote', ['site_note_id' => $note->id, 'zoho_variation_id' => $zohoVariationId,]);
            }

            /*
             |--------------------------------------------------------------------------
             | 4. Upload PDF + attachments to Zoho Attachments related list
             |--------------------------------------------------------------------------
             */
            //Log::info('ZohoCreateVariation files prepared for upload', ['site_note_id' => $note->id, 'zoho_variation_id' => $zohoVariationId, 'file_count' => count($files), 'file_names' => collect($files)->pluck('name')->values()->all(),]);

            $attachmentResults = $zoho->uploadAttachmentsToRecordFromStreams('Products', $zohoVariationId, $files);
            //Log::info('Zoho attachments uploaded to Variation', ['site_note_id' => $note->id, 'zoho_variation_id' => $zohoVariationId, 'attachments' => $attachmentResults,]);
        } catch (Throwable $e) {
            Log::error('ZohoCreateVariation failed', ['site_note_id' => $note->id ?? $this->siteNoteId, 'zoho_variation_id' => $note->zoho_variation_id ?? null, 'error' => $e->getMessage(),]);

            throw $e;
        } finally {
            $this->closeOpenStreams($files);
        }
    }

    protected function safeFilename(string $filename): string
    {
        $filename = trim($filename);

        if ($filename === '')
            return 'attachment';

        return preg_replace('/[^A-Za-z0-9\.\-_ ]/', '_', $filename);
    }

    protected function moneyToWholeNumber($value): ?int
    {
        if ($value === null) {
            return null;
        }

        // Remove normal spaces and non-breaking spaces
        $value = trim(str_replace("\xc2\xa0", ' ', (string)$value));

        if ($value === '') {
            return null;
        }

        // Remove commas, then keep only digits, decimal point and minus sign
        $value = str_replace(',', '', $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        // If more than one decimal point exists, keep the first only
        $parts = explode('.', $value);
        if (count($parts) > 2) {
            $value = array_shift($parts) . '.' . implode('', $parts);
        }

        if ($value === '' || $value === '.' || $value === '-' || $value === '-.') {
            return null;
        }

        return (int)round((float)$value, 0, PHP_ROUND_HALF_UP);
    }

    protected function closeOpenStreams(array $files): void
    {
        foreach ($files as $file) {
            if (isset($file['contents']) && is_resource($file['contents']))
                fclose($file['contents']);
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('ZohoCreateVariation permanently failed', ['site_note_id' => $this->siteNoteId, 'error' => $e->getMessage(),]);
    }
}