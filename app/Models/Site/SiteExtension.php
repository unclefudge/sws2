<?php

namespace App\Models\Site;

use PDF;
use URL;
use Mail;
use App\Models\Comms\Todo;
use App\Jobs\SiteExtensionPdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SiteExtension extends Model {

    protected $table = 'site_extensions';
    protected $fillable = ['name', 'date', 'approved_by', 'approved_at', 'attachment', 'status', 'notes'];

    protected $dates = ['date', 'approved_at'];

    /**
     * A SiteExtension has many sites.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sites()
    {
        return $this->hasMany('App\Models\Site\SiteExtensionSite', 'extension_id');
    }

    /*
    * List of sites completed
    */
    public function sitesCompleted()
    {
        $completed = [];
        foreach ($this->sites as $site_ext) {
            if ($site_ext->reasons)
                $completed[] = $site_ext->id;
        }

        return SiteExtensionSite::find($completed);
    }

    /*
    * List of sites not completed for supervisor
    */
    public function sitesNotCompletedBySupervisor($user_id)
    {
        $not_completed = [];
        foreach ($this->sites as $site_ext) {
            if ($site_ext->site->supervisor_id == $user_id) {
                if (!$site_ext->reasons)
                    $not_completed[] = $site_ext->id;
            }
        }

        return SiteExtensionSite::find($not_completed);
    }

    /**
     * Create PDF Report
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createPDF()
    {
        $data = [];
        foreach ($this->sites as $site_ext) {
            $data[] = [
                'id'                   => $site_ext->id,
                'name'                 => $site_ext->site->name,
                'super_initials'       => $site_ext->site->supervisorInitials,
                'completion_date'      => ($site_ext->completion_date) ? $site_ext->completion_date->format('d/m/y') : '',
                'extend_reasons'       => $site_ext->reasons,
                'extend_reasons_text'  => $site_ext->reasonsSBC(),
                'extend_reasons_array' => $site_ext->reasonsArray(),
                'notes'                => $site_ext->notes
            ];
        }

        $dir = "/filebank/company/3/docs/contract-extension";
        // Create directory if required
        if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);

        $filename = "ContractExtensions " .$this->date->format('d-m-Y') . '.pdf';
        $file = public_path("$dir/$filename");
        if (file_exists($file))
            unlink($file);

        //SiteExtensionPdf::dispatch('pdf/site/contract-extension', $this, $data, $file);

        $extension = $this;
        $pdf = PDF::loadView('pdf/site/contract-extension', compact('data', 'extension'));
        $pdf->setPaper('A4', 'landscape');
        //$pdf->stream();
        $pdf->save($file);

        $this->attachment = $filename;
        $this->save();
    }

    /**
     * Create ToDoo for SiteExtension to be Signed Off
     */
    public function createSignOffToDo($user_list)
    {
        $todo_request = [
            'type'       => 'extension',
            'type_id'    => $this->id,
            'name'       => 'Authorise Contract Time Extensions - ' . $this->date->format('d/m/Y'),
            'info'       => 'Please sign off on completed items',
            'due_at'     => Carbon::today()->toDateTimeString(),
            'company_id' => 3,
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Close any outstanding ToDoo for this SiteExtension
     */
    public function closeToDo()
    {
        $todos = Todo::where('type', 'extension')->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = (Auth::check()) ? Auth::user()->id : 1;
            $todo->save();
        }
    }

    /**
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attributes['attachment'])
            return '/filebank/company/3/docs/contract-extension/'.$this->attributes['attachment'];
        return '';
    }
}