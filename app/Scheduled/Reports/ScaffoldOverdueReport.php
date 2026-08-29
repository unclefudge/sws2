<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteScaffoldHandoverOutstanding;
use App\Models\Comms\Todo;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\Models\Site\SiteDoc;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use App\User;
use Carbon\Carbon;

class ScaffoldOverdueReport implements ScheduledOperationHandler
{
    /*-------------------------------------------------------------------------------
     * Sends two related scaffold-handover reports from different data sources:
     *
     * 1. Ian Scott Ewin items come from overdue scaffold-handover ToDos.
     * 2. Ashbys items come from past "Erect Scaffold" planner tasks that do not
     *    yet have a Scaffolding Handover Certificate document.
     *
     * Items are grouped by Site Supervisor. A Supervisor can therefore receive
     * one Ian email, one Ashbys email, or both, containing only their own sites.
     *-----------------------------------------------------------------------------*/

    public function __construct(private ScheduledDynamicRecipientResolver $recipientResolver, private ScheduledReportMailer $mailer)
    {
    }

    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.scaffold_overdue',
            'name' => 'Overdue scaffolds',
            'category' => 'report',
            'description' => 'Emails outstanding scaffold handover certificates grouped by scaffold provider and Site Supervisor.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Relevant Site Supervisor (To), scaffold provider contacts (CC), and dashboard management recipients',
            // Dynamic recipients cannot safely be replaced by one shared dashboard list:
            // the Supervisor changes per email and Ian must not receive Ashbys reports.
            'dynamicRecipients' => [
                ['key' => 'site_supervisor', 'label' => 'Site Supervisor', 'delivery' => 'to', 'description' => 'The Supervisor responsible for the sites in the individual email.', 'required' => true],
                ['key' => 'scaffold_provider', 'label' => 'Scaffold provider', 'delivery' => 'cc', 'description' => 'The provider contacts relevant to the scaffold group in the individual email.', 'required' => true],
            ],
            'clientConfigurable' => true,
        ];
    }

    /**
     * Find overdue scaffold handovers and send one provider-specific email per Supervisor.
     */
    public function handle(): int
    {
        $today = Carbon::today();

        // Build the two provider-specific groups separately because their
        // overdue items come from different parts of SafeWorksite.
        $ianBySupervisor = $this->ianScottEwinItems($today);
        $ashbysBySupervisor = $this->ashbysItems($today);

        echo 'Ian Scott Ewin overdue scaffold handovers: ' . $this->itemCount($ianBySupervisor) . "\n";
        echo 'Ashbys overdue scaffold handovers: ' . $this->itemCount($ashbysBySupervisor) . "\n";
        echo "----------------------------------------------------\n";

        // Ian receives only Ian Scott Ewin scaffold reports as a required CC.
        $emailsSent = $this->sendProviderReports($ianBySupervisor, 'Ian Scott Ewin', [
            $this->fixedRecipient('scaffold_provider', 'Ian Scott Ewin', 'ianscottewin@gmail.com'),
        ]);

        // These contacts receive only Ashbys scaffold reports as required CCs.
        $emailsSent += $this->sendProviderReports($ashbysBySupervisor, 'Ashbys', [
            $this->fixedRecipient('scaffold_provider', 'Ashbys coordination', 'construct@capecod.com.au'),
            $this->fixedRecipient('scaffold_provider', 'Ashbys', 'info@ashby.com.au'),
        ]);

        if (!$emailsSent) echo "No email required.\n";

        return $emailsSent;
    }

    /*-------------------------------------------------------------------------------
     * Find overdue Ian Scott Ewin scaffold-handover ToDos and group them by
     * Site Supervisor. The site code is extracted from names formatted like:
     * "Scaffold Handover Certificate for 4351-Site Name".
     *------------------------------------------------------------------------------*/
    private function ianScottEwinItems(Carbon $today): array
    {
        $todos = Todo::query()->where('status', 1)->where('type', 'scaffold handover')->whereDate('due_at', '<', $today)
            ->where('due_at', '<>', '0000-00-00 00:00:00')->orderBy('due_at')->get();

        // Parse first, then load all matching sites in one query rather than
        // querying the Site model once for every ToDo.
        $parsed = $todos->map(function (Todo $todo) {
            if (!preg_match('/Scaffold Handover Certificate for\s+([^-]+)-/i', $todo->name, $matches)) return null;
            return ['todo' => $todo, 'site_code' => trim($matches[1])];
        })->filter()->values();

        $sites = Site::query()->whereIn('code', $parsed->pluck('site_code')->unique())->get()->keyBy('code');
        $grouped = [];

        foreach ($parsed as $item) {
            $todo = $item['todo'];
            $site = $sites->get($item['site_code']);
            if (!$site) continue;

            $supervisorId = (int)($site->supervisor_id ?: 0);
            $grouped[$supervisorId][$todo->type_id] = ['name' => $todo->name, 'due_at' => $todo->due_at->format('d/m/Y')];
        }

        return $grouped;
    }

    /*-------------------------------------------------------------------------------
     * Find Ashbys "Erect Scaffold" planner tasks (task 116) that are:
     * - before today and after 1 January 2024;
     * - attached to an active site;
     * - not manually excluded; and
     * - not covered by an existing Scaffolding Handover Certificate document.
     *
     * The remaining planner tasks are grouped by Site Supervisor.
     *------------------------------------------------------------------------------*/
    private function ashbysItems(Carbon $today): array
    {
        $excludedPlannerTasks = [129578, 129601, 129993, 135665, 136666, 137626, 137903, 139403];
        $plans = SitePlanner::query()->with('site')->whereDate('from', '>', Carbon::createFromFormat('Y-m-d', '2024-01-01'))
            ->whereDate('from', '<', $today)->where('task_id', 116)->orderBy('from')->get();

        // Load all sites with a certificate once, avoiding a document query for every plan.
        $certificateSiteIds = SiteDoc::query()->whereIn('site_id', $plans->pluck('site_id')->unique())
            ->where('name', 'like', '%Scaffolding Handover Certificate%')->pluck('site_id')->unique();
        $grouped = [];

        foreach ($plans as $plan) {
            $site = $plan->site;
            if (!$site || (int)$site->status !== 1 || in_array((int)$plan->id, $excludedPlannerTasks, true) || $certificateSiteIds->contains($plan->site_id)) continue;

            $supervisorId = (int)($site->supervisor_id ?: 0);
            $grouped[$supervisorId][$plan->id] = ['name' => $site->name, 'due_at' => $plan->from->format('d/m/Y')];
        }

        return $grouped;
    }

    /*-------------------------------------------------------------------------------
     * Send one email for every Supervisor/provider group.
     *
     * Recipient behaviour:
     * - The relevant Supervisor is a required dynamic To recipient.
     * - The relevant scaffold provider contacts are required dynamic CCs.
     * - Kirstie, Ross and Damian are the original/legacy management CCs.
     * - Legacy mode keeps those original CCs.
     * - Append mode keeps them and adds dashboard recipients.
     * - Managed mode removes them and uses dashboard management recipients.
     *
     * Provider and Supervisor recipients remain dynamic in Managed mode so an
     * Ian report cannot accidentally be delivered to Ashbys, or vice versa.
     *------------------------------------------------------------------------------*/
    private function sendProviderReports(array $itemsBySupervisor, string $provider, array $providerRecipients): int
    {
        $emailsSent = 0;

        foreach ($itemsBySupervisor as $supervisorId => $items) {
            $supervisor = $supervisorId ? User::find($supervisorId) : null;
            $supervisorName = $supervisor?->firstname ?: 'No Allocated Supervisor';
            $mailable = new SiteScaffoldHandoverOutstanding($items, $provider, $supervisorName);

            // These are retained for Legacy/Append mode. Managed mode replaces
            // them with the To/CC rules configured in Scheduled Operations.
            $mailable->cc(['kirstie@capecod.com.au', 'ross@capecod.com.au', 'damian@capecod.com.au']);

            // The mailer exposes these record-specific recipients to the central
            // listener before it applies the configured recipient mode.
            $dynamicRecipients = array_merge(
                $this->recipientResolver->user('site_supervisor', 'Relevant Site Supervisor', $supervisor, 'to'),
                $providerRecipients
            );
            $this->mailer->send($mailable, $dynamicRecipients);

            echo "{$provider} => {$supervisorName}: " . count($items) . " item(s).\n";
            $emailsSent++;
        }

        return $emailsSent;
    }

    private function fixedRecipient(string $key, string $label, string $email): array
    {
        // Static provider addresses still use the dynamic-recipient context so
        // they remain tied to the correct provider-specific email in Managed mode.
        return ['key' => $key, 'label' => $label, 'type' => 'cc', 'email' => $email, 'name' => $label, 'required' => true, 'reason' => null];
    }

    private function itemCount(array $itemsBySupervisor): int
    {
        return array_sum(array_map('count', $itemsBySupervisor));
    }
}
