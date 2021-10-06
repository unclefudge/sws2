@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Site Planner Key Task</h1>
            <p>These key tasks are happening on the following sites today:</p>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #f0f6fa; font-weight: bold;">
                    <td width="60" style="border: 1px solid">Site</td>
                    <td width="200" style="border: 1px solid">Name</td>
                    <td width="200" style="border: 1px solid">Task</td>
                    <td width="250" style="border: 1px solid">Company</td>
                </tr>
                @foreach($tasks as $task)
                <tr>
                    <td style="border: 1px solid">{{ $task->site->code }}</td>
                    <td style="border: 1px solid">{{ $task->site->name }}</td>
                    <td style="border: 1px solid">{{ $task->task->name }}</td>
                    <td style="border: 1px solid">{{ $task->company->name }}</td>
                </tr>
                @endforeach
            </table>
            <br>
            <hr>
            <p>This email has been generated on behalf of Cape Cod</p>

            <p>Regards,<br/>SafeWorksite</p>
        </td>
    </tr>
</table>

@include('emails/_email-end')
