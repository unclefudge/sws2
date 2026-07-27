<div class="tab-pane {{ $tabs['0'] == 'profile' ? 'active' : '' }}" id="tab_profile">
    <div class="row">
        <div class="col-md-3 hidden-sm hidden-xs hide ">
            <ul class="list-unstyled profile-nav">
                <li>
                    <img src="/img/user_photo.jpg" class="img-responsive pic-bordered" alt=""/>
                </li>
                <!--
                <li class="font-green-haze">
                    <a href="javascript:;"> Tasks
                        <span> 3 </span></a>
                </li> -->
            </ul>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-9 profile-info">
                    <h1 class="font-green sbold uppercase">
                        {{ $client->name }}
                    </h1>

                    @if($client->address)
                        {{ $client->address }}<br>
                    @endif

                    @if($client->suburb)
                        {{  $client->suburb . ', ' }}
                    @endif

                    @if ($client->state || $client->postcode)
                        {{  $client->state . ' ' .  $client->postcode }}
                    @endif


                    <p>
                    <ul class="list-inline">
                        @if ($client->phone)
                            <li><i class="fa fa-phone"></i> <a href="tel:{{ preg_replace("/[^0-9]/", "", $client->phone) }}"> {{ $client->phone }} </a>
                            </li>
                        @endif

                        @if ($client->email)
                            <li><i class="fa fa-envelope-o"></i> <a href="mailto:{{ $client->email }}"> {{ $client->email }} </a></li>
                        @endif
                    </ul>
                    </p>

                    <p>{{ $client->notes }}</p>
                    <input type="hidden" name="company_id" id="company_id" value="{{ $client->company_id }}">
                </div>
                <!--end col-md-8-->
            </div>
            <!--end row-->
            <div class="tabbable-line tabbable-custom-profile">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#tab_profile_staff" data-toggle="tab"> Sites </a>
                    </li>
                    <!--
                    <li>
                        <a href="#tab_1_22" data-toggle="tab"> Tasks </a>
                    </li>
                    -->
                </ul>
                <div class="tab-content">

                    <!-- Staff tab -->
                    <div class="tab-pane active" id="tab_profile_staff">
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table_staff555">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:5%"></th>
                                    <th style="width:5%"> No. </th>
                                    <th> Name </th>
                                    <th> Address </th>
                                    <th> Suburb </th>
                                    <th style="width:10%"> Status </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($client->sites->sortBy('code') as $site)
                                    <tr>
                                        <td><div class="text-center"><a href="/site/{{ $site->slug }}"><i class="fa fa-search"></i></a></div></td>
                                        <td>{{ $site->code }}</td>
                                        <td><a href="/site/{{ $site->slug }}">{{ $site->name }}</a></td>
                                        <td>{{ $site->address }}</td>
                                        <td>{{ $site->suburb }}</td>
                                        <td>{!! $site->statusText('colour') !!}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Tasks tab -->
                    <div class="tab-pane" id="tab_1_22">
                        <div class="tab-pane active" id="tab_1_1_1">
                            <div class="scroller" data-height="290px" data-always-visible="1" data-rail-visible1="1">
                                <ul class="feeds">
                                    <li>
                                        <div class="col1">
                                            <div class="cont">
                                                <div class="cont-col1">
                                                    <div class="label label-success">
                                                        <i class="fa fa-bell-o"></i>
                                                    </div>
                                                </div>
                                                <div class="cont-col2">
                                                    <div class="desc"> You have 4 pending tasks.
                                                        <span class="label label-danger label-sm"> Take action
                                                            <i class="fa fa-share"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col2">
                                            <div class="date"> Just now</div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--tab-pane-->
                </div>
            </div>
        </div>
    </div>
</div>