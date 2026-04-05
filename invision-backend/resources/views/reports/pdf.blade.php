<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] ?? 'Report' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; margin: 20px; }
        h1 { font-size: 18px; color: #4338ca; margin-bottom: 4px; }
        .meta { font-size: 9px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #4338ca; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        tr:nth-child(even) td { background: #f9fafb; }
        .footer { margin-top: 20px; font-size: 8px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $report['title'] ?? 'Report' }}</h1>
    <div class="meta">
        @if(!empty($report['period']))
            Period: {{ $report['period'] }} &nbsp;|&nbsp;
        @endif
        Generated: {{ $report['generated'] ?? now()->toDateTimeString() }}
    </div>

    @if(!empty($report['rows']))
        <table>
            <thead>
                <tr>
                    @foreach(array_keys($report['rows'][0]) as $header)
                        <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($report['rows'] as $row)
                    <tr>
                        @foreach($row as $value)
                            <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No data available for this report.</p>
    @endif

    <div class="footer">Invision SaaS Platform — Auto-generated report</div>
</body>
</html>
