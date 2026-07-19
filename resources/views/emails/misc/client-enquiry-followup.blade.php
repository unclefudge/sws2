@php
    $font = "font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 12pt; line-height: 1.35; color: #000000;";
    $labelStyle = $font . " font-weight: bold; padding: 4px 0; vertical-align: top;";
    $valueStyle = $font . " padding: 4px 0; vertical-align: top;";
@endphp

        <!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>**Part 1 only** Your Home Addition Enquiry</title>
</head>

<body style="margin:0; padding:0; background:#ffffff; {{ $font }}">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; {{ $font }}">
    <tr>
        <td align="left" style="padding:35px 25px; {{ $font }}">

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:650px; {{ $font }}">

                <tr>
                    <td style="padding-bottom:16px; {{ $font }}">
                        <p style="color:#FF0000">**Change the From field to inform@<br>
                            **Enter the Clients Email address in the To field: {{ $submission->email }}<br>
                            **Add your signature, enter the Clients Name if you have it and remove prompt text then Send<br>
                        </p>
                        <p>Hi{{ ($submission->full_name) ? " $submission->full_name" : '' }},</p>

                        <p>I hope you are well.</p>

                        <p>I noticed that you’ve started an enquiry on our website but haven’t had a chance to complete all the steps yet. I just wanted to check in and see how you’d like to proceed.</p>

                        <p>You’re welcome to return and finish your enquiry at any time, it should only take a few more minutes to complete.</p>

                        <p>Alternatively, if it’s easier, I can arrange for one of our team members to give you a call and take down the remaining details for you. Just let me know what works best.</p>

                        <p>If you have any questions or need assistance, feel free to reply to this email, I’m happy to help.</p>

                        <p>Looking forward to hearing from you.</p>
                    </td>
                </tr>
                {{--}}<tr>
                    <td style="padding-top:65px; {{ $font }}">
                        Kind regards,<br>
                        <strong>THE CLIENT SERVICES TEAM</strong>
                    </td>
                </tr>

                <tr>
                    <td style="padding-top:45px; {{ $font }}">
                        <img src="https://www.capecod.com.au/wp-content/uploads/2024/07/My-Life.-My-Home.-Logo-e1741744241578.png"
                             alt="Cape Cod Australia"
                             width="180"
                             style="display:block; width:180px; max-width:180px; height:auto; border:0;">
                    </td>
                </tr>

                <tr>
                    <td style="padding-top:45px; font-size:12pt; {{ $font }}">
                        Cape Cod Australia Pty Ltd ABN 54 000 605 407<br>
                        4/426 Church Street NORTH PARRAMATTA NSW 2151<br>
                        PO Box 2002 NORTH PARRAMATTA NSW 1750
                    </td>
                </tr>--}}

            </table>

        </td>
    </tr>
</table>

</body>
</html>