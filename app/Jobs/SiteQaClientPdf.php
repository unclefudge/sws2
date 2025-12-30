<?php

namespace App\Jobs;

use App\Models\Misc\Attachment;
use App\Models\Site\Site;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Log;
use PDF;

class SiteQaClientPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $attachId, public $data, public $site_id)
    {
    }

    public function handle()
    {
        $attachment = Attachment::findOrFail($this->attachId);
        $site = Site::findOrFail($this->site_id);

        try {
            $pdf = PDF::loadView('pdf/site-qa-single', ['data' => $this->data, 'site' => $site])->setPaper('a4')->output();
            Storage::disk('filebank_spaces')->put("$attachment->directory/$attachment->attachment", $pdf);
            $attachment->update(['status' => '1']);
        } catch (\Throwable $e) {
            $attachment->update(['status' => '0']);
            throw $e;
        }
    }
}
