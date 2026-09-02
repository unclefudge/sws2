@if (Auth::user()->isCC())
    <h3 class="font-green form-section clearfix">
        Report Email Lists
        @if (Auth::user()->hasRole2('web-admin'))
            <button type="button" class="btn btn-circle green btn-outline btn-sm pull-right" data-toggle="modal" data-target="#add-report-notification">
                <i class="fa fa-plus"></i> Add Report Email List
            </button>
        @endif
    </h3>
    @if (Auth::user()->hasRole2('web-admin'))
        <p class="help-block">
            <i class="fa fa-lock font-grey-silver"></i> Locked lists are referenced by SafeWorkSite code and can be disabled, but not deleted.
        </p>
    @endif

    @forelse ($reportCategories as $category)
        {!! $category->notificationSelect(Auth::user()->hasRole2('web-admin'), !$loop->first, !$loop->last) !!}
    @empty
        <div class="note note-info">No report email lists have been configured.</div>
    @endforelse
@endif
