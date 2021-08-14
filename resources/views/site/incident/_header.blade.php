{{-- Incident Header --}}
<div class="row">
    <div class="col-md-12">
        <div class="member-bar">
            <!--<i class="fa fa-user ppicon-user-member-bar" style="font-size: 80px; opacity: .5; padding:5px"></i>-->
            <i class="icon-medkit-member-bar hidden-xs"></i>
            <div class="member-name">
                <div class="full-name-wrap">{{ $incident->site_name }}</div>
                <span class="member-number">Incident ID #{{ $incident->id }}</span>
                <span class="member-split">&nbsp;|&nbsp;</span>
                <span class="member-number">
                    @if ($incident->status == 1) OPEN @endif
                    @if ($incident->status == 0) <span class="label label-sm label-danger">RESOLVED</span> @endif
                    @if ($incident->status == 2) <span class="label label-sm label-warning">IN PROGRESS</span> @endif
                </span>
            </div>

            <?php
            $active_profile = $active_analysis = $active_admin = '';
            list($first, $site_url, $rest) = explode('/', Request::path(), 3);
            if (!ctype_digit($rest)) {
                list($uid, $rest) = explode('/', $rest, 2);
                $active_analysis = (preg_match('/^analysis*/', $rest)) ? 'active' : '';
                $active_admin = (preg_match('/^admin*/', $rest)) ? 'active' : '';
            } else
                $active_profile = 'active';
            ?>

            <ul class="member-bar-menu">
                <li class="member-bar-item {{ $active_profile }}"><i class="icon-profile"></i><a class="member-bar-link" href="/site/incident/{{ $incident->id }}" title="Profile">PROFILE</a></li>

                @if (Auth::user()->allowed2('del.site.incident', $incident))
                    <li class="member-bar-item {{ $active_admin }}"><i class="icon-lock"></i><a class="member-bar-link" href="/site/incident/{{ $incident->id }}/admin" title="Admin">ADMIN</a></li>
                @endif

                <li class="member-bar-item {{ $active_analysis }}"><i class="icon-chartarrow"></i><a class="member-bar-link" href="/site/incident/{{ $incident->id }}/analysis" title="Investigate">ANALYSIS</a></li>
            </ul>
        </div>
    </div>
</div>
