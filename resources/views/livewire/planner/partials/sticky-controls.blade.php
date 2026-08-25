@once
    <style>
        /* Keep the planner filters and navigation available while the schedule scrolls. */
        .planner-sticky-controls {
            position:sticky;
            top:50px;
            z-index:1000;
            padding-top:10px;
            padding-bottom:10px !important;
            background:#fff;
            border-bottom:1px solid #eef1f5;
            box-shadow:0 5px 10px rgba(36,50,66,.08);
        }

        /* The fixed Metronic menu is desktop-only; mobile controls can sit at the viewport top. */
        @media (max-width:991px) {
            .planner-sticky-controls { top:0; }
        }
    </style>
@endonce
