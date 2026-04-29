<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>{{ $title }}</title>
        <style>
            body {
                color: #0f172a;
                font-family: DejaVu Sans, sans-serif;
                font-size: 11px;
                line-height: 1.45;
                margin: 0;
            }

            .header {
                border-bottom: 2px solid #1b3144;
                margin-bottom: 18px;
                padding-bottom: 14px;
            }

            .brand {
                color: #1b3144;
                font-size: 18px;
                font-weight: bold;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .placeholder {
                border: 1px solid #cbd5e1;
                color: #64748b;
                display: inline-block;
                margin-top: 6px;
                padding: 5px 8px;
            }

            h1 {
                font-size: 22px;
                margin: 18px 0 4px;
            }

            .meta {
                color: #475569;
                margin: 0 0 14px;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th {
                background: #f1f5f9;
                color: #334155;
                font-size: 10px;
                text-align: left;
                text-transform: uppercase;
            }

            th,
            td {
                border: 1px solid #cbd5e1;
                padding: 7px;
                vertical-align: top;
            }

            .footer {
                color: #64748b;
                font-size: 10px;
                margin-top: 14px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="brand">Council CCTV Monitoring Report</div>
            <div class="placeholder">Council branding placeholder | Micronet Solutions CCTV platform</div>
            <h1>{{ $title }}</h1>
            <p class="meta">
                Reporting window: {{ $range['label'] }}<br>
                Generated: {{ $generatedAt->format('d M Y H:i') }}
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($columns as $index => $column)
                            <td>{{ $row[$index] ?? '' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}">No data matched the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Uptime reports use camera_status_logs where available. Rows with no prior historical status are estimated from current camera state.
        </div>
    </body>
</html>
