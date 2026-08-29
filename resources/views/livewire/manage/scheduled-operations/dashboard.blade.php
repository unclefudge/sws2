<div class="scheduled-ops" wire:poll.15s>
    @php
        // wire:ignore lets Bootstrap Select own its generated markup. Changing
        // this fingerprint deliberately rebuilds category selects after the
        // category manager adds, renames, reorders or disables an option.
        $categorySelectVersion = md5($categories->map(
            fn($category) => implode('|', [$category->id, $category->slug, $category->name, (int) $category->enabled, $category->sort_order])
        )->join(';'));
    @endphp
    <style>
        .scheduled-ops .ops-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .scheduled-ops .ops-title-row h2 {
            margin: 0;
            color: #46515f;
            font-weight: 600;
        }

        .scheduled-ops .ops-mode {
            padding: 7px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .scheduled-ops .ops-mode-legacy {
            background: #fff4d4;
            color: #8a6d1f;
        }

        .scheduled-ops .ops-mode-shadow {
            background: #e9f2fb;
            color: #3977a8;
        }

        .scheduled-ops .ops-mode-live {
            background: #e5f6ec;
            color: #267747;
        }

        .scheduled-ops .ops-banner {
            margin-bottom: 20px;
            padding: 14px 17px;
            border-left: 4px solid #36c6d3;
            background: #f4f8fa;
            color: #5d6874;
        }

        .scheduled-ops .ops-heartbeat {
            display: block;
            margin-top: 7px;
            font-size: 12px;
        }

        .scheduled-ops .ops-heartbeat-ok {
            color: #267747;
        }

        .scheduled-ops .ops-heartbeat-warning {
            color: #b83e48;
            font-weight: 600;
        }

        .scheduled-ops .ops-stats {
            display: grid;
            grid-template-columns:repeat(4, minmax(130px, 1fr));
            gap: 12px;
            margin-bottom: 22px;
        }

        .scheduled-ops .ops-stat {
            padding: 15px 17px;
            border: 1px solid #e3e7ea;
            border-radius: 7px;
            background: #fff;
        }

        .scheduled-ops .ops-stat strong {
            display: block;
            color: #35404b;
            font-size: 25px;
            line-height: 1;
        }

        .scheduled-ops .ops-stat span {
            display: block;
            margin-top: 7px;
            color: #7a858f;
            font-size: 12px;
            text-transform: uppercase;
        }

        .scheduled-ops .ops-stat-danger {
            border-color: #e7505a;
            background: #fde7e9;
        }

        .scheduled-ops .ops-stat-danger strong, .scheduled-ops .ops-stat-danger span {
            color: #b83e48;
        }

        .scheduled-ops .ops-tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 18px;
            border-bottom: 1px solid #e2e6e9;
        }

        .scheduled-ops .ops-tab-tools {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin: -8px 0 14px;
        }

        .scheduled-ops .ops-schedule-search {
            flex: 1 1 320px;
            min-width: 220px;
            max-width: 520px;
            margin-right: auto;
        }

        .scheduled-ops .ops-archive-toggle {
            white-space: nowrap;
        }

        .scheduled-ops .ops-sort-toggle {
            min-width: 105px;
            white-space: nowrap;
        }

        .scheduled-ops .ops-sort-toggle.is-name {
            border-color: #36c6d3;
            background: #e8f8fa;
            color: #279aa5;
        }

        .scheduled-ops .ops-archive-toggle.is-active {
            border-color: #36c6d3;
            background: #e8f8fa;
            color: #279aa5;
        }

        .scheduled-ops .ops-tab {
            padding: 11px 18px;
            border: 0;
            border-bottom: 3px solid transparent;
            background: transparent;
            color: #6a747e;
            font-weight: 600;
        }

        .scheduled-ops .ops-tab.active {
            border-color: #36c6d3;
            color: #2b9faa;
        }

        .scheduled-ops .ops-filters {
            display: grid;
            grid-template-columns:minmax(200px, 2fr) minmax(145px, .8fr) minmax(150px, 1fr) minmax(190px, 1.15fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .scheduled-ops .form-control {
            height: 42px;
            border: 1px solid #c9d2dc;
            border-radius: 0;
            box-shadow: none;
            color: #5d6873;
            background-color: #fff;
        }

        .scheduled-ops .form-control:focus {
            border-color: #36c6d3;
            box-shadow: 0 0 0 1px rgba(54, 198, 211, .15);
        }

        .scheduled-ops .ops-select-host {
            min-width: 0;
        }

        .scheduled-ops .ops-select-host .bootstrap-select {
            width: 100% !important;
        }

        /* Keep the same Bootstrap Select skin already used by the planners. */
        .scheduled-ops .ops-select-host .bootstrap-select > .dropdown-toggle {
            min-height: 42px;
            border: 1px solid #c9d2dc !important;
            border-radius: 0;
            background: #fff !important;
            outline: 0 !important;
            box-shadow: none !important;
        }

        .scheduled-ops .ops-select-host .bootstrap-select.open > .dropdown-toggle,
        .scheduled-ops .ops-select-host .bootstrap-select > .dropdown-toggle:focus {
            border-color: #36c6d3 !important;
            outline: 0 !important;
            box-shadow: none !important;
        }

        .scheduled-ops .ops-select-host .bootstrap-select .dropdown-menu {
            z-index: 100060;
        }

        .scheduled-ops .ops-select-host .bootstrap-select .bs-searchbox input {
            height: 38px;
        }

        .scheduled-ops select.ops-select {
            min-height: 42px;
        }

        .scheduled-ops .ops-select-host .select2-container {
            width: 100% !important;
        }

        .scheduled-ops .ops-select-host .select2-container--default .select2-selection--multiple {
            min-height: 42px;
            border: 1px solid #c9d2dc;
            border-radius: 0;
        }

        .scheduled-ops .ops-select-host .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #36c6d3;
        }

        .scheduled-ops .help-block {
            display: block;
            margin: 6px 0 0;
            color: #e7505a;
            font-size: 12px;
            font-weight: 600;
        }

        .scheduled-ops .ops-table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e6e9;
            border-radius: 7px;
        }

        .scheduled-ops .ops-table {
            width: 100%;
            margin: 0;
        }

        .scheduled-ops .ops-table th {
            padding: 11px 12px;
            background: #edf4f9;
            color: #46515f;
            white-space: nowrap;
        }

        .scheduled-ops .ops-table td {
            padding: 8px 12px;
            border-top: 1px solid #e8ebed;
            color: #5d6873;
            vertical-align: middle;
        }

        .scheduled-ops .ops-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 15px;
            color: #7a858f;
            font-size: 12px;
        }

        .scheduled-ops .ops-page-buttons {
            display: flex;
            gap: 4px;
        }

        .scheduled-ops .ops-page-btn {
            min-width: 35px;
            height: 35px;
            padding: 0 10px;
            border: 1px solid #d4dade;
            border-radius: 3px;
            background: #fff;
            color: #596570;
            font-weight: 600;
        }

        .scheduled-ops .ops-page-btn:hover:not(:disabled) {
            border-color: #36c6d3;
            color: #2b9faa;
        }

        .scheduled-ops .ops-page-btn.is-active {
            border-color: #36c6d3;
            background: #36c6d3;
            color: #fff;
        }

        .scheduled-ops .ops-page-btn:disabled {
            background: #f1f3f4;
            color: #a4abb1;
            cursor: not-allowed;
        }

        .scheduled-ops .ops-name {
            color: #35404b;
            font-weight: 600;
        }

        .scheduled-ops .ops-disabled-label {
            margin-left: 7px;
        }

        .scheduled-ops .ops-key {
            display: block;
            margin-top: 3px;
            color: #99a2aa;
            font-family: monospace;
            font-size: 11px;
        }

        .scheduled-ops .ops-status {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .scheduled-ops .status-successful {
            background: #e4f6ea;
            color: #28784a;
        }

        .scheduled-ops .status-failed, .scheduled-ops .status-partial, .scheduled-ops .status-missed {
            background: #fde7e9;
            color: #b83e48;
        }

        .scheduled-ops .status-running, .scheduled-ops .status-queued {
            background: #e7f2fb;
            color: #3378aa;
        }

        .scheduled-ops .status-shadow, .scheduled-ops .status-skipped {
            background: #f0f1f2;
            color: #747d85;
        }

        .scheduled-ops .ops-btn {
            padding: 7px 11px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-weight: 600;
        }

        .scheduled-ops .ops-btn-small {
            padding: 4px 8px;
            font-size: 12px;
        }

        .scheduled-ops .ops-btn-primary {
            background: #36c6d3;
            color: #fff;
        }

        .scheduled-ops .ops-btn-light {
            border-color: #d4dade;
            background: #fff;
            color: #596570;
        }

        .scheduled-ops .ops-btn-danger {
            border-color: #e7505a;
            background: #fff;
            color: #b83e48;
        }

        .scheduled-ops .ops-category-section {
            margin-top: 12px;
            border: 1px solid #e3e7ea;
            border-radius: 7px;
            background: #fff;
            overflow: hidden;
        }

        .scheduled-ops .ops-category-toggle {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 13px 15px;
            border: 0;
            background: #edf4f9;
            color: #46515f;
            text-align: left;
        }

        .scheduled-ops .ops-category-toggle strong {
            font-size: 17px;
            text-transform: capitalize;
        }

        .scheduled-ops .ops-category-toggle small {
            margin-left: 8px;
            color: #86919a;
            font-weight: 400;
        }

        .scheduled-ops .ops-category-toggle:hover {
            background: #e5eff6;
        }

        .scheduled-ops .ops-category-section .ops-schedule-grid {
            padding: 0 10px;
        }

        .scheduled-ops .ops-schedule-grid {
            display: grid;
            gap: 0;
        }

        .scheduled-ops .ops-schedule {
            display: grid;
            grid-template-columns:minmax(220px, 1.1fr) minmax(190px, .8fr) minmax(260px, 1.4fr) auto;
            gap: 14px;
            align-items: center;
            padding: 12px 5px;
            border: 0;
            border-top: 1px solid #e3e7ea;
            border-radius: 0;
            background: #fff;
        }

        .scheduled-ops .ops-schedule:first-child {
            border-top: 0;
        }

        .scheduled-ops .ops-schedule-description {
            display: block;
            margin-top: 3px;
            color: #7a858f;
            font-size: 12px;
            line-height: 1.4;
        }

        .scheduled-ops .ops-recipient {
            color: #7a858f;
            font-size: 13px;
        }

        .scheduled-ops .ops-recipient-mode {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 7px;
            border-radius: 10px;
            background: #eef2f4;
            color: #64717d;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .scheduled-ops .ops-handler-info {
            display: block;
            margin-top: 7px;
        }

        .scheduled-ops .ops-handler-badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .scheduled-ops .ops-handler-scheduled {
            background: #e4f6ea;
            color: #28784a;
        }

        .scheduled-ops .ops-handler-legacy {
            background: #fff4d4;
            color: #8a6d1f;
        }

        .scheduled-ops .ops-handler-missing {
            background: #fde7e9;
            color: #b83e48;
        }

        .scheduled-ops .ops-handler-code {
            display: block;
            margin-top: 4px;
            color: #8a949c;
            font-family: monospace;
            font-size: 10px;
            overflow-wrap: anywhere;
        }

        .scheduled-ops .ops-off {
            opacity: .55;
        }

        .scheduled-ops .ops-flash {
            margin-bottom: 15px;
            padding: 11px 14px;
            border-radius: 5px;
            background: #e5f6ec;
            color: #267747;
        }

        .scheduled-ops .ops-flash-error {
            background: #fde7e9;
            color: #b83e48;
        }

        .scheduled-ops .ops-archive-panel {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #dce3e7;
        }

        .scheduled-ops .ops-archive-panel > div > strong {
            display: block;
            color: #46515f;
        }

        .scheduled-ops .ops-archive-panel > div > span {
            display: block;
            margin-top: 3px;
            color: #7a858f;
            font-size: 12px;
        }

        .scheduled-ops .ops-client-setting {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .scheduled-ops .ops-client-setting .ops-status-toggle {
            margin: 0;
        }

        .scheduled-ops .sws-modal-card {
            border: 0;
        }

        .scheduled-ops .sws-modal-header {
            padding: 18px 64px 18px 22px;
            background: #46515f;
            border-bottom: 0;
        }

        .scheduled-ops .sws-modal-title, .scheduled-ops .sws-modal-close {
            color: #fff;
        }

        .scheduled-ops .sws-modal-close {
            top: 16px;
            right: 22px;
            width: 38px;
            height: 38px;
            border-radius: 0;
            background: rgba(255, 255, 255, .12);
            font-size: 20px;
            line-height: 38px;
        }

        .scheduled-ops .sws-modal-close:hover, .scheduled-ops .sws-modal-close:focus {
            background: rgba(255, 255, 255, .22);
            color: #fff;
        }

        .scheduled-ops .ops-detail-grid {
            display: grid;
            grid-template-columns:repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }

        .scheduled-ops .ops-detail {
            padding: 11px;
            background: #f3f5f6;
            border-radius: 5px;
        }

        .scheduled-ops .ops-detail span {
            display: block;
            color: #929ba3;
            font-size: 11px;
            text-transform: uppercase;
        }

        .scheduled-ops .ops-detail strong {
            display: block;
            margin-top: 4px;
            color: #46515f;
            overflow-wrap: anywhere;
        }

        .scheduled-ops .ops-detail-status {
            border-left: 4px solid #a7b0b8;
        }

        .scheduled-ops .ops-detail-status-successful {
            border-color: #36a866;
            background: #e5f6ec;
        }

        .scheduled-ops .ops-detail-status-successful strong {
            color: #267747;
        }

        .scheduled-ops .ops-detail-status-queued, .scheduled-ops .ops-detail-status-running {
            border-color: #e89b2c;
            background: #fff3df;
        }

        .scheduled-ops .ops-detail-status-queued strong, .scheduled-ops .ops-detail-status-running strong {
            color: #a65d00;
        }

        .scheduled-ops .ops-detail-status-failed, .scheduled-ops .ops-detail-status-partial, .scheduled-ops .ops-detail-status-missed {
            border-color: #e7505a;
            background: #fde7e9;
        }

        .scheduled-ops .ops-detail-status-failed strong, .scheduled-ops .ops-detail-status-partial strong, .scheduled-ops .ops-detail-status-missed strong {
            color: #b83e48;
        }

        .scheduled-ops .ops-detail-status-shadow {
            border-color: #4f94c8;
            background: #e9f2fb;
        }

        .scheduled-ops .ops-detail-status-shadow strong {
            color: #3977a8;
        }

        .scheduled-ops .ops-detail-status-skipped {
            border-color: #a7b0b8;
            background: #f0f1f2;
        }

        .scheduled-ops .ops-detail-status-skipped strong {
            color: #68737d;
        }

        .scheduled-ops .ops-output {
            max-height: 240px;
            overflow: auto;
            padding: 13px;
            background: #25303a;
            color: #e5ebef;
            border-radius: 5px;
            white-space: pre-wrap;
            font: 12px/1.5 monospace;
        }

        .scheduled-ops .ops-error {
            margin: 12px 0;
            padding: 12px;
            border-left: 4px solid #e7505a;
            background: #fff2f3;
            color: #9b323a;
            overflow-wrap: anywhere;
        }

        .scheduled-ops .ops-mail {
            margin-top: 10px;
            padding: 11px 13px;
            border: 1px solid #e2e6e9;
            border-radius: 5px;
        }

        .scheduled-ops .ops-mail strong {
            color: #46515f;
        }

        .scheduled-ops .ops-mail small {
            display: block;
            margin-top: 4px;
            color: #8a949c;
        }

        .scheduled-ops .ops-form-grid {
            display: grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .scheduled-ops .ops-form-grid-3 {
            display: grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .scheduled-ops .ops-form-span-2 {
            grid-column: span 2;
        }

        .scheduled-ops .ops-category-field {
            display: flex;
            align-items: stretch;
            gap: 7px;
        }

        .scheduled-ops .ops-category-field .ops-select-host {
            flex: 1;
            min-width: 0;
        }

        .scheduled-ops .ops-advanced-toggle {
            margin: 8px 0 2px;
            border: 0;
            background: transparent;
            color: #329ba5;
            font-weight: 600;
            padding: 4px 0;
        }

        .scheduled-ops .ops-advanced {
            margin-top: 9px;
            padding: 14px;
            border: 1px solid #dce3e7;
            border-radius: 6px;
            background: #f7f9fa;
        }

        .scheduled-ops .ops-status-toggle {
            position: relative;
            display: inline-block;
            margin: 2px 0 12px;
            cursor: pointer;
        }

        .scheduled-ops .ops-status-toggle > input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        .scheduled-ops .ops-status-track {
            display: grid;
            grid-template-columns:1fr 1fr;
            width: 190px;
            overflow: hidden;
            border: 1px solid #ccd3d8;
            border-radius: 5px;
            background: #edf0f2;
        }

        .scheduled-ops .ops-status-track span {
            padding: 9px 13px;
            color: #7b858d;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            transition: background .12s ease, color .12s ease;
        }

        .scheduled-ops .ops-status-toggle > input:not(:checked) + .ops-status-track .ops-status-disabled {
            background: #e7505a;
            color: #fff;
        }

        .scheduled-ops .ops-status-toggle > input:checked + .ops-status-track .ops-status-enabled {
            background: #26a65b;
            color: #fff;
        }

        .scheduled-ops .ops-status-toggle > input:focus + .ops-status-track {
            box-shadow: 0 0 0 3px rgba(54, 198, 211, .18);
        }

        .scheduled-ops .ops-day-toggle, .scheduled-ops .ops-month-toggle {
            display: inline-flex;
            width: 100%;
        }

        .scheduled-ops .ops-day-toggle label, .scheduled-ops .ops-month-toggle label {
            position: relative;
            flex: 1;
            min-width: 0;
            margin: 0;
            cursor: pointer;
        }

        .scheduled-ops .ops-day-toggle input, .scheduled-ops .ops-month-toggle input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        .scheduled-ops .ops-day-toggle span, .scheduled-ops .ops-month-toggle span {
            display: flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            margin-left: -1px;
            padding: 9px 6px;
            border: 1px solid #ccd3d8;
            background: #e8ebed;
            color: #68737d;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            transition: background .12s ease, color .12s ease, border-color .12s ease;
        }

        .scheduled-ops .ops-day-toggle label:first-child span, .scheduled-ops .ops-month-toggle label:first-child span {
            margin-left: 0;
            border-radius: 5px 0 0 5px;
        }

        .scheduled-ops .ops-day-toggle label:last-child span, .scheduled-ops .ops-month-toggle label:last-child span {
            border-radius: 0 5px 5px 0;
        }

        .scheduled-ops .ops-day-toggle input:checked + span, .scheduled-ops .ops-month-toggle input:checked + span {
            position: relative;
            z-index: 1;
            border-color: #46515f;
            background: #46515f;
            color: #fff;
        }

        .scheduled-ops .ops-day-toggle input:focus + span, .scheduled-ops .ops-month-toggle input:focus + span {
            position: relative;
            z-index: 2;
            box-shadow: 0 0 0 3px rgba(54, 198, 211, .18);
        }

        .scheduled-ops .ops-month-toggle {
            overflow-x: auto;
        }

        .scheduled-ops .ops-month-toggle label {
            min-width: 52px;
        }

        .scheduled-ops .ops-help {
            color: #7a858f;
            font-size: 12px;
            line-height: 1.45;
        }

        .scheduled-ops .ops-recipient-panel {
            margin-top: 18px;
            padding: 15px;
            border: 1px solid #dce3e7;
            border-radius: 7px;
            background: #f7f9fa;
        }

        .scheduled-ops .ops-rule {
            display: grid;
            grid-template-columns:85px 150px minmax(260px, 1fr) auto;
            gap: 8px;
            align-items: start;
            margin-top: 9px;
        }

        .scheduled-ops .ops-rule .form-control {
            width: 100%;
        }

        .scheduled-ops .ops-rule-remove {
            min-height: 40px;
            color: #b83e48;
        }

        .scheduled-ops .ops-category-sort {
            border-top: 1px solid #e4e8eb;
        }

        .scheduled-ops .ops-category-row {
            display: grid;
            grid-template-columns:auto auto minmax(180px, 1fr) minmax(130px, .7fr) auto;
            gap: 8px;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid #e4e8eb;
            background: #fff;
            transition: opacity .15s, transform .15s, box-shadow .15s;
        }

        .scheduled-ops .ops-category-row.is-dragging {
            opacity: .4;
        }

        .scheduled-ops .ops-category-row.is-drag-over {
            transform: translateY(2px);
            box-shadow: 0 -3px 0 #36c6d3;
        }

        .scheduled-ops .ops-category-row .ops-slug {
            color: #9099a1;
            font-family: monospace;
            font-size: 12px;
        }

        .scheduled-ops .ops-drag-handle, .scheduled-ops .ops-visibility {
            display: inline-flex;
            width: 39px;
            height: 39px;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 1px solid #d4dade;
            border-radius: 4px;
            background: #fff;
            color: #66727d;
        }

        .scheduled-ops .ops-drag-handle {
            cursor: grab;
        }

        .scheduled-ops .ops-drag-handle:active {
            cursor: grabbing;
        }

        .scheduled-ops .ops-visibility.is-enabled {
            border-color: #36c6d3;
            background: #e8f8fa;
            color: #279aa5;
        }

        .scheduled-ops .ops-visibility.is-disabled {
            background: #edf0f2;
            color: #929ba3;
        }

        .scheduled-ops .ops-activity {
            margin-top: 16px;
            padding-top: 13px;
            border-top: 1px solid #e2e6e9;
            color: #7a858f;
            font-size: 12px;
        }

        .scheduled-ops .ops-activity div + div {
            margin-top: 5px;
        }

        .scheduled-ops .ops-handler {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 13px;
            border: 1px solid #e2e6e9;
            border-radius: 6px;
        }

        .scheduled-ops .ops-handler + .ops-handler {
            margin-top: 9px;
        }

        @media (max-width: 850px) {
            .scheduled-ops .ops-stats {
                grid-template-columns:repeat(2, 1fr);
            }

            .scheduled-ops .ops-schedule {
                grid-template-columns:1fr;
            }

            .scheduled-ops .ops-filters {
                grid-template-columns:1fr;
            }

            .scheduled-ops .ops-rule {
                grid-template-columns:1fr 1fr;
            }

            .scheduled-ops .ops-rule > :nth-child(3) {
                grid-column: span 2;
            }

            .scheduled-ops .ops-form-grid-3 {
                grid-template-columns:1fr;
            }

            .scheduled-ops .ops-category-row {
                grid-template-columns:auto auto 1fr;
            }

            .scheduled-ops .ops-category-row > :nth-child(4), .scheduled-ops .ops-category-row > :nth-child(5) {
                grid-column: 3;
            }
        }

        @media (max-width: 550px) {
            .scheduled-ops .ops-title-row {
                align-items: flex-start;
            }

            .scheduled-ops .ops-detail-grid {
                grid-template-columns:1fr;
            }

            .scheduled-ops .ops-form-grid {
                grid-template-columns:1fr;
            }

            .scheduled-ops .ops-form-span-2 {
                grid-column: auto;
            }

            .scheduled-ops .ops-tab-tools {
                align-items: stretch;
                flex-direction: column;
            }

            .scheduled-ops .ops-schedule-search {
                flex: auto;
                width: 100%;
                max-width: none;
            }

            .scheduled-ops .ops-archive-panel {
                align-items: stretch;
                flex-direction: column;
            }

            .scheduled-ops .ops-pagination {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    <div class="portlet light">
        <div class="portlet-body">
            <div class="ops-title-row">
                <h2>Scheduled Operations</h2>
                <span class="ops-mode ops-mode-{{ $mode }}">{{ $mode }} mode</span>
            </div>

            <div class="ops-banner">
                @if($mode === 'legacy')
                    The original nightly and hourly controllers are still running. This dashboard is ready for testing but will not replace them until the environment is deliberately changed.
                @elseif($mode === 'shadow')
                    Shadow mode records which independent jobs would run while the original cron remains live. No new jobs are executed automatically.
                @else
                    Live mode is active. Scheduled work is dispatched as independent queue jobs and failures are monitored automatically.
                @endif

                @if(in_array($mode, ['shadow','live'], true))
                    @php
                        $heartbeatFresh = $heartbeat?->last_success_at && $heartbeat->last_success_at->gte(now()->subMinutes(3));
                    @endphp
                    <span class="ops-heartbeat {{ $heartbeatFresh ? 'ops-heartbeat-ok' : 'ops-heartbeat-warning' }}">
                        <i class="fa {{ $heartbeatFresh ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                        {{ $heartbeat?->last_success_at ? 'Scheduler last checked '.$heartbeat->last_success_at->format('d/m/Y g:i:s a') : 'The new scheduler has not checked in yet.' }}
                    </span>
                @endif
            </div>

            @if(session()->has('scheduled-success'))
                <div class="ops-flash"><i class="fa fa-check-circle"></i> {{ session('scheduled-success') }}</div>
            @endif
            @if(session()->has('scheduled-error'))
                <div class="ops-flash ops-flash-error"><i class="fa fa-exclamation-triangle"></i> {{ session('scheduled-error') }}</div>
            @endif

            <div class="ops-stats">
                <div class="ops-stat"><strong>{{ $stats['total'] }}</strong><span>Runs {{ $stats['date_label'] }}</span></div>
                <div class="ops-stat"><strong>{{ $stats['successful'] }}</strong><span>Successful</span></div>
                <div class="ops-stat"><strong>{{ $stats['running'] }}</strong><span>Queued / running</span></div>
                <div class="ops-stat {{ $stats['failed'] > 0 ? 'ops-stat-danger' : '' }}"><strong>{{ $stats['failed'] }}</strong><span>Failed / missed</span></div>
            </div>

            <div class="ops-tabs">
                <button class="ops-tab {{ $activeTab === 'runs' ? 'active' : '' }}" wire:click="$set('activeTab','runs')">Run history</button>
                <button class="ops-tab {{ $activeTab === 'schedules' ? 'active' : '' }}" wire:click="$set('activeTab','schedules')">Schedules &amp; recipients</button>
            </div>

            @if($activeTab === 'runs')
                <div class="ops-filters">
                    <input type="search" class="form-control" placeholder="Search operation name" wire:model.live.debounce.300ms="search">
                    <input type="date" class="form-control" wire:model.live="dateFilter" aria-label="Run date">
                    <div class="ops-select-host" wire:key="run-status-filter-{{ $statusFilter }}" wire:ignore>
                        <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('statusFilter', $el.value)">
                            <option value="">All statuses</option>
                            @foreach(['queued','running','successful','failed','missed','shadow','skipped'] as $status)
                                <option value="{{ $status }}" @selected($statusFilter === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ops-select-host" wire:key="run-category-filter-{{ $categorySelectVersion }}-{{ $categoryFilter }}" wire:ignore>
                        <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('categoryFilter', $el.value)">
                            <option value="except_hourly" @selected($categoryFilter === 'except_hourly')>All categories except Hourly</option>
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}" @selected($categoryFilter === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead>
                        <tr>
                            <th>Operation</th>
                            <th>Scheduled</th>
                            <th>Trigger</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th>Emails</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($runs as $run)
                            <tr>
                                <td><span class="ops-name">{{ $run->task_name }}</span></td>
                                <td>{{ optional($run->scheduled_for)->format('d/m/Y g:i a') }}</td>
                                <td>{{ ucfirst($run->trigger) }}</td>
                                <td><span class="ops-status status-{{ $run->status }}">{{ $run->status }}</span></td>
                                <td>{{ $run->duration_ms !== null ? number_format($run->duration_ms / 1000, 2).'s' : '—' }}</td>
                                <td>{{ $run->messages->where('status','sent')->count() }}</td>
                                <td>
                                    <button class="ops-btn ops-btn-light ops-btn-small" wire:click="showRun({{ $run->id }})">Details</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No scheduled run history matches these filters.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($runs->hasPages())
                    <div class="ops-pagination">
                        <span>Showing {{ $runs->firstItem() }} to {{ $runs->lastItem() }} of {{ $runs->total() }} results</span>
                        <div class="ops-page-buttons">
                            <button class="ops-page-btn" type="button" wire:click="previousPage('runsPage')" wire:loading.attr="disabled" @disabled($runs->onFirstPage()) aria-label="Previous page"><i class="fa fa-chevron-left"></i></button>
                            @foreach(range(1, $runs->lastPage()) as $page)
                                <button class="ops-page-btn {{ $runs->currentPage() === $page ? 'is-active' : '' }}" type="button" wire:click="gotoPage({{ $page }}, 'runsPage')" wire:loading.attr="disabled" aria-label="Page {{ $page }}"
                                        @if($runs->currentPage() === $page) aria-current="page" @endif>{{ $page }}</button>
                            @endforeach
                            <button class="ops-page-btn" type="button" wire:click="nextPage('runsPage')" wire:loading.attr="disabled" @disabled(!$runs->hasMorePages()) aria-label="Next page"><i class="fa fa-chevron-right"></i></button>
                        </div>
                    </div>
                @endif
            @else
                <div class="ops-tab-tools">
                    <input type="search" class="form-control ops-schedule-search" placeholder="Search schedule, recipient or handler" wire:model.live.debounce.300ms="scheduleSearch">
                    <button type="button" class="ops-btn ops-btn-light ops-archive-toggle {{ $includeArchived ? 'is-active' : '' }}" wire:click="$toggle('includeArchived')" aria-pressed="{{ $includeArchived ? 'true' : 'false' }}"
                            title="{{ $includeArchived ? 'Hide archived operations' : 'Show archived operations' }}">
                        <i class="fa {{ $includeArchived ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                    </button>
                    <button type="button" class="ops-btn ops-btn-light ops-sort-toggle {{ $scheduleSort === 'name' ? 'is-name' : '' }}" wire:click="toggleScheduleSort"
                            title="Switch to {{ $scheduleSort === 'name' ? 'day/schedule' : 'name' }} order" aria-label="Currently sorted by {{ $scheduleSort === 'name' ? 'name' : 'day and schedule' }}; switch order">
                        <i class="fa {{ $scheduleSort === 'name' ? 'fa-sort-alpha-asc' : 'fa-calendar' }}"></i> {{ $scheduleSort === 'name' ? 'Name order' : 'Day order' }}
                    </button>
                    <a class="ops-btn ops-btn-light" href="/settings/notifications"><i class="fa fa-envelope"></i> Notifications</a>
                    <button class="ops-btn ops-btn-light" wire:click="openCategoryManager"><i class="fa fa-folder-open"></i> Categories</button>
                    <button class="ops-btn ops-btn-primary" wire:click="openAddOperation"><i class="fa fa-plus"></i> Add operation</button>
                </div>
                @forelse($definitions as $category => $items)
                    @php
                        // Search results open automatically so a matching operation
                        // is visible even when its category was previously collapsed.
                        $categoryCollapsed = trim($scheduleSearch) === '' && in_array($category, $collapsedScheduleCategories, true);
                    @endphp
                    <section class="ops-category-section" wire:key="schedule-category-{{ $category }}">
                        <button class="ops-category-toggle" type="button" wire:click="toggleScheduleCategory('{{ $category }}')" aria-expanded="{{ $categoryCollapsed ? 'false' : 'true' }}">
                            <span>
                                <strong>{{ $categoryLabels[$category] ?? str_replace('_',' ',$category) }}</strong>
                                <small>{{ count($items) }} operation{{ count($items) === 1 ? '' : 's' }}</small>
                            </span>
                            <i class="fa {{ $categoryCollapsed ? 'fa-chevron-down' : 'fa-chevron-up' }}"></i>
                        </button>
                        @unless($categoryCollapsed)
                            <div class="ops-schedule-grid">
                                @foreach($items as $definition)
                                    <div class="ops-schedule {{ !$definition['enabled'] ? 'ops-off' : '' }}">
                                        <div>
                                            <span class="ops-name">{{ $definition['name'] }}</span>
                                            @if($definition['archived'] ?? false)
                                                <span class="ops-status status-skipped ops-disabled-label">Archived</span>
                                            @elseif(!$definition['enabled'])
                                                <span class="ops-status status-skipped ops-disabled-label">Disabled</span>
                                            @endif
                                            @if($definition['description'])
                                                <span class="ops-schedule-description">{{ $definition['description'] }}</span>
                                            @endif
                                            <span class="ops-handler-info">
                                                <span class="ops-handler-badge ops-handler-{{ $definition['handler_type'] }}">{{ $definition['handler_type_label'] }}</span>
                                                <span class="ops-handler-code">{{ $definition['handler_label'] }}</span>
                                            </span>
                                        </div>
                                        <div><strong>{{ $definition['schedule_label'] }}</strong></div>
                                        <div class="ops-recipient">
                                            <strong>Recipients:</strong> {{ $definition['recipients'] }}
                                            <span class="ops-recipient-mode">{{ $definition['recipient_mode'] ?? 'legacy' }}</span>
                                        </div>
                                        <div>
                                            @if($definition['archived'] ?? false)
                                                <button class="ops-btn ops-btn-light" wire:click="restoreOperation({{ $definition['definition_id'] }})"><i class="fa fa-undo"></i> Restore</button>
                                            @else
                                                <button class="ops-btn ops-btn-light" wire:click="editSettings('{{ $definition['key'] }}')" title="Operation settings"><i class="fa fa-cog"></i></button>
                                                <button class="ops-btn ops-btn-primary" wire:click="requestRun('{{ $definition['key'] }}')">Run now</button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endunless
                    </section>
                @empty
                    <div class="ops-banner">No schedules match your search and archive filters.</div>
                @endforelse
            @endif
        </div>
    </div>

    <x-ui.modal :show="(bool) $selectedRun" title="Scheduled operation details" close-action="closeModals" max-width="850px" class="scheduled-ops-modal">
        @if($selectedRun)
            <h3 style="margin-top:0;color:#46515f">{{ $selectedRun->task_name }}</h3>
            <div class="ops-detail-grid">
                <div class="ops-detail ops-detail-status ops-detail-status-{{ $selectedRun->status }}"><span>Status</span><strong>{{ ucfirst($selectedRun->status) }}</strong></div>
                <div class="ops-detail"><span>Scheduled</span><strong>{{ optional($selectedRun->scheduled_for)->format('d/m/Y g:i a') }}</strong></div>
                <div class="ops-detail"><span>Attempt / duration</span><strong>{{ $selectedRun->attempt }} / {{ $selectedRun->duration_ms !== null ? number_format($selectedRun->duration_ms / 1000, 2).'s' : '—' }}</strong></div>
            </div>

            @if($selectedRun->exception_message)
                <div class="ops-error"><strong>{{ $selectedRun->exception_class }}</strong><br>{{ $selectedRun->exception_message }}<br><small>{{ $selectedRun->exception_file }}:{{ $selectedRun->exception_line }}</small></div>
            @endif

            <h4>Output</h4>
            <pre class="ops-output">{{ $selectedRun->output ?: 'No console output was produced.' }}</pre>

            <h4 style="margin-top:20px">Emails sent ({{ $selectedRun->messages->where('status','sent')->count() }})</h4>
            @forelse($selectedRun->messages as $message)
                <div class="ops-mail">
                    <strong>{{ $message->subject ?: '(No subject)' }}</strong>
                    <span class="ops-status status-{{ $message->status === 'sent' ? 'successful' : 'failed' }}" style="float:right">{{ $message->status }}</span>
                    <small>To: {{ $message->recipients->where('type','to')->pluck('email')->join(', ') ?: 'No recipients captured' }}</small>
                    <small>CC/BCC: {{ $message->recipients->whereIn('type',['cc','bcc'])->pluck('email')->join(', ') ?: 'None' }}</small>
                    @if($message->html_body || $message->text_body)
                        <a href="{{ route('scheduled-operations.message-preview', $message) }}" target="_blank" rel="noopener">Preview email</a>
                    @endif
                </div>
            @empty
                <p>No email was sent by this run.</p>
            @endforelse

            <x-slot name="footer">
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Close</button>
                @if(in_array($selectedRun->status, ['failed', 'missed'], true))
                    <button class="sws-modal-btn sws-modal-btn-primary" wire:click="requestRetry({{ $selectedRun->id }})">Retry</button>
                @elseif(in_array($selectedRun->status, ['successful', 'shadow'], true))
                    <button class="sws-modal-btn sws-modal-btn-primary" wire:click="requestRunAgain({{ $selectedRun->id }})">Run again</button>
                @endif
            </x-slot>
        @endif
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showRunConfirm" title="Run operation now?" close-action="closeModals" confirm-action="confirmRun" confirm-label="Add to queue" loading-target="confirmRun">
        This runs <span class="sws-confirm-item">{{ $pendingDefinition['name'] ?? '' }}</span> independently of its normal schedule. Any emails and data changes are real.
    </x-ui.confirm-modal>

    <x-ui.confirm-modal :show="$showRetryConfirm" title="Retry failed operation?" close-action="closeModals" confirm-action="confirmRetry" confirm-label="Retry operation" loading-target="confirmRetry">
        This creates a new auditable attempt for <span class="sws-confirm-item">{{ $pendingDefinition['name'] ?? '' }}</span>. The original failed run is preserved.
    </x-ui.confirm-modal>

    <x-ui.confirm-modal :show="$showArchiveConfirm" title="Archive operation?" close-action="closeModals" confirm-action="confirmArchive" confirm-label="Archive operation" loading-target="confirmArchive">
        Archive <span class="sws-confirm-item">{{ $pendingArchiveName }}</span>? It will be disabled, removed from normal scheduling, and unavailable for manual runs. Its settings, recipient rules and history will be preserved and it can be restored later.
    </x-ui.confirm-modal>

    <x-ui.modal :show="$showSettings" title="Operation settings" close-action="closeModals" max-width="980px" class="scheduled-ops-modal">
        @if($settingsDefinition)
            <div class="ops-form-grid">
                <div class="form-group">
                    <label class="control-label">Display name</label>
                    <input class="form-control" type="text" wire:model="settingName">
                    @error('settingName')<span class="help-block">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="control-label">Category</label>
                    <div class="ops-category-field">
                        <div class="ops-select-host" wire:key="setting-category-{{ $settingDefinitionId }}-{{ $categorySelectVersion }}-{{ $settingCategory }}" wire:ignore>
                            <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('settingCategory', $el.value)">
                                @foreach($categories as $category)
                                    @if($category->enabled || $category->slug === $settingCategory)
                                        <option value="{{ $category->slug }}" @selected($settingCategory === $category->slug)>{{ $category->name }}{{ !$category->enabled ? ' (disabled)' : '' }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button class="ops-btn ops-btn-light" type="button" wire:click="openCategoryManager" title="Manage categories"><i class="fa fa-cog"></i></button>
                    </div>
                    @error('settingCategory')<span class="help-block">{{ $message }}</span>@enderror
                </div>
                <div class="form-group ops-form-span-2">
                    <label class="control-label">Description</label>
                    <textarea class="form-control" style="height:74px" wire:model="settingDescription"></textarea>
                    @error('settingDescription')<span class="help-block">{{ $message }}</span>@enderror
                </div>
            </div>

            <label class="ops-status-toggle">
                <input type="checkbox" wire:model="settingEnabled" aria-label="Enable automatic runs">
                <span class="ops-status-track"><span class="ops-status-disabled">Disabled</span><span class="ops-status-enabled">Enabled</span></span>
            </label>

            <h4>Schedule <small>(Sydney time)</small></h4>
            <div class="ops-form-grid">
                <div class="form-group">
                    <label class="control-label">Frequency</label>
                    <div class="ops-select-host" wire:key="setting-frequency-{{ $settingDefinitionId }}-{{ $settingScheduleType }}" wire:ignore>
                        <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('settingScheduleType', $el.value)">
                            <option value="hourly" @selected($settingScheduleType === 'hourly')>Every hour</option>
                            <option value="daily" @selected($settingScheduleType === 'daily')>Daily</option>
                            <option value="weekdays" @selected($settingScheduleType === 'weekdays')>Every weekday</option>
                            <option value="weekly" @selected($settingScheduleType === 'weekly')>Selected weekdays</option>
                            <option value="fortnightly" @selected($settingScheduleType === 'fortnightly')>Fortnightly</option>
                            <option value="monthly_nth_weekday" @selected($settingScheduleType === 'monthly_nth_weekday')>Monthly — numbered weekday</option>
                            <option value="monthly_last_weekday" @selected($settingScheduleType === 'monthly_last_weekday')>Monthly — last weekday</option>
                            <option value="monthly_day" @selected($settingScheduleType === 'monthly_day')>Monthly — day of month</option>
                            <option value="quarterly" @selected($settingScheduleType === 'quarterly')>Selected months</option>
                        </select>
                    </div>
                    @error('settingScheduleType')<span class="help-block">{{ $message }}</span>@enderror
                </div>
                @if($settingScheduleType === 'weekly')
                    <div class="form-group">
                        <label class="control-label">Run on</label>
                        <div class="ops-day-toggle" role="group" aria-label="Run on weekdays">
                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri'] as $number => $day)
                                <label><input type="checkbox" value="{{ $number }}" wire:model="settingWeekdays"><span>{{ $day }}</span></label>
                            @endforeach
                        </div>
                        @error('settingWeekdays')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @endif

                @if(in_array($settingScheduleType, ['fortnightly','monthly_nth_weekday','monthly_last_weekday'], true))
                    <div class="form-group">
                        <label class="control-label">Weekday</label>
                        <div class="ops-select-host" wire:key="setting-weekday-{{ $settingDefinitionId }}-{{ $settingWeekday }}" wire:ignore>
                            <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('settingWeekday', Number($el.value))">
                                @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $number => $day)
                                    <option value="{{ $number }}" @selected((int) $settingWeekday === $number)>{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('settingWeekday')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @endif
                @if($settingScheduleType === 'fortnightly')
                    <div class="form-group">
                        <label class="control-label">Anchor date</label>
                        <input class="form-control" type="date" wire:model="settingAnchor">
                        <span class="ops-help">Choose one date that belongs to the intended fortnight.</span>
                        @error('settingAnchor')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @elseif($settingScheduleType === 'monthly_nth_weekday')
                    <div class="form-group">
                        <label class="control-label">Occurrence</label>
                        <div class="ops-select-host" wire:key="setting-occurrence-{{ $settingDefinitionId }}-{{ $settingOccurrence }}" wire:ignore>
                            <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('settingOccurrence', Number($el.value))">
                                <option value="1" @selected((int) $settingOccurrence === 1)>First</option>
                                <option value="2" @selected((int) $settingOccurrence === 2)>Second</option>
                                <option value="3" @selected((int) $settingOccurrence === 3)>Third</option>
                                <option value="4" @selected((int) $settingOccurrence === 4)>Fourth</option>
                                <option value="5" @selected((int) $settingOccurrence === 5)>Fifth</option>
                            </select>
                        </div>
                        @error('settingOccurrence')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @elseif(in_array($settingScheduleType, ['monthly_day','quarterly'], true))
                    <div class="form-group">
                        <label class="control-label">Day of month</label>
                        <input class="form-control" type="number" min="1" max="28" wire:model="settingDay">
                        <span class="ops-help">Limited to 1–28 so it exists every month.</span>
                        @error('settingDay')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @endif
                @if($settingScheduleType === 'quarterly')
                    <div class="form-group ops-form-span-2">
                        <label class="control-label">Run in these months</label>
                        <div class="ops-month-toggle" role="group" aria-label="Run in selected months">
                            @foreach([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'] as $number => $month)
                                <label><input type="checkbox" value="{{ $number }}" wire:model="settingMonths"><span>{{ $month }}</span></label>
                            @endforeach
                        </div>
                        @error('settingMonths')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @endif
            </div>

            <button class="ops-advanced-toggle" type="button" wire:click="$toggle('showAdvancedSettings')">
                <i class="fa {{ $showAdvancedSettings ? 'fa-chevron-up' : 'fa-chevron-down' }}"></i>
                {{ $showAdvancedSettings ? 'Hide advanced settings' : 'Advanced settings' }}
            </button>
            @if($showAdvancedSettings)
                <div class="ops-advanced">
                    <div class="ops-form-grid-3">
                        @if($settingScheduleType === 'hourly')
                            <div class="form-group">
                                <label class="control-label">Minute past the hour</label>
                                <input class="form-control" type="number" min="0" max="59" wire:model="settingMinute">
                                @error('settingMinute')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        @else
                            <div class="form-group">
                                <label class="control-label">Run time</label>
                                <input class="form-control" type="time" wire:model="settingTime">
                                @error('settingTime')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="control-label">Maximum attempts</label>
                            <input class="form-control" type="number" min="1" max="10" wire:model="settingTries">
                            @error('settingTries')<span class="help-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">Timeout (seconds)</label>
                            <input class="form-control" type="number" min="30" max="300" wire:model="settingTimeout">
                            <span class="ops-help">Maximum 300 seconds to match the current Forge worker.</span>
                            @error('settingTimeout')<span class="help-block">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="ops-archive-panel">
                        @if($settingCanBeClientConfigurable && $settingCategory === 'report')
                            <div class="ops-client-setting">
                                <label class="ops-status-toggle" title="Show this report under the client's Settings > Notifications page">
                                    <input type="checkbox" wire:model="settingClientConfigurable" aria-label="Show in client scheduled report settings">
                                    <span class="ops-status-track"><span class="ops-status-disabled">No</span><span class="ops-status-enabled">Yes</span></span>
                                </label>
                                <strong>Client report settings</strong>
                            </div>
                        @else
                            <div><strong>Archive operation</strong><span>Stops future scheduled and manual runs while preserving settings and history.</span></div>
                        @endif
                        <button class="ops-btn ops-btn-danger" type="button" wire:click="requestArchive"><i class="fa fa-archive"></i> Archive operation</button>
                    </div>
                    @error('settingClientConfigurable')<span class="help-block">{{ $message }}</span>@enderror
                </div>
            @endif

            <div class="ops-recipient-panel">
                <h4 style="margin-top:0">Email recipients</h4>
                <div class="ops-form-grid">
                    <div class="form-group">
                        <label class="control-label">Recipient control</label>
                        <div class="ops-select-host" wire:key="setting-recipient-mode-{{ $settingDefinitionId }}-{{ $settingRecipientMode }}" wire:ignore>
                            <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('settingRecipientMode', $el.value)">
                                <option value="legacy" @selected($settingRecipientMode === 'legacy')>Legacy — use addresses in existing code</option>
                                <option value="append" @selected($settingRecipientMode === 'append')>Append — keep code addresses and add rules below</option>
                                <option value="managed" @selected($settingRecipientMode === 'managed')>Managed — replace code addresses with rules below</option>
                            </select>
                        </div>
                        @error('settingRecipientMode')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="control-label">Summary shown in list</label>
                        <input class="form-control" type="text" wire:model="settingRecipientSummary" placeholder="e.g. Site supervisors and WHS group">
                        @error('settingRecipientSummary')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <p class="ops-help">
                    <strong>Legacy</strong> changes nothing. <strong>Append</strong> is safest while migrating. <strong>Managed</strong> makes this screen the complete To/CC/BCC source.
                    Managed mode keeps dynamic recipients explicitly declared by a converted handler, such as the relevant Supervisor or assigned company contact, while replacing old fixed addresses from report code.
                </p>

                @foreach($recipientRules as $index => $rule)
                    <div class="ops-rule" wire:key="recipient-rule-{{ $index }}">
                        <div class="ops-select-host" wire:key="recipient-delivery-{{ $settingDefinitionId }}-{{ $index }}-{{ $rule['delivery_type'] ?? '' }}" wire:ignore>
                            <select class="form-control bs-select ops-select" data-width="100%" aria-label="Delivery type" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.delivery_type', $el.value)">
                                <option value="to" @selected(($rule['delivery_type'] ?? '') === 'to')>To</option>
                                <option value="cc" @selected(($rule['delivery_type'] ?? '') === 'cc')>CC</option>
                                <option value="bcc" @selected(($rule['delivery_type'] ?? '') === 'bcc')>BCC</option>
                            </select>
                        </div>
                        <div class="ops-select-host" wire:key="recipient-source-{{ $settingDefinitionId }}-{{ $index }}-{{ $rule['source_type'] ?? '' }}" wire:ignore>
                            <select class="form-control bs-select ops-select" data-width="100%" aria-label="Recipient source" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.source_type', $el.value)">
                                <option value="user" @selected(($rule['source_type'] ?? '') === 'user')>User</option>
                                <option value="notification_group" @selected(($rule['source_type'] ?? '') === 'notification_group')>Notification group</option>
                                <option value="manual" @selected(($rule['source_type'] ?? '') === 'manual')>Email address</option>
                            </select>
                        </div>
                        @if(($rule['source_type'] ?? '') === 'user')
                            @php
                                $selectedUserIds = collect(is_array($rule['source_value'] ?? null) ? $rule['source_value'] : [])
                                    ->map(fn($id) => (string) $id);
                            @endphp
                            <div class="ops-select-host" wire:key="recipient-user-value-{{ $settingDefinitionId }}-{{ $index }}" wire:ignore>
                                <select class="form-control" multiple style="width:100%"
                                        x-init="const parent = $($el).closest('.sws-modal-card'); $($el).select2({width: '100%', placeholder: 'Select one or more users', dropdownParent: parent.length ? parent : $(document.body)}).on('change', function () { $wire.set('recipientRules.{{ $index }}.source_value', $(this).val() || []); })">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected($selectedUserIds->contains((string) $user->id))>{{ $user->fullname }} ({{ $user->company?->name_alias ?? 'Unknown company' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif(($rule['source_type'] ?? '') === 'notification_group')
                            <div class="ops-select-host" wire:key="recipient-group-value-{{ $settingDefinitionId }}-{{ $index }}-{{ $rule['source_value'] ?? '' }}" wire:ignore>
                                <select class="form-control bs-select ops-select" data-width="100%" data-live-search="true" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.source_value', $el.value)">
                                    <option value="">Select notification group</option>
                                    @foreach($notificationGroups as $group)
                                        <option value="{{ $group->id }}" @selected((string) ($rule['source_value'] ?? '') === (string) $group->id)>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input class="form-control" type="email" wire:model="recipientRules.{{ $index }}.source_value" placeholder="person@example.com">
                        @endif
                        <button class="ops-btn ops-btn-light ops-rule-remove" wire:click="removeRecipientRule({{ $index }})" title="Remove recipient"><i class="fa fa-trash"></i></button>
                    </div>
                    @error('recipientRules.'.$index.'.delivery_type')<span class="help-block">{{ $message }}</span>@enderror
                    @error('recipientRules.'.$index.'.source_type')<span class="help-block">{{ $message }}</span>@enderror
                    @error('recipientRules.'.$index.'.source_value')<span class="help-block">{{ $message }}</span>@enderror
                @endforeach
                @error('recipientRules')<span class="help-block">{{ $message }}</span>@enderror
                <button class="ops-btn ops-btn-light" style="margin-top:10px" wire:click="addRecipientRule"><i class="fa fa-plus"></i> Add recipient rule</button>
                <span class="ops-help" style="margin-left:8px">Select several users in one User rule. Add a separate Email address rule for each manually entered address.</span>
            </div>

            @if($changeLogs->isNotEmpty())
                {{--}}<div class="ops-activity">
                    <strong>Recent changes</strong>
                    @foreach($changeLogs as $change)
                        <div>{{ $change->created_at->format('d/m/Y g:i a') }} — {{ str_replace('_',' ',$change->action) }}{{ $change->user ? ' by '.$change->user->fullname : '' }}</div>
                    @endforeach
                </div>--}}
            @endif

            <x-slot name="footer">
                @if($hasLegacyDefault)
                    <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="resetSettings">Restore defaults</button>
                @endif
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
                <button class="sws-modal-btn sws-modal-btn-primary" wire:click="saveSettings" wire:loading.attr="disabled" wire:target="saveSettings">Save operation</button>
            </x-slot>
        @endif
    </x-ui.modal>

    <x-ui.modal :show="$showCategoryManager" title="Operation categories" close-action="closeCategoryManager" max-width="760px" class="scheduled-ops-modal">
        <p class="ops-help">Drag the handles to set the dashboard order. The eye controls whether a category is available; internal slugs stay fixed so existing handlers and run history remain compatible.</p>

        <div class="ops-form-grid" style="align-items:end;margin-bottom:14px">
            <div class="form-group" style="margin-bottom:0">
                <label class="control-label">New category</label>
                <input class="form-control" type="text" wire:model="newCategoryName" placeholder="e.g. Safety reports">
                @error('newCategoryName')<span class="help-block">{{ $message }}</span>@enderror
            </div>
            <div>
                <button class="ops-btn ops-btn-primary" type="button" wire:click="addCategory"><i class="fa fa-plus"></i> Add category</button>
            </div>
        </div>

        <div class="ops-category-sort" x-data="{ draggedRow: null }"
             x-on:dragstart="draggedRow = $event.target.closest('.ops-category-row'); if (!draggedRow) return; draggedRow.classList.add('is-dragging'); $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', draggedRow.dataset.categoryId)"
             x-on:dragend="draggedRow?.classList.remove('is-dragging'); $el.querySelectorAll('.is-drag-over').forEach((row) => row.classList.remove('is-drag-over')); draggedRow = null"
             x-on:dragover.prevent="if (!draggedRow) return; const target = $event.target.closest('.ops-category-row'); if (!target || target === draggedRow) return; $el.querySelectorAll('.is-drag-over').forEach((row) => row.classList.remove('is-drag-over')); target.classList.add('is-drag-over'); const after = $event.clientY > target.getBoundingClientRect().top + (target.offsetHeight / 2); $el.insertBefore(draggedRow, after ? target.nextSibling : target)"
             x-on:drop.prevent="if (!draggedRow) return; $wire.reorderCategories(Array.from($el.querySelectorAll('.ops-category-row')).map((row) => row.dataset.categoryId))">
            @foreach($categoryRows as $rowKey => $category)
                <div class="ops-category-row" data-category-id="{{ $category['id'] }}" wire:key="operation-category-{{ $category['id'] }}">
                    <button class="ops-drag-handle" type="button" draggable="true" title="Drag to reorder" aria-label="Drag {{ $category['name'] }} to reorder"><i class="fa fa-bars"></i></button>
                    <button class="ops-visibility {{ $category['enabled'] ? 'is-enabled' : 'is-disabled' }}" type="button" wire:click="toggleCategoryEnabled('{{ $rowKey }}')" title="{{ $category['enabled'] ? 'Disable' : 'Enable' }} {{ $category['name'] }}"
                            aria-pressed="{{ $category['enabled'] ? 'true' : 'false' }}">
                        <i class="fa {{ $category['enabled'] ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                        <span class="sr-only">{{ $category['enabled'] ? 'Enabled' : 'Disabled' }}</span>
                    </button>
                    <div>
                        <input class="form-control" type="text" wire:model="categoryRows.{{ $rowKey }}.name">
                        @error('categoryRows.'.$rowKey.'.name')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                    <span class="ops-slug">{{ $category['slug'] }}</span>
                    <small>{{ $categoryOperationCounts[$category['slug']] ?? 0 }} operation(s)</small>
                </div>
            @endforeach
        </div>

        <x-slot name="footer">
            <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeCategoryManager">Cancel</button>
            <button class="sws-modal-btn sws-modal-btn-primary" wire:click="saveCategories" wire:loading.attr="disabled" wire:target="saveCategories">Save categories</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.modal :show="$showAddOperation" title="Add scheduled operation" close-action="closeModals" max-width="760px" class="scheduled-ops-modal">
        <p>Code handlers found in <code>app/Scheduled/Operations</code> appear here. Installing one creates a disabled operation so its schedule and recipients can be reviewed safely.</p>
        @forelse($availableHandlers as $handler)
            <div class="ops-handler">
                <div>
                    <span class="ops-name">{{ $handler['name'] }}</span>
                    <span class="ops-key">{{ $handler['key'] }}</span>
                    <small>{{ $handler['description'] }}</small>
                </div>
                <button class="ops-btn ops-btn-primary" wire:click="installHandler('{{ $handler['handler_key'] }}')">Install</button>
            </div>
        @empty
            <div class="ops-banner">There are no unconfigured handlers. Add a class implementing <code>ScheduledOperationHandler</code>, deploy it, then run <code>php artisan scheduled:sync</code>.</div>
        @endforelse
        <x-slot name="footer">
            <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Close</button>
        </x-slot>
    </x-ui.modal>

</div>
