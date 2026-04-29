<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Annual Service Report - {{ $task->title }}</title>
        <style>
            body {
                color: #0f172a;
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
                line-height: 1.5;
                margin: 0;
            }

            h1 {
                color: #1b3144;
                font-size: 22px;
                margin: 18px 0 4px;
            }

            h2 {
                border-bottom: 1px solid #cbd5e1;
                color: #1b3144;
                font-size: 15px;
                margin: 18px 0 8px;
                padding-bottom: 4px;
            }

            .brand {
                border-bottom: 2px solid #1b3144;
                padding-bottom: 12px;
            }

            .placeholder {
                border: 1px solid #cbd5e1;
                color: #64748b;
                display: inline-block;
                margin-top: 6px;
                padding: 5px 8px;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #cbd5e1;
                padding: 7px;
                text-align: left;
                vertical-align: top;
            }

            th {
                background: #f1f5f9;
                color: #334155;
                width: 28%;
            }
        </style>
    </head>
    <body>
        <div class="brand">
            <strong>Council CCTV Annual Service Report</strong>
            <div class="placeholder">Council branding placeholder | Micronet Solutions CCTV platform</div>
        </div>

        <h1>{{ $task->title }}</h1>
        <p>Generated {{ $generatedAt->format('d M Y H:i') }}</p>

        <h2>Estate Details</h2>
        <table>
            <tr><th>Organisation</th><td>{{ $task->organisation?->name ?: $task->site?->organisation?->name ?: $task->camera?->site?->organisation?->name ?: 'Not set' }}</td></tr>
            <tr><th>Site</th><td>{{ $task->site?->name ?: $task->camera?->site?->name ?: 'Not set' }}</td></tr>
            <tr><th>Camera</th><td>{{ $task->camera?->name ?: 'Not linked' }}</td></tr>
            <tr><th>Engineer</th><td>{{ $task->assignedUser?->name ?: 'Unassigned' }}</td></tr>
            <tr><th>Scheduled date</th><td>{{ optional($task->scheduled_for)->format('d M Y') ?? 'Not set' }}</td></tr>
            <tr><th>Completed date</th><td>{{ optional($task->completed_at)->format('d M Y H:i') ?? 'Not completed' }}</td></tr>
        </table>

        <h2>Camera Connectivity</h2>
        <table>
            <tr><th>Connectivity type</th><td>{{ $task->camera ? str($task->camera->connectivity_type ?: 'unknown')->replace('_', ' ')->title() : 'No camera linked' }}</td></tr>
            <tr><th>Provider</th><td>{{ $task->camera?->connectivity_provider ?: 'Not set' }}</td></tr>
            <tr><th>SIM / ICCID</th><td>{{ trim(($task->camera?->sim_number ?: '').' '.$task->camera?->sim_iccid) ?: 'Not set' }}</td></tr>
            <tr><th>Router</th><td>{{ trim(($task->camera?->router_model ?: '').' '.$task->camera?->router_serial) ?: 'Not set' }}</td></tr>
            <tr><th>WAN IP</th><td>{{ $task->camera?->wan_ip_address ?: 'Not set' }}</td></tr>
        </table>

        <h2>Findings</h2>
        <p>{{ $task->notes ?: 'No findings recorded.' }}</p>

        <h2>Recommendations</h2>
        <p>{{ $task->engineer_recommendations ?: 'No recommendations recorded.' }}</p>

        <h2>Completion Notes</h2>
        <p>{{ $task->completion_notes ?: 'No completion notes recorded.' }}</p>

        <h2>Attachments</h2>
        @if ($task->attachments->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Uploaded by</th>
                        <th>Uploaded date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($task->attachments as $attachment)
                        <tr>
                            <td>{{ $attachment->filename }}</td>
                            <td>{{ $attachment->uploadedBy?->name ?: 'Unknown' }}</td>
                            <td>{{ $attachment->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No attachments uploaded.</p>
        @endif
    </body>
</html>
