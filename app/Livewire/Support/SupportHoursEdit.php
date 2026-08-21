<?php

namespace App\Livewire\Support;

use App\Models\Support\SupportHour;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SupportHoursEdit extends Component
{
    public array $hours = [];

    public function mount(): void
    {
        abort_unless(Auth::user()->hasAnyRole2('web-admin'), 404);

        $this->loadHours();
    }

    protected function loadHours(): void
    {
        $this->hours = SupportHour::query()
            ->orderBy('order')
            ->get()
            ->map(fn (SupportHour $hour) => [
                'id' => (int)$hour->id,
                'day' => $hour->day,
                'h9_11' => (int)$hour->h9_11,
                'h11_1' => (int)$hour->h11_1,
                'h1_3' => (int)$hour->h1_3,
                'h3_5' => (int)$hour->h3_5,
                'order' => (int)$hour->order,
                'notes' => (string)($hour->notes ?? ''),
            ])
            ->all();
    }

    public function setDay(int $index, int $state): void
    {
        abort_unless(isset($this->hours[$index]), 404);
        abort_unless(in_array($state, [1, 2, 3], true), 422);

        foreach (['h9_11', 'h11_1', 'h1_3', 'h3_5'] as $field) {
            $this->hours[$index][$field] = $state;
        }
    }

    public function setHour(int $index, string $field): void
    {
        abort_unless(isset($this->hours[$index]), 404);
        abort_unless(in_array($field, ['h9_11', 'h11_1', 'h1_3', 'h3_5'], true), 404);

        $state = (int)$this->hours[$index][$field];

        // Preserve the old Vue behaviour:
        // grey/0 -> Busy/1 -> Available/2 -> Working/3 -> Busy/1.
        $this->hours[$index][$field] = $state === 3 ? 1 : $state + 1;
    }

    public function clearHours(): void
    {
        foreach ($this->hours as $index => $hour) {
            foreach (['h9_11', 'h11_1', 'h1_3', 'h3_5'] as $field) {
                $this->hours[$index][$field] = 0;
            }

            $this->hours[$index]['notes'] = '';
        }
    }

    public function defaultHours(): void
    {
        $defaults = [
            'Monday' => [3, 3, 3, 3],
            'Tuesday' => [2, 2, 2, 2],
            'Wednesday' => [1, 1, 2, 2],
            'Thursday' => [3, 3, 3, 3],
            'Friday' => [2, 2, 2, 2],
        ];

        foreach ($this->hours as $index => $hour) {
            $values = $defaults[$hour['day']] ?? [0, 0, 0, 0];

            $this->hours[$index]['h9_11'] = $values[0];
            $this->hours[$index]['h11_1'] = $values[1];
            $this->hours[$index]['h1_3'] = $values[2];
            $this->hours[$index]['h3_5'] = $values[3];
            $this->hours[$index]['notes'] = '';
        }
    }

    public function save()
    {
        abort_unless(Auth::user()->hasAnyRole2('web-admin'), 404);

        $this->validate([
            'hours' => ['required', 'array'],
            'hours.*.id' => ['required', 'integer'],
            'hours.*.day' => ['nullable', 'string', 'max:255'],
            'hours.*.h9_11' => ['required', 'integer', 'between:0,3'],
            'hours.*.h11_1' => ['required', 'integer', 'between:0,3'],
            'hours.*.h1_3' => ['required', 'integer', 'between:0,3'],
            'hours.*.h3_5' => ['required', 'integer', 'between:0,3'],
            'hours.*.notes' => ['nullable', 'string'],
        ]);

        $allowedIds = SupportHour::pluck('id')->map(fn ($id) => (int)$id)->all();
        $submittedIds = collect($this->hours)->pluck('id')->map(fn ($id) => (int)$id)->all();

        abort_unless(
            count($submittedIds) === count(array_unique($submittedIds))
            && empty(array_diff($submittedIds, $allowedIds)),
            422
        );

        DB::transaction(function () {
            foreach ($this->hours as $hourData) {
                $hour = SupportHour::query()->lockForUpdate()->findOrFail($hourData['id']);

                $hour->update([
                    'h9_11' => (int)$hourData['h9_11'],
                    'h11_1' => (int)$hourData['h11_1'],
                    'h1_3' => (int)$hourData['h1_3'],
                    'h3_5' => (int)$hourData['h3_5'],
                    'notes' => trim((string)$hourData['notes']) ?: null,
                ]);
            }
        });

        return redirect('/support/hours');
    }

    public function stateClass(int $state): string
    {
        return match ($state) {
            1 => 'state-red',
            2 => 'state-orange',
            3 => 'state-green',
            default => 'state-grey',
        };
    }

    public function stateText(int $state): string
    {
        return match ($state) {
            1 => 'Busy',
            2 => 'Available',
            3 => 'Working',
            default => 'Clear',
        };
    }

    public function render()
    {
        return view('livewire.support.support-hours-edit');
    }
}
