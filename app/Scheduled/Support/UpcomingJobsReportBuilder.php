<?php

namespace App\Scheduled\Support;

use App\Http\Controllers\Site\SiteUpcomingComplianceController;
use App\Models\Site\SiteUpcomingSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class UpcomingJobsReportBuilder
{
    public function createPdf(): string
    {
        $settings = SiteUpcomingSettings::query()->whereIn('field', ['opt', 'cfest', 'cfadm'])->where('status', 1)->orderBy('order')->get(['field', 'order', 'colour']);
        $settingsColours = ['opt' => [], 'cfest' => [], 'cfadm' => []];

        foreach ($settings as $setting) {
            $parts = $setting->colour ? explode('-', $setting->colour) : [];
            $settingsColours[$setting->field][$setting->order] = !empty($parts[2]) ? "#{$parts[2]}" : '';
        }

        $startdata = SiteUpcomingComplianceController::getUpcomingData();
        File::ensureDirectoryExists(storage_path('app/tmp'));
        $file = storage_path('app/tmp/upcoming-jobs-planning-' . Carbon::now()->format('Ymd-His-u') . '.pdf');

        \PDF::loadView('pdf/site/upcoming-compliance', ['startdata' => $startdata, 'settings_colours' => $settingsColours])->setPaper('A4', 'landscape')->save($file);

        return $file;
    }
}
