<?php

namespace App\Services;

use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SitePlannerDataBuilder
{
    /**
     * Build Site Planner report data.
     *
     * This returns the SAME $data structure currently used by pdf.plan-site
     */
    public static function build(array $options): array
    {
        /*
        * Required options:
        * - date (Y-m-d)
        * - weeks (int)
        * - mode: site | supervisor | client | company
        *
        * Optional:
        * - site_ids
        * - supervisor_ids
        * - company_ids
        */

        $date = Carbon::parse($options['date'])->format('Y-m-d');
        $weeks = (int)$options['weeks'];
        $mode = $options['mode'];

        // --------------------------------------------------
        // Resolve sites
        // --------------------------------------------------
        if ($mode === 'supervisor') {
            $superIds = $options['supervisor_ids'] ?? [];

            $superSites = Auth::user()->company->reportsTo()->sites('1')->whereIn('supervisor_id', $superIds)->pluck('id')->toArray();
            $sites = [];
            foreach ($superSites as $sid) {
                $site = Site::find($sid);
                if ($site && $site->JobStart && $site->JobStart->lt(Carbon::today())) {
                    $sites[] = $sid;
                }
            }
        } else
            $sites = $options['site_ids'] ?? Auth::user()->company->reportsTo()->sites('1')->pluck('id')->toArray();

        // --------------------------------------------------
        // Build planner data (UNCHANGED LOGIC)
        // --------------------------------------------------
        $data = [];

        foreach ($sites as $siteID) {
            $site = Site::findOrFail($siteID);

            $obj_site = (object)[
                'site_id' => $site->id,
                'site_name' => $site->name,
                'supervisor' => $site->supervisor?->name ?? '',
                'prac_complete' => (bool)$site->completion_signed,
                'reportType' => $mode,
                'weeks' => [],
            ];

            $current_date = $date;

            for ($w = 1; $w <= $weeks; $w++) {

                $date_from = Carbon::parse($current_date);
                while ($date_from->isWeekend()) {
                    $date_from->addDay();
                }

                $dates = [$date_from->format('Y-m-d')];
                $date_to = $date_from->copy();

                for ($i = 2; $i < 6; $i++) {
                    $date_to->addDay();
                    while ($date_to->isWeekend()) {
                        $date_to->addDay();
                    }
                    $dates[] = $date_to->format('Y-m-d');
                }

                // Pull planner entries
                $planner = SitePlanner::where('site_id', $site->id)->where(function ($q) use ($date_from, $date_to) {
                    $q->whereBetween('from', [$date_from, $date_to])
                        ->orWhereBetween('to', [$date_from, $date_to])
                        ->orWhere(function ($q2) use ($date_from, $date_to) {
                            $q2->where('from', '<', $date_from)->where('to', '>', $date_to);
                        });
                })
                    ->orderBy('from')->get();

                // Build entities
                $entities = [];

                foreach ($planner as $plan) {
                    $key = "{$plan->entity_type}.{$plan->entity_id}";
                    if (!isset($entities[$key])) {
                        if ($plan->entity_type === 'c') {
                            $entity = Company::find($plan->entity_id);
                            $entity_name = $entity?->name ?? 'Company';
                        } else {
                            $entity = Trade::find($plan->entity_id);
                            $entity_name = $entity?->name ?? 'Trade';
                        }

                        $entities[$key] = [
                            'entity_type' => $plan->entity_type,
                            'entity_id' => $plan->entity_id,
                            'entity_name' => $entity_name,
                            'dates' => array_fill_keys($dates, '&nbsp;'),
                        ];
                    }
                }

                // Header row
                $obj_site->weeks[$w][0][] = 'COMPANY';
                foreach ($dates as $d) {
                    $obj_site->weeks[$w][0][] = strtoupper(Carbon::parse($d)->format('l d/m'));
                }

                // Body rows
                $row = 1;
                foreach ($entities as $e) {
                    $obj_site->weeks[$w][$row][] = $e['entity_name'];

                    foreach ($dates as $d) {
                        $tasks = $site->entityTasksOnDate($e['entity_type'], $e['entity_id'], $d);

                        if ($tasks) {
                            $str = implode('<br>', $tasks);
                        } else {
                            $str = '&nbsp;';
                        }

                        $obj_site->weeks[$w][$row][] = $str;
                    }
                    $row++;
                }

                $current_date = Carbon::parse($current_date)->addDays(7)->format('Y-m-d');
            }

            $data[] = $obj_site;
        }

        return $data;
    }
}
