@php
    $font = "font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 12pt; line-height: 1.35; color: #000000;";
    $labelStyle = $font . " font-weight: bold; padding: 4px 0; vertical-align: top;";
    $valueStyle = $font . " padding: 4px 0; vertical-align: top;";
@endphp

        <!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Cape Cod enquiry has been received</title>
</head>

<body style="margin:0; padding:0; background:#ffffff; {{ $font }}">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; {{ $font }}">
    <tr>
        <td align="left" style="padding:35px 25px; {{ $font }}">

            <table width="650" cellpadding="0" cellspacing="0" border="0" style="max-width:650px; {{ $font }}">

                <tr>
                    <td style="padding-bottom:16px; {{ $font }}">
                        One of our friendly client service team members will contact you in the next 1-2 business days to
                        understand your renovation goals and connect you with our expert design team.
                    </td>
                </tr>

                <tr>
                    <td style="font-weight:bold; padding-bottom:4px; {{ $font }}">
                        Summary of web form submission:
                    </td>
                </tr>

                <tr>
                    <td style="{{ $font }}">
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="{{ $font }}">

                            <tr>
                                <td width="280" style="{{ $labelStyle }}">Enquiry Name</td>
                                <td style="{{ $valueStyle }}">{{ $summary['name'] ?? '' }}</td>
                            </tr>

                            <tr>
                                <td style="{{ $labelStyle }}">Mobile</td>
                                <td style="{{ $valueStyle }}">{{ $summary['mobile'] ?? '' }}</td>
                            </tr>

                            <tr>
                                <td style="{{ $labelStyle }}">Email Address</td>
                                <td style="{{ $valueStyle }}">
                                    <a href="mailto:{{ $summary['email'] ?? '' }}" style="color:#0066cc; {{ $font }}">
                                        {{ $summary['email'] ?? '' }}
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td style="{{ $labelStyle }}">Address</td>
                                <td style="{{ $valueStyle }}">
                                    {{ $summary['street_address'] ?? '' }}<br>
                                    {{ $summary['suburb'] ?? '' }} {{ $summary['postcode'] ?? '' }}
                                </td>
                            </tr>

                            <tr>
                                <td style="{{ $labelStyle }} padding-top:12px;">Renovations</td>
                                <td style="{{ $valueStyle }} padding-top:12px;">
                                    {{ $summary['renovations'] ?? '' }}
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>

                <tr>
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
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>