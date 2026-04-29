<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function index(): View
    {
        return view('reports.index');
    }

    public function uptime(Request $request): View
    {
        $filters = $this->reports->filters($request);

        return view('reports.uptime', [
            'filters' => $filters,
            'range' => $this->reports->dateRange($filters),
            'rows' => $this->reports->uptimeRows($filters),
            ...$this->reports->filterOptions(),
        ]);
    }

    public function events(Request $request): View
    {
        $filters = $this->reports->filters($request);

        return view('reports.events', [
            'filters' => $filters,
            'range' => $this->reports->dateRange($filters),
            'rows' => $this->reports->eventRows($filters),
            ...$this->reports->filterOptions(),
        ]);
    }

    public function sites(Request $request): View
    {
        $filters = $this->reports->filters($request);

        return view('reports.sites', [
            'filters' => $filters,
            'range' => $this->reports->dateRange($filters),
            'rows' => $this->reports->siteSummaryRows($filters),
            ...$this->reports->filterOptions(),
        ]);
    }

    public function clients(Request $request): View
    {
        $filters = [
            ...$this->reports->filters($request),
            'ownership_type' => 'client',
        ];

        return view('reports.clients', [
            'filters' => $filters,
            'range' => $this->reports->dateRange($filters),
            'rows' => $this->reports->clientRows($filters),
            ...$this->reports->filterOptions(),
        ]);
    }

    public function exportUptime(Request $request)
    {
        $filters = $this->reports->filters($request);
        $rows = $this->reports->uptimeRows($filters);
        $columns = [
            'Camera',
            'Organisation',
            'Site',
            'Connectivity',
            'Total monitored',
            'Online',
            'Offline',
            'Uptime %',
            'Offline incidents',
            'Longest offline',
            'Data quality',
        ];

        if ($this->format($request) === 'pdf') {
            return $this->downloadPdf('Uptime report', $filters, $columns, $rows->map(fn (array $row): array => [
                $row['camera'],
                $row['organisation'],
                $row['site'],
                $row['connectivity_type'],
                $row['total_monitored_time'],
                $row['online_time'],
                $row['offline_time'],
                number_format($row['uptime_percentage'], 2).'%',
                $row['offline_incidents'],
                $row['longest_offline_period'],
                $row['data_quality'],
            ]), 'uptime-report.pdf');
        }

        return $this->downloadCsv('uptime-report.csv', $columns, $rows, fn (array $row): array => [
            $row['camera'],
            $row['organisation'],
            $row['site'],
            $row['connectivity_type'],
            $row['total_monitored_time'],
            $row['online_time'],
            $row['offline_time'],
            number_format($row['uptime_percentage'], 2).'%',
            $row['offline_incidents'],
            $row['longest_offline_period'],
            $row['data_quality'],
        ]);
    }

    public function exportEvents(Request $request)
    {
        $filters = $this->reports->filters($request);
        $rows = $this->reports->eventRows($filters);
        $columns = ['Event time', 'Camera', 'Site', 'Organisation', 'Event type', 'Event state', 'Description'];

        if ($this->format($request) === 'pdf') {
            return $this->downloadPdf('Event report', $filters, $columns, $rows->map(fn (array $row): array => [
                $row['event_time_display'],
                $row['camera'],
                $row['site'],
                $row['organisation'],
                $row['event_type'],
                $row['event_state'],
                $row['event_description'],
            ]), 'event-report.pdf');
        }

        return $this->downloadCsv('event-report.csv', $columns, $rows, fn (array $row): array => [
            $row['event_time_display'],
            $row['camera'],
            $row['site'],
            $row['organisation'],
            $row['event_type'],
            $row['event_state'],
            $row['event_description'],
        ]);
    }

    public function exportSites(Request $request)
    {
        $filters = $this->reports->filters($request);
        $rows = $this->reports->siteSummaryRows($filters);
        $columns = ['Site', 'Organisation', 'Total cameras', 'Online', 'Offline', 'Unknown', 'Site status', 'Last event', 'Connectivity'];

        if ($this->format($request) === 'pdf') {
            return $this->downloadPdf('Site summary report', $filters, $columns, $rows->map(fn (array $row): array => [
                $row['site'],
                $row['organisation'],
                $row['total_cameras'],
                $row['online_cameras'],
                $row['offline_cameras'],
                $row['unknown_cameras'],
                ucfirst($row['site_status']),
                $row['last_event_time_display'],
                $row['connectivity_summary'],
            ]), 'site-summary-report.pdf');
        }

        return $this->downloadCsv('site-summary-report.csv', $columns, $rows, fn (array $row): array => [
            $row['site'],
            $row['organisation'],
            $row['total_cameras'],
            $row['online_cameras'],
            $row['offline_cameras'],
            $row['unknown_cameras'],
            ucfirst($row['site_status']),
            $row['last_event_time_display'],
            $row['connectivity_summary'],
        ]);
    }

    public function exportClients(Request $request)
    {
        $filters = [
            ...$this->reports->filters($request),
            'ownership_type' => 'client',
        ];
        $rows = $this->reports->clientRows($filters);
        $columns = ['Client', 'Sites', 'Cameras', 'Uptime %', 'Incidents', 'Latest event', 'Latest event time'];

        if ($this->format($request) === 'pdf') {
            return $this->downloadPdf('Client camera report', $filters, $columns, $rows->map(fn (array $row): array => [
                $row['client_name'],
                $row['sites'],
                $row['cameras'],
                number_format($row['uptime_percentage'], 2).'%',
                $row['incidents'],
                $row['latest_event'],
                $row['latest_event_time'],
            ]), 'client-camera-report.pdf');
        }

        return $this->downloadCsv('client-camera-report.csv', $columns, $rows, fn (array $row): array => [
            $row['client_name'],
            $row['sites'],
            $row['cameras'],
            number_format($row['uptime_percentage'], 2).'%',
            $row['incidents'],
            $row['latest_event'],
            $row['latest_event_time'],
        ]);
    }

    private function format(Request $request): string
    {
        $format = strtolower($request->string('format', 'csv')->toString());

        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        return $format;
    }

    private function downloadCsv(string $filename, array $columns, Collection $rows, callable $mapper): StreamedResponse
    {
        return response()->streamDownload(function () use ($columns, $rows, $mapper): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($rows as $row) {
                fputcsv($handle, $mapper($row));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function downloadPdf(string $title, array $filters, array $columns, Collection $rows, string $filename)
    {
        return Pdf::loadView('reports.print', [
            'title' => $title,
            'filters' => $filters,
            'range' => $this->reports->dateRange($filters),
            'columns' => $columns,
            'rows' => $rows,
            'generatedAt' => now(),
        ])
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}
