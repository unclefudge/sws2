<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 15px;
            margin-top: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            text-align: left;
            vertical-align: top;
            padding: 6px;
            border: 1px solid #ddd;
        }

        .pre {
            white-space: pre-wrap;
        }

        .muted {
            color: #666;
        }
    </style>
</head>
<body>

<h1>Site Variation</h1>

<p class="muted">
    Job: {{ $site->name ?? '' }}<br>
    Site Note ID: {{ $note->id }}
</p>

<table>
    <tr>
        <th>Name</th>
        <td>{{ $note->variation_name }}</td>
    </tr>
    <tr>
        <th>Category</th>
        <td>{{ $note->category->name }}</td>
    </tr>
</table>

<h2>Description</h2>
<div class="pre">{{ $note->variation_info }}</div>

<table>
    <tr>
        <th>Net</th>
        <th>Gross</th>
        <th>Extra / Credit</th>
    </tr>
    <tr>
        <td>{{ $note->variation_net }}</td>
        <td>{{ $note->variation_cost }}</td>
        <td>{{ $note->costing_extra_credit }}</td>
    </tr>
    <tr>
        <td colspan="3"><b>Total Extension Days:</b> {{ $note->variation_days }}</td>
    </tr>
</table>

<h2>Cost Centres & Item Details</h2>
@foreach ($note->costs as $cost)
    {{$cost->category->name}}: {{$cost->details}}<br>
@endforeach

<h2>Note</h2>
<div class="pre">{{ $note->notes }}</div>

</body>
</html>