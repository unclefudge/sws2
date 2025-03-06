@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Missing Company Information</h1>

            <table style="border: 1px solid; border-collapse: collapse">
                @foreach($missing_info as $data)
                    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                        <td colspan="2">{!! $data['company_name'] !!} &nbsp; &nbsp; {!! $data['company_nickname'] !!}</td>
                        <td>{{ $data['next_planner'] }}</td>
                    </tr>
                    {{-- Missing Info --}}
                    @if ($data['missing_info'] != '')
                        <tr>
                            <td colspan="3" style="border: 1px solid">{!! $data['missing_info'] !!}</td>
                        </tr>
                    @endif
                    {{-- Missing Docs --}}
                    @foreach ($data['docs'] as $doc)
                        <tr>
                            <td style="border: 1px solid; width: 80px">{!! $doc['expiry_human'] !!}</td>
                            <td style="border: 1px solid">{!! $doc['name'] !!}</td>
                            <td style="border: 1px solid;">{!! $doc['expiry_date'] !!}</td>
                        </tr>
                    @endforeach
                @endforeach
            </table>
            <a href="config('app.url').'/manage/report/missing_company_info_planner'" class="btn"></a>
            <hr>
            <p>Regards,<br/>SafeWorksite</p>
        </td>
    </tr>
</table>

@include('emails/_email-end')
