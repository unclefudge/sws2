<?php

namespace App\Livewire\Manage\Report;

use App\Models\Misc\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RecentReports extends Component
{
    public function render()
    {
        $reports = Report::query()
            ->where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subDays(10))
            ->latest('id')
            ->get(['id', 'name', 'status', 'created_at']);

        return view('livewire.manage.report.recent-reports', [
            'reports' => $reports,
            'hasPending' => $reports->contains(fn ($report) => in_array($report->status, ['pending', 'processing'], true)),
        ]);
    }
}
