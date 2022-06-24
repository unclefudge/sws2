@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Equipment Restock</h1>
            <p>The following items are low in stock and require reordering.</p>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <td width="250" style="border: 1px solid">Item Name</td>
                    <td width="80" style="border: 1px solid">Available</td>
                    <td width="80" style="border: 1px solid">Required Min</td>
                    <td width="100" style="border: 1px solid">Last Ordered</td>
                </tr>
                @foreach($data as $equipment)
                    @if ($equipment->total < $equipment->min_stock)
                        <tr>
                            <td style="border: 1px solid">{{ $equipment->name }}</td>
                            <td style="border: 1px solid">{{ $equipment->total }}</td>
                            <td style="border: 1px solid">{{ $equipment->min_stock }}</td>
                            <td style="border: 1px solid">{{ ($equipment->purchased_last) ? $equipment->purchased_last->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @endif
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