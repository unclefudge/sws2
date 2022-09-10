<?php

namespace App\Models\Site;

use PDF;
use URL;
use Mail;
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
    * List of items completed
    */
    public function sitesCompleted()
    {
        $completed = [];
        foreach ($this->sites as $site) {
            if ($site->reasons)
                $completed[] = $site->id;
        }

        return SiteExtensionSite::find($completed);
    }

    /**
     * Create PDF Report
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createPDF()
    {

        $data = [];
        foreach ($this->sites as $site) {
            $data[] = [
                'id'                   => $site->id,
                'name'                 => $site->site->name,
                'super_initials'       => $site->site->supervisorsInitialsSBC(),
                'completion_date'      => ($site->completion_date) ? $site->completion_date->format('d/m/y') : '',
                'extend_reasons'       => $site->reasons,
                'extend_reasons_text'  => $site->reasonsSBC(),
                'extend_reasons_array' => $site->reasonsArray(),
                'notes'                => $site->notes
            ];
        }

        $dir = "/filebank/company/3/docs/contract-extension";
        // Create directory if required
        if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);

        $file = public_path("$dir/ContractExtensions " .$this->date->format('d-m-Y') . '.pdf');
        if (file_exists($file))
            unlink($file);

        SiteExtensionPdf::dispatch('pdf/site/contract-extension', $data, $file);

        //$pdf = PDF::loadView('pdf/site/contract-extension', compact('data'));
        //$pdf->setPaper('A4', 'landscape');
        //$pdf->save($file);

        $this->attachment = "ContractExtensions " .$this->date->format('d-m-Y') . '.pdf';
        $this->save();
    }

    /**
     * Create ToDoo for ProjectSupply and assign to given user(s)
     */
    public function createSignOffToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type'       => 'project supply',
            'type_id'    => $this->id,
            'name'       => 'Project Supply Information - ' . $site->name,
            'info'       => 'Please sign off on completed items',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->site->company_id,
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Close any outstanding ToDoo for this Project Supply
     */
    public function closeToDo()
    {
        $todos = Todo::where('type', 'project supply')->where('type_id', $this->id)->where('status', '1')->get();
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