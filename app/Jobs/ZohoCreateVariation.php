<?php

namespace App\Jobs;

use App\Models\Site\SiteNote;
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
        //$note = SiteNote::with(['site', 'site.super',])->findOrFail($this->siteNoteId);
        $note = SiteNote::findOrFail($this->siteNoteId);

        if (!in_array($note->category_id, [16, 19, 20]))
            return;

        if (!$note->site || !$note->site->zoho_job_id) {
            Log::warning('Zoho variation skipped: missing Zoho Job ID', ['site_note_id' => $note->id, 'site_id' => $note->site_id,]);

            return;
        }

        if ($note->zoho_variation_id) {
            Log::info('Zoho variation skipped: already created', ['site_note_id' => $note->id, 'zoho_variation_id' => $note->zoho_variation_id,]);

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
            /*$attachments = Attachment::where('table', 'site_notes')->where('table_id', $note->id)->get();
            foreach ($attachments as $attachment) {
                if (!$attachment->directory || !$attachment->attachment) {
                    continue;
                }

                $fileBankPath = trim($attachment->directory, '/') . '/' . $attachment->attachment;

                ray([
                    'attachment_id' => $attachment->id,
                    'name' => $attachment->name,
                    'directory' => $attachment->directory,
                    'attachment' => $attachment->attachment,
                    'fileBankPath' => $fileBankPath,
                    'default_disk' => config('filesystems.filebank_default_disk'),
                    'fallback_disk' => config('filesystems.filebank_fallback_disk'),
                    'spaces_exists' => \Storage::disk('filebank_spaces')->exists($fileBankPath),
                    'local_exists' => \Storage::disk('filebank_local')->exists($fileBankPath),
                ]);

                if (!FileBank::exists($fileBankPath)) {
                    Log::warning('SiteNote attachment missing from FileBank', ['site_note_id' => $note->id, 'attachment_id' => $attachment->id, 'filebank_path' => $fileBankPath,]);

                    continue;
                }

                $files[] = [
                    'name' => $this->safeFilename($attachment->name ?: $attachment->attachment),
                    'contents' => FileBank::readStream($fileBankPath),
                ];
            }*/

            /*
             |--------------------------------------------------------------------------
             | 3. Upload PDF + attachments to Zoho ZFS
             |--------------------------------------------------------------------------
             */
            /*try {
                $fileIds = $zoho->uploadFilesToZfsFromStreams($files);
            } finally {
                $this->closeOpenStreams($files);
            }*/

            /*
             |--------------------------------------------------------------------------
             | 4. Create Zoho Product / Variation
             |--------------------------------------------------------------------------
             */
            $varStatus = ($note->category_id == 19) ? '5-Sent to DC/Super' : '7-Client OK';
            $varDescription = ($note->variation_info ?? '') . "\r\n\r\nNote:\r\n" . ($note->notes ?? '') . "\r\n\r\nTotal Extension Days: " . ($note->variation_days ?? '');

            $data = [
                'var_type' => 'SV',
                'job_number' => '1234', //$note->site->code,
                'job_name' => '1976497000011760001', //$note->site->zoho_job_id,
                'product_name' => "TESTING:  " . $note->variation_name,
                'debit_or_credit' => ($note->costing_extra_credit == 'Extra') ? 'DEBIT Variation' : 'CREDIT Variation',
                'status' => $varStatus,
                'variation_cost' => preg_replace('/[^0-9.]/', '', $note->variation_net),
                'client_price' => preg_replace('/[^0-9.]/', '', $note->variation_cost),
                'margin' => 20,
                'super' => $note->site->super?->initials ?? '',
                'description' => $varDescription,
                'RFV8_released' => ($note->category_id == 19) ? null : Carbon::now()->format('Y-m-d'),
                //'file_ids' => $fileIds,  // These become File_Id__s objects inside ZohoCrmService.
            ];

            $zohoResult = $zoho->createVariation($data);
            $zohoVariationId = $zohoResult['zoho_variation_id'] ?? null;
            //Log::info('Zoho variation created from SiteNote', ['site_note_id' => $note->id, 'site_id' => $note->site_id, 'zoho_variation_id' => $note->zoho_variation_id, 'zoho_file_count' => count($fileIds),]);

            /*
             |--------------------------------------------------------------------------
             | 5. Upload Attachments to Variation
             |--------------------------------------------------------------------------
             */
            if ($zohoVariationId) {
                $attachmentResults = $zoho->uploadAttachmentsToRecordFromStreams('Products', $zohoVariationId, $files);

                $note->zoho_variation_id = $zohoVariationId;
                //$note->save();

                Log::info('Zoho attachments uploaded to Variation', ['site_note_id' => $note->id, 'zoho_variation_id' => $zohoVariationId, 'attachments' => $attachmentResults,]);
            }
        } catch (Throwable $e) {
            $this->closeOpenStreams($files);
            Log::error('ZohoCreateVariation failed', ['site_note_id' => $note->id ?? $this->siteNoteId, 'error' => $e->getMessage(),]);
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