<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Outstanding FOC Defective Inspections</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #333; font-size: 14px">
<p>Hi {{ $supervisorFirstName }},</p>

<p>The following jobs currently have outstanding <strong>Defective</strong> FOC items.</p>

<table cellpadding="8" cellspacing="0" border="0" width="100%" style="border-collapse: collapse; border: 1px solid #ddd">
    <thead>
    <tr style="background: #f3f3f3">
        <th align="left" style="border: 1px solid #ddd">Job</th>
        <th align="left" style="border: 1px solid #ddd">Site</th>
        <th align="left" style="border: 1px solid #ddd">Outstanding Defects</th>
        <th align="left" style="border: 1px solid #ddd">FOC</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($jobs as $job)
        <tr>
            <td valign="top" style="border: 1px solid #ddd">{{ $job['site_code'] }}</td>
            <td valign="top" style="border: 1px solid #ddd">{{ $job['site_name'] }}</td>
            <td valign="top" style="border: 1px solid #ddd">
                @foreach ($job['defects'] as $defect)
                    <div style="margin-bottom: 6px">
                        {{ $defect['name'] }}
                        <span style="color: #888">({{ $defect['updated_at'] }})</span>
                    </div>
                @endforeach
            </td>
            <td valign="top" style="border: 1px solid #ddd">
                <a href="{{ url('/site/foc/' . $job['foc_id']) }}">View FOC</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<p style="margin-top: 18px">Items will automatically drop off this report once they are marked complete in Safe Worksite.</p>

<p>Safe Worksite</p>
</body>
</html>
