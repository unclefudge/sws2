{{-- Show Review --}}
<div class="portlet light" id="show_review">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Review</span>
        </div>
        <div class="actions">
            @if ($pEdit && $incident->status)
                <button class="btn btn-circle green btn-outline btn-sm" onclick="addForm('review')">Add</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        @if ($incident->reviews()->count())
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered table-hover order-column" id="table_prevent">
                        <thead>
                        <tr class="mytable-header">
                            <th width="5%"> #</th>
                            <th> Role</th>
                            <th> By Whom</th>
                            <th> Comments</th>
                            <th> Date Signed</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($incident->reviews() as $review)
                            <?php
                            list($crap, $review_role) = explode(' : ', $review->name);
                            $done_at = ($review->done_at) ? $review->done_at->format('d/m/Y') : '';
                            if (!$done_at && $review->openedBy()->count())
                                $done_at = "<span class='font-red'>Seen by user</span>";

                            ?>
                            <tr>
                                <td>
                                    <div class="text-center"><a href="/todo/{{ $review->id  }}"><i class="fa fa-search"></i></a></div>
                                </td>
                                <td>{{ $review_role }}</td>
                                <td>{!! ($review->assignedToBySBC()) ? $review->assignedToBySBC() : "<a href='/todo/".$review->id."/edit/' class='font-red'>Unassigned</span>" !!}</td>
                                <td>{!! ($review->comments) ? $review->comments : '' !!}</td>
                                <td>{!! $done_at !!}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-md-12">No reviews</div>
            </div>
        @endif
    </div>
</div>