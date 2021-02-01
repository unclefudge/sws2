<div class="tab-pane {{ $tabs['0'] == 'profile' ? 'active' : '' }}" id="tab_profile">
    <div class="row">
        <div class="col-md-3 hidden-sm hidden-xs">
            <ul class="list-unstyled profile-nav">
                <li>
                    @if($site->photo && file_exists(public_path($site->photo)))
                        <img src="/{{ $site->photo }}" class="img-responsive pic-bordered" alt=""/>
                    @else
                        <img src="/img/site_photo.jpg" class="img-responsive pic-bordered" alt=""/>
                    @endif
                    @if (Auth::user()->allowed2('edit.site', $site))
                        <a href="/site/{{ $site->slug }}/settings/photo" class="profile-edit"> edit </a>
                    @endif
                </li>
                <li class="font-green-haze">
                    @if ($site->hasAccidentsOpen())
                        <a href="/site/accident"> Accidents ({{ $site->hasAccidentsOpen() }} unresolved)<span class="bg-red"> {{ $site->accidents->count() }}</span></a>
                    @else
                        <a>Accidents <span class="bg-blue"> {{ $site->accidents->count() }}</span></a>
                    @endif
                </li>
                <li class="font-green-haze">
                    @if ($site->hasHazardsOpen())
                        <a href="/site/hazard"> Hazards ({{ $site->hasHazardsOpen() }} unresolved)<span class="bg-red"> {{ $site->hazards->count() }} </span></a>
                    @else
                        <a>Hazards <span class="bg-blue"> {{ $site->hazards->count() }} </span></a>
                    @endif
                </li>
            </ul>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-9 profile-info">
                    <h1 class="font-green sbold uppercase">{{ $site->name }} <br>
                        <small class="font-grey-silver" style="text-transform: none; margin-top: -50px">Site: {{ $site->code }}</small>
                    </h1>
                    {{ $site->address }}<br>
                    {{ $site->suburb . ', ' }}
                    {{ $site->state . ' ' .  $site->postcode }}
                    @if (Auth::user()->isCC() ||  Auth::user()->company_id == '96')
                        <p>
                        <ul class="list-inline">
                            @if ($site->client_phone)
                                <li><i class="fa fa-phone"></i> <a href="tel:{{ preg_replace("/[^0-9]/", "", $site->client_phone) }}"> {{ $site->client_phone }}</a> {{ $site->client_phone_desc }}</li>
                            @endif
                            @if ($site->client_phone2)
                                <li><i class="fa fa-phone"></i> <a href="tel:{{ preg_replace("/[^0-9]/", "", $site->client_phone2) }}"> {{ $site->client_phone2 }}</a> {{ $site->client_phone2_desc }}
                                </li>
                            @endif

                            @if ($site->email)
                                <li><i class="fa fa-envelope-o"></i> <a href="mailto:{{ $site->email }}"> {{ $site->email }} </a></li>
                            @endif
                        </ul>
                        </p>
                    @endif

                    @if(Auth::user()->company_id == $site->company_id)
                        <p>{{ $site->notes }}</p>
                    @endif

                    @if(!$site->status)
                        <h3 class="font-red uppercase" style="margin:0 0 10px;">Completed {{ ($site->completed) ? $site->completed->format('d/m/Y')  : 'N/A'}}</h3>
                    @elseif($site->status < 0)
                        <h3 class="font-blue uppercase" style="margin:0 0 10px;">Upcoming</h3>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="portlet sale-summary">
                        <div class="portlet-title">
                            <div class="caption font-red sbold"> Supervisor</div>
                        </div>
                        <div class="portlet-body">
                            <ul class="list-unstyled">
                                @foreach($site->supervisors as $user)
                                    <li><span class="sale-info"> {{ $user->firstname }} {{ $user->lastname }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
            <!--end row-->
            <div class="tabbable-line tabbable-custom-profile">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#tab_attendance" data-toggle="tab"> Attendance </a>
                    </li>
                    @if (Auth::user()->company_id == $site->company_id && Auth::user()->allowed2('view.company.acc', $site->company))
                        <li>
                            <a href="#tab_admin" data-toggle="tab"> Admin </a>
                        </li>
                    @endif
                </ul>
                <div class="tab-content">

                    <!-- Staff tab -->
                    <div class="tab-pane active" id="tab_attendance">
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table_staff555">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"> Date</th>
                                    <th> Name</th>
                                    <th> Company</th>
                                    <th width="5%"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($site->attendance as $attend)
                                    @if ($attend->user->id == Auth::user()->id || ($attend->user->company_id == Auth::user()->company_id  && Auth::user()->hasPermission2('edit.user.security')))
                                        <tr>
                                            <td>{{ $attend->date->format('d/m/y') }}</td>
                                            <td>{{ App\User::find($attend->user_id)->fullname }}</td>
                                            <td>{{ App\User::find($attend->user_id)->company->name_alias }}</td>
                                            <td></td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Admin tab -->
                    <div class="tab-pane" id="tab_admin">
                        <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-xs-6">Contract Sent</div>
                                    <div class="col-xs-6">{!! ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : "&nbsp;" !!}</div>
                                    <div class="col-xs-6"> Contract Signed</div>
                                    <div class="col-xs-6">{!! ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : "&nbsp;" !!}</div>
                                    <div class="col-xs-6"> Deposit Paid</div>
                                    <div class="col-xs-6">{!! ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : "&nbsp;" !!}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-xs-9">Engineering Certificate</div>
                                    <div class="col-xs-3">{!! ($site->engineering) ? 'Yes' : 'No' !!}</div>
                                    <div class="col-xs-9">Construction Certificate</div>
                                    <div class="col-xs-3">{!! ($site->construction) ? 'Yes' : 'No' !!}</div>
                                    <div class="col-xs-9">Home Builder Compensation Fund</div>
                                    <div class="col-xs-3">{!! ($site->hbcf) ? 'Yes' : 'No' !!}</div>
                                </div>
                            </div>
                            <br><br>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-xs-12">Consultant: {{ $site->consultant_name }}</div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--tab-pane-->
                </div>
            </div>
        </div>
    </div>
</div>