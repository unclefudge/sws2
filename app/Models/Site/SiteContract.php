<?php

namespace App\Models\Site;

use App\Services\FileBank;
use Illuminate\Database\Eloquent\Model;

class SiteContract extends Model
{

    protected $table = 'site_contracts';
    protected $fillable = [
        'site_id', 'owner1_title', 'owner1_name', 'owner1_mobile', 'owner1_email', 'owner1_abn', 'owner2_title', 'owner2_name', 'owner2_mobile', 'owner2_email', 'owner2_abn',
        'owner_address', 'owner_suburb', 'owner_state', 'owner_postcode', 'contract_price', 'contract_net', 'contract_gst', 'deposit',
        'land_lot', 'land_dp', 'land_title', 'land_address', 'land_suburb', 'land_state', 'land_postcode',
        'building_period', 'initial_period', 'hia_contract_id', 'hia_template_id', 'hia_pdf', 'hia_xml', 'status', 'notes'];


    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site', 'site_id');
    }


    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment)
            return '';

        return FileBank::url("site/{$this->site_id}/docs/{$this->attachment}");
    }


    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->site->company;
    }
}