<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $name }} · Summit Programme</title>
    <style>
        @page { margin: 32px 36px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #071411; }
        .band { background: #06454c; color: #fff; padding: 20px 22px; border-radius: 8px; }
        .band h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .band h1 span { color: #eab94b; }
        .band p { margin: 6px 0 0; font-size: 11px; color: #d6e9eb; }
        h2 { margin: 22px 0 2px; font-size: 15px; color: #087f8c; }
        .date { margin: 0 0 8px; color: #4b5a57; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .04em; color: #4b5a57; border-bottom: 1.5px solid #087f8c; padding: 6px 8px; }
        td { vertical-align: top; border-bottom: 1px solid #dbe4e3; padding: 8px; }
        .time { white-space: nowrap; font-weight: bold; width: 70px; }
        .type { display: inline-block; background: #e2f1f7; border-radius: 10px; padding: 2px 8px; font-size: 9px; font-weight: bold; }
        .title { font-weight: bold; font-size: 12px; }
        .summary { color: #4b5a57; margin-top: 2px; }
        .empty { padding: 10px 8px; color: #4b5a57; background: #f3f7f7; border-radius: 6px; }
        .foot { margin-top: 24px; font-size: 9px; color: #4b5a57; }
    </style>
</head>
<body>
    <div class="band">
        <h1>Summit <span>Programme</span></h1>
        <p>{{ $name }} · {{ $place }}</p>
    </div>

    @foreach ($days as $day)
        <h2>{{ $day['label'] }}</h2>
        <p class="date">{{ $day['date'] }}</p>
        @if ($day['sessions'])
            <table>
                <thead><tr><th>Time</th><th>Session</th><th>Type</th><th>Hall</th><th>Speaker</th></tr></thead>
                <tbody>
                    @foreach ($day['sessions'] as $s)
                        <tr>
                            <td class="time">{{ str_replace('–', ' – ', $s['time']) }}</td>
                            <td>
                                <div class="title">{{ $s['title'] }}</div>
                                @if (! empty($s['summary']))<div class="summary">{{ $s['summary'] }}</div>@endif
                            </td>
                            <td><span class="type">{{ $s['type'] }}</span></td>
                            <td>{{ $s['hall'] }}</td>
                            <td>{{ $s['speaker'] ?? 'Speaker TBA' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">Announcing soon. Sessions for this day are published as they are confirmed.</p>
        @endif
    @endforeach

    <p class="foot">Times are Dhaka time (GMT+6). The programme may change; the latest version is always on the website's agenda page.</p>
</body>
</html>
