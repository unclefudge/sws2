<?php

namespace App\Scheduled\Reports;

use App\Mail\Misc\EquipmentTransfers;
use App\Models\Company\Company;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class EquipmentTransfersReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.equipment_transfers',
            'name' => 'Equipment transfers',
            'category' => 'report',
            'description' => 'Emails a PDF containing equipment transfers recorded during the previous seven days.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Notification group: equipment.transfers',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Build the equipment transfer PDF and send it when transfers exist.
     */
    public function handle(): int
    {
        $to = Carbon::now();
        $from = $to->copy()->subDays(7);
        $transactions = EquipmentLog::query()->where('action', 'T')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->get();
        echo "Equipment transfer transactions: {$transactions->count()}\n";

        if ($transactions->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        // Use the report's existing notification group. If Append or Managed is
        // selected in the dashboard, those recipient changes are applied automatically.
        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('equipment.transfers');
        $directory = storage_path('app/tmp');
        File::ensureDirectoryExists($directory);
        $file = $directory . '/equipment-transfers-' . $to->format('Ymd-His-u') . '.pdf';

        try {
            \PDF::loadView('pdf/equipment-transfers', ['transactions' => $transactions, 'from' => $from, 'to' => $to])->setPaper('A4', 'portrait')->save($file);

            $mailable = new EquipmentTransfers($file, $transactions);
            if ($emailList) $mailable->to($emailList);
            $mailable->send(app('mailer'));

            echo "Equipment Transfers report sent.\n";
        } finally {
            File::delete($file);
        }

        return 1;
    }
}
