<?php

namespace App\Models\Site;

use App\Models\Comms\Todo;
use App\Services\FileBank;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use PDF;
use URL;


class SiteExtension extends Model
{

    protected $table = 'site_extensions';
    protected $fillable = ['name', 'date', 'approved_by', 'approved_at', 'attachment', 'status', 'notes'];

    protected $casts = ['date' => 'datetime', 'approved_at' => 'datetime'];

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
                'id' => $site_ext->id,
                'name' => $site_ext->site->name,
                'super_initials' => $site_ext->site->supervisorInitials,
                'completion_date' => ($site_ext->completion_date) ? $site_ext->completion_date->format('d/m/y') : '',
                'extend_reasons' => $site_ext->reasons,
                'extend_reasons_text' => $site_ext->reasonsSBC(),
                'extend_reasons_array' => $site_ext->reasonsArray(),
                'days' => $site_ext->days,
                'notes' => $site_ext->notes
            ];
        }

        // Sort by site name
        usort($data, fn($a, $b) => $a['name'] <=> $b['name']);

        $basePath = 'company/3/docs/contract-extension';
        $filename = 'ContractExtensions ' . $this->date->format('d-m-Y') . '.pdf';
        $path = "{$basePath}/{$filename}";

        // Generate PDF (in memory)
        $extension = $this;
        $pdf = PDF::loadView('pdf/site/contract-extension', compact('data', 'extension'))->setPaper('A4', 'landscape');

        // Overwrite if it already exists
        FileBank::delete($path);

        // Save PDF to Spaces
        FileBank::putContents($path, $pdf->output());

        // Persist filename only
        $this->attachment = $filename;
        $this->save();
    }

    /**
     * Create ToDoo for SiteExtension to be Signed Off
     */
    public function createSignOffToDo($user_list)
    {
        $todo_request = [
            'type' => 'extension signoff',
            'type_id' => $this->id,
            'name' => 'Authorise Contract Time Extensions - ' . $this->date->format('d/m/Y'),
            'info' => 'Please sign off on completed items',
            'due_at' => Carbon::today()->toDateTimeString(),
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
        $todos = Todo::whereIn('type', ['extension', 'extension signoff'])->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = (Auth::check()) ? Auth::user()->id : 1;
            $todo->save();
        }
    }

    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment)
            return '';

        return FileBank::url("company/3/docs/contract-extension/{$this->attachment}");
    }
}