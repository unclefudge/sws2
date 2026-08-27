<?php

namespace App\Scheduled\Reports;

use App\Models\Company\Company;
use App\Models\Misc\Equipment\Equipment;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Illuminate\Support\Facades\Mail;

class EquipmentRestockReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.equipment_restock',
            'name' => 'Equipment restock',
            'category' => 'report',
            'description' => 'Emails a list of active equipment whose stock is below its minimum level.',
            'schedule' => [
                'type' => 'weekly',
                'weekdays' => [5], // Friday. The dashboard can change this later.
                'time' => '00:05',
            ],
            'recipients' => 'Notification group: equipment.restock',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Find equipment that needs restocking and email the list when required.
     */
    public function handle(): int
    {
        $equipment = Equipment::query()->whereNotNull('min_stock')->where('status', 1)->orderBy('name')->get()->filter(fn(Equipment $item) => $item->total < $item->min_stock)->values();
        echo "Equipment requiring restock: {$equipment->count()}\n";

        if ($equipment->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        $capeCod = Company::findOrFail(3);

        // Use the report's existing notification group. If Append or Managed is
        // selected in the dashboard, those recipient changes are applied automatically.
        $emailList = app()->environment('prod') ? $capeCod->notificationsUsersEmailType('equipment.restock') : [config('mail.email_dev')];

        Mail::send('emails/misc/equipment-restock', ['data' => $equipment], function ($message) use ($emailList) {
            $message->from('do-not-reply@safeworksite.com.au', 'Safe Worksite');
            if ($emailList) $message->to($emailList);
            $message->subject('SafeWorksite - Equipment Restock');
        });

        echo "Equipment Restock report sent.\n";

        return 1;
    }
}
