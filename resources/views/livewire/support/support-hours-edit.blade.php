<div>
    <style>
        .support-hours-help {
            margin-bottom:18px;
            padding:12px 14px;
            background:#f8fafb;
            border-left:3px solid #b8c1ca;
            color:#5f6b75;
        }

        .support-hours-table {
            margin-bottom:14px;
        }

        .support-hours-table > tbody > tr > td {
            background:#fff !important;
            vertical-align:middle !important;
        }

        .support-day {
            font-weight:600;
            color:#4b555f;
        }

        .support-day-presets {
            white-space:nowrap;
        }

        .support-state-dot {
            display:inline-block;
            width:22px;
            height:22px;
            margin-right:4px;
            padding:0;
            border:1px solid rgba(0,0,0,.08);
            border-radius:3px;
            cursor:pointer;
            vertical-align:middle;
        }

        .support-state-dot:hover,
        .support-state-dot:focus {
            outline:0;
            box-shadow:0 0 0 2px rgba(54,198,211,.15);
        }

        .support-hour-cell-td {
            position:relative;
            padding:0 !important;
        }

        .support-hour-cell {
            position:absolute;
            inset:0;
            display:block;
            width:100%;
            height:100%;
            min-width:70px;
            border:0;
            border-radius:0;
            cursor:pointer;
            font-size:12px;
            font-weight:600;
            color:#4b555f;
        }

        .support-hour-cell:hover,
        .support-hour-cell:focus {
            outline:0;
            box-shadow:inset 0 0 0 2px rgba(255,255,255,.65);
        }

        .state-red { background:#e26a6a !important; }
        .state-orange { background:#f4d03f !important; }
        .state-green { background:#36d7b7 !important; }
        .state-grey { background:#e9edef !important; }

        .support-hours-legend {
            margin:10px 0 18px;
            color:#68747e;
        }

        .support-hours-legend-item {
            display:inline-block;
            margin-right:22px;
            margin-bottom:6px;
        }

        .support-hours-legend-box {
            display:inline-block;
            width:18px;
            height:18px;
            margin-right:5px;
            border:1px solid rgba(0,0,0,.08);
            vertical-align:middle;
        }

        .support-hours-actions {
            min-height:38px;
            margin-top:10px;
        }

        @media (max-width:767px) {
            .support-hours-table {
                min-width:850px;
            }
        }
    </style>

    <div class="support-hours-help">
        Click a coloured square beside a day to set the whole day, or click an individual time block to cycle <strong>Busy → Available → Working</strong>.
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-nohover support-hours-table">
            <thead>
            <tr class="mytable-header">
                <th style="width:125px">Day</th>
                <th style="width:120px">Set day</th>
                <th style="width:110px" class="text-center">9 - 11</th>
                <th style="width:110px" class="text-center">11 - 1</th>
                <th style="width:110px" class="text-center">1 - 3</th>
                <th style="width:110px" class="text-center">3 - 5</th>
                <th>Comments</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($hours as $index => $hour)
                <tr wire:key="support-hour-{{ $hour['id'] }}">
                    <td class="support-day">{{ $hour['day'] }}</td>

                    <td class="support-day-presets">
                        <button type="button" class="support-state-dot state-red" wire:click="setDay({{ $index }}, 1)" title="Set {{ $hour['day'] }} to Busy"></button>
                        <button type="button" class="support-state-dot state-orange" wire:click="setDay({{ $index }}, 2)" title="Set {{ $hour['day'] }} to Available"></button>
                        <button type="button" class="support-state-dot state-green" wire:click="setDay({{ $index }}, 3)" title="Set {{ $hour['day'] }} to Working"></button>
                    </td>

                    @foreach (['h9_11', 'h11_1', 'h1_3', 'h3_5'] as $field)
                        @php
                            $state = (int)$hour[$field];
                        @endphp
                        <td class="support-hour-cell-td">
                            <button type="button" class="support-hour-cell {{ $this->stateClass($state) }}" wire:click="setHour({{ $index }}, '{{ $field }}')" title="{{ $this->stateText($state) }}" aria-label="{{ $this->stateText($state) }}"></button>
                        </td>
                    @endforeach

                    <td>
                        <input type="text" class="form-control" wire:model.defer="hours.{{ $index }}.notes" placeholder="Optional comment">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="support-hours-legend">
        <span class="support-hours-legend-item"><span class="support-hours-legend-box state-red"></span> Busy / unavailable</span>
        <span class="support-hours-legend-item"><span class="support-hours-legend-box state-orange"></span> Available to work</span>
        <span class="support-hours-legend-item"><span class="support-hours-legend-box state-green"></span> Working</span>
        <span class="support-hours-legend-item"><span class="support-hours-legend-box state-grey"></span> Clear</span>
    </div>

    <div class="clearfix support-hours-actions">
        <div class="pull-left">
            <button type="button" class="btn default" wire:click="clearHours">Clear</button>
            <button type="button" class="btn dark" wire:click="defaultHours">Default</button>
        </div>

        <div class="pull-right">
            <a href="/support/hours" class="btn default">Cancel</a>
            <button type="button" class="btn green" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save"><i class="fa fa-spinner fa-pulse"></i> Saving...</span>
            </button>
        </div>
    </div>
</div>
