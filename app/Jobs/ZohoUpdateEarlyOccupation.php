<?php

namespace App\Jobs;

use App\Models\Site\SiteNote;
use App\Services\Zoho\ZohoCrmService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ZohoUpdateEarlyOccupation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;
    public array $backoff = [60, 300, 900, 1800, 3600];
    public int $timeout = 180;

    public function __construct(public int $siteNoteId)
    {
    }

    public function handle(ZohoCrmService $zoho): void
    {
        $note = SiteNote::with('site')->findOrFail($this->siteNoteId);

        if ((int)$note->category_id !== 94) {
            return;
        }

        if (!$note->site || !$note->site->zoho_job_id) {
            Log::warning('Early Occupation Zoho update skipped: missing Zoho Job ID', ['site_note_id' => $note->id, 'site_id' => $note->site_id,]);
            return;
        }

        if (!$note->occupation_date) {
            throw new RuntimeException("Early Occupation Site Note {$note->id} has no occupation date.");
        }

        $appTimezone = config('app.timezone', 'Australia/Hobart');
        $occupationDateTime = Carbon::parse($note->occupation_date)->setTimezone($appTimezone)->setTime(9, 0)->format('Y-m-d\TH:i:sP');

        $result = $zoho->updateRecord('Accounts', $note->site->zoho_job_id, ['Client_Occupation' => $occupationDateTime]);

        //Log::info('Zoho Early Occupation field updated', ['site_note_id' => $note->id, 'site_id' => $note->site_id, 'zoho_job_id' => $note->site->zoho_job_id, 'occupation_datetime' => $occupationDateTime, 'zoho_record_id' => $result['zoho_record_id'] ?? null,]);
    }

    public function failed(Throwable $e): void
    {
        $error = Str::limit(strip_tags($e->getMessage()), 1500);

        Log::critical('Zoho Early Occupation update permanently failed', ['site_note_id' => $this->siteNoteId, 'exception' => get_class($e), 'error' => $error,]);

        $recipient = config('services.zoho.failure_alert_email');
        if (!$recipient) {
            Log::warning('Zoho failure alert email is not configured', ['site_note_id' => $this->siteNoteId,]);
            return;
        }

        try {
            Mail::raw(
                "The Zoho Early Occupation update permanently failed after all retries.\n\n"
                . "SWS Site Note ID: {$this->siteNoteId}\n"
                . "Exception: " . get_class($e) . "\n"
                . "Error: {$error}\n",
                function ($message) use ($recipient) {
                    $message->to($recipient)
                        ->subject("SWS Zoho Early Occupation update failed: Site Note {$this->siteNoteId}");
                }
            );
        } catch (Throwable $mailException) {
            Log::critical('Unable to send Zoho Early Occupation failure alert', ['site_note_id' => $this->siteNoteId, 'error' => Str::limit(strip_tags($mailException->getMessage()), 1000),]);
        }
    }
}
