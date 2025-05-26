@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Company Documents Pending</h1>
            <table style="width: 100%; border: 1px solid; border-collapse: collapse">
                <thead>
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <th style="border: 1px solid"> Company</th>
                    <th style="width: 250px; border: 1px solid"> Document</th>
                    <th style="width: 100px; border: 1px solid"> Updated</th>
                </tr>
                </thead>
                <tbody>
                @foreach($pending_info as $doc)
                        <?php
                        $todos = App\Models\Comms\Todo::where('type', 'company doc')->where('type_id', $doc->id)->get();
                        $task = "";
                        if ($todos) {
                            foreach ($todos as $todo) {
                                $task .= ($todo->status) ? "<br>ToDo : " . $todo->assignedToBySBC() : "<br>ToDo: Closed by " . $todo->doneBY->name . "(" . $todo->done_at->format('d/m/Y') . ")"; // . " :" . $todo->id;
                            }
                        }
                        ?>
                    <tr style="border: 1px solid">
                        <td style="border: 1px solid">{{ $doc->company->name}} {!! $task !!}</td>
                        <td style="border: 1px solid">{{ $doc->name}}</td>
                        <td style="border: 1px solid">{{ $doc->updated_at->format('d/m/Y')}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <a href="config('app.url').'/manage/report/pending_company_docs" class="btn"></a>
            <hr>
            <p>Regards,<br/>SafeWorksite</p>
        </td>
    </tr>
</table>

@include('emails/_email-end')
