@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.qa'))
            <li><a href="/site/qa">Quality Assurance</a><i class="fa fa-circle"></i></li>
            <li><a href="/site/qa/templates">Templates</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Report Order</span></li>
    </ul>
@stop

@section('content')
    <style>
        td:hover {
            cursor: move;
        }
    </style>
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">QA Report Order</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        <div class="form-body">
                            {{--}}
                            <div class="row">
                                <div class="col-xs-2 text-center"></div>
                                <div class="col-xs-4"></div>
                                <div class="col-xs-2"></div>
                            </div>
                            @foreach ($templates as $template)
                                <div class="row">
                                    <div class="col-xs-1">
                                        <a href="/site/qa/templates/order/up/{{ $template->id }}" style="margin-left: 10px"><i class="fa fa-chevron-up"></i></a><br>
                                        <a href="/site/qa/templates/order/down/{{ $template->id }}" style="margin-left: 10px"><i class="fa fa-chevron-down"></i></a>
                                    </div>
                                    <div class="col-xs-1">
                                        <span style="margin-top: 5px"> {{ $template->order }}. &nbsp; </span>
                                    </div>
                                    <div class="col-xs-10">{!! $template->name !!}</div>
                                </div>
                                @if (!$loop->last)
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px;">
                                @endif
                            @endforeach
                            --}}

                            {{--}}<h3>Sortable</h3>
                            <table class="table table-striped">
                                <tr>
                                    <th>Name</th>
                                </tr>
                                <tbody class="sortable">
                                @foreach ($templates as $template)
                                    <tr id="{{$template->id}}">
                                        <td>{!! $template->name !!}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>--}}
                            <table class="table table-hover" id="myTable">
                                <thead>
                                <tr>
                                    <th style="width: 5px" class="text-center"></th>
                                    <th style="width: 50px" class="text-center">#</th>
                                    <th>Name</th>
                                    {{--}}<th>ID</th>--}}
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($templates as $template)
                                    <tr data-index="{{$template->id}}" data-order="{{$template->order}}" data-position="{{$template->order}}">
                                        <td><i class="fa fa-bars"></i></td>
                                        <td id="{{$template->id}}-order" style="text-align: center">{{$template->order}}</td>
                                        <td>{!! $template->name !!}</td>
                                        {{--}}<td>{{$template->id}}</td>--}}
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="form-actions right">
                            <a href="/site/qa/templates" class="btn default"> Back</a>
                            <button type="submit" class="btn green"> Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            var fixHelperModified = function (e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function (index) {
                        $(this).width($originals.eq(index).width())
                    });
                    return $helper;
                },
                updateIndex = function (e, ui) {
                    //console.log(ui);
                    $(this).children().each(function (index) {
                        var row = index + 1;
                        if ($(this).attr('data-position') != row) {
                            $(this).attr('data-position', row); // set new position
                            $('#' + $(this).attr('data-index') + '-order').html(row); // change order # on page
                            $(this).addClass('updatedrow'); // add class to know row needs to be updated in DB
                        }
                    });
                    saveNewPositions();
                };

            function saveNewPositions() {
                // Create array of arrays [index => newposition] for all rows that had updated positions
                var newPositions = [];
                $('.updatedrow').each(function () {
                    //
                    newPositions.push([$(this).attr('data-index'), $(this).attr('data-position')]);
                    $(this).removeClass('updatedrow');
                });
                //console.log(positions);

                $.ajax({
                    url: '/site/qa/templates/order/update',
                    type: 'POST',
                    dataType: 'json',
                    data: {order: newPositions},
                    success: function (data) {
                        console.log('updated order');
                    },
                });
            }

            $("#myTable tbody").sortable({
                helper: fixHelperModified,
                stop: updateIndex
            }).disableSelection();

            $("tbody").sortable({
                distance: 5,
                delay: 100,
                opacity: 0.6,
                cursor: 'move',
                update: function () {
                }
            });


        });
    </script>
@stop

