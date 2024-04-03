<?php

namespace App\Livewire\Site;

//use App\Models\Site\SiteMaintenance;
use Livewire\Component;

class SiteMaintenance extends Component
{
    public $main;
    public $editSiteDetails;
    public $editClientDetails;
    public $editGallery;
    public $m_completed;
    public $m_supervisor; // Site Super
    public $m_super_id; // Assigned Super
    public $m_category_id;
    public $m_warranty;
    public $m_status;
    public $m_onhold_reason;
    public $m_ac_form_required;
    public $m_contact_name;
    public $m_contact_phone;
    public $m_contact_email;
    public $m_client_contacted;
    public $m_client_appointment;

    public function mount($main)
    {
        $this->main = $main;
        $this->editSiteDetails = false;
        $this->editClientDetails = false;
        $this->editGallery = false;
        $this->initialiseForm($main);

    }

    public function initialiseForm($main): void
    {
        $this->m_completed = $main->completed ? $main->completed->format('d/m/Y') : '';
        $this->m_supervisor = $main->supervisor;
        $this->m_super_id = $main->super_id;
        $this->m_category_id = $main->category_id;
        $this->m_warranty = $main->warranty;
        $this->m_status = $main->status;
        $this->m_onhold_reason = $main->onhold_reason;
        $this->m_ac_form_required = $main->ac_form_required;
        $this->m_contact_name = $main->contact_name;
        $this->m_contact_phone = $main->contact_phone;
        $this->m_contact_email = $main->contact_email;
        $this->m_client_contacted = $main->client_contacted ? $main->client_contacted->format('d/m/Y') : '';
        $this->m_client_appointment = $main->client_appointment ? $main->client_appointment->format('d/m/Y') : '';
    }

    public function render()
    {
        return view('livewire.site.site-maintenance');
    }

    public function saveForm(): void
    {
        //ray('saving');
    }


}
