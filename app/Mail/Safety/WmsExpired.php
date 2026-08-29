<?php

namespace App\Mail\Safety;

use App\Models\Company\Company;
use App\Models\Safety\WmsDoc;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use URL;

class WmsExpired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WmsDoc $doc, public bool $expired = false) {
    }

    public function build(): self
    {
        $company = Company::findOrFail($this->doc->for_company_id);
        $renewalDate = $this->doc->updated_at->copy()->addYears(2)->format('d/m/Y');
        $message = $this->expired ? "has Expired {$renewalDate}" : "due to expire {$renewalDate}";

        return $this
            ->from('do-not-reply@safeworksite.com.au', 'SafeWorksite')
            ->subject("SWMS - {$this->doc->name} {$message}")
            ->view('emails/workmethod-expired')
            ->with([
                'user_email' => 'do-not-reply@safeworksite.com.au',
                'user_fullname' => 'SafeWorksite',
                'user_company_name' => 'SafeWorksite',
                'company_name' => $company->name,
                'doc_name' => $this->doc->name,
                'mesg' => $message,
                'url' => URL::to('/safety/doc/wms') . '/' . $this->doc->id,
            ]);
    }
}
