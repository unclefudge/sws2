<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Cape Cod enquiry has been received</title>
</head>
<body style="margin:0; padding:0; background:#ffffff; font-family:Arial, Helvetica, sans-serif; color:#000000; font-size:14px; line-height:1.45;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;">
    <tr>
        <td align="left" style="padding:35px 25px;">

            <table width="650" cellpadding="0" cellspacing="0" border="0" style="max-width:650px;">
                <tr>
                    <td style="font-size:14px; color:#000000; padding-bottom:16px;">
                        One of our friendly client service team members will contact you in the next 1-2 business days to
                        understand your renovation goals and connect you with our expert design team.
                    </td>
                </tr>

                <tr>
                    <td style="font-size:14px; font-weight:bold; padding-bottom:4px;">
                        Summary of web form submission:
                    </td>
                </tr>

                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:14px;">
                            <tr>
                                <td width="280" style="font-weight:bold; padding:4px 0;">Enquiry Name</td>
                                <td style="padding:4px 0;">{{ $summary['name'] ?? '' }}</td>
                            </tr>

                            <tr>
                                <td style="font-weight:bold; padding:4px 0;">Mobile</td>
                                <td style="padding:4px 0;">{{ $summary['mobile'] ?? '' }}</td>
                            </tr>

                            <tr>
                                <td style="font-weight:bold; padding:4px 0;">Email Address</td>
                                <td style="padding:4px 0;">
                                    <a href="mailto:{{ $summary['email'] ?? '' }}" style="color:#0066cc;">
                                        {{ $summary['email'] ?? '' }}
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td style="font-weight:bold; padding:4px 0; vertical-align:top;">Address</td>
                                <td style="padding:4px 0;">
                                    {{ $summary['street_address'] ?? '' }}<br>
                                    {{ $summary['suburb'] ?? '' }} {{ $summary['postcode'] ?? '' }}
                                </td>
                            </tr>

                            <tr>
                                <td style="font-weight:bold; padding:12px 0 4px; vertical-align:top;">Renovations</td>
                                <td style="padding:12px 0 4px;">
                                    {{ $summary['renovations'] ?? '' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding-top:65px; font-size:14px;">
                        Kind regards,<br>
                        <strong>THE CLIENT SERVICES TEAM</strong>
                    </td>
                </tr>

                <tr>
                    <td style="padding-top:45px;">
                        <img src="https://www.capecod.com.au/wp-content/uploads/2024/07/My-Life.-My-Home.-Logo-e1741744241578.png"
                             alt="Cape Cod Australia"
                             width="180"
                             style="display:block; width:180px; max-width:180px; height:auto;">
                    </td>
                </tr>

                <tr>
                    <td style="padding-top:45px; font-size:12px; color:#000000; line-height:1.6;">
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