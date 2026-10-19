<?php

namespace App\Livewire\Admin\Analytics;

use App\Services\AnalyticsService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $datePreset = 'last_30_days';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $activeChartMetric = 'revenue';

    protected $queryString = [
        'datePreset' => ['except' => 'last_30_days'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount(AnalyticsService $analyticsService): void
    {
        if (empty($this->dateFrom) || empty($this->dateTo)) {
            [$start, $end] = $analyticsService->resolveDateRange($this->datePreset);
            $this->dateFrom = $start->format('Y-m-d');
            $this->dateTo = $end->format('Y-m-d');
        }
    }

    public function setPreset(string $preset, ?AnalyticsService $analyticsService = null): void
    {
        $this->datePreset = $preset;
        $service = $analyticsService ?? app(AnalyticsService::class);
        [$start, $end] = $service->resolveDateRange($preset);
        $this->dateFrom = $start->format('Y-m-d');
        $this->dateTo = $end->format('Y-m-d');
    }

    public function updatedDateFrom(): void
    {
        $this->datePreset = 'custom';
        if (! empty($this->dateTo) && ! empty($this->dateFrom) && $this->dateFrom > $this->dateTo) {
            $this->dateTo = $this->dateFrom;
        }
    }

    public function updatedDateTo(): void
    {
        $this->datePreset = 'custom';
        if (! empty($this->dateFrom) && ! empty($this->dateTo) && $this->dateTo < $this->dateFrom) {
            $this->dateFrom = $this->dateTo;
        }
    }

    public function clearCustomDates(?AnalyticsService $analyticsService = null): void
    {
        $this->setPreset('last_30_days', $analyticsService);
    }

    public function setChartMetric(string $metric): void
    {
        if (in_array($metric, ['revenue', 'orders'], true)) {
            $this->activeChartMetric = $metric;
        }
    }

    public function exportCsv(AnalyticsService $analyticsService): StreamedResponse
    {
        [$start, $end] = $analyticsService->resolveDateRange($this->datePreset, $this->dateFrom, $this->dateTo);
        $csvContent = $analyticsService->generateCsvReport($start, $end);
        $fileName = 'business-report-'.$start->format('Y-m-d').'-to-'.$end->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function render(AnalyticsService $analyticsService)
    {
        [$start, $end, $rangeLabel] = $analyticsService->resolveDateRange(
            $this->datePreset,
            $this->dateFrom,
            $this->dateTo
        );

        [$prevStart, $prevEnd] = $analyticsService->getComparisonPeriod($start, $end);

        $summary = $analyticsService->getSummary($start, $end);
        $trends = $analyticsService->getTrends($start, $end);
        $topProducts = $analyticsService->getTopProducts($start, $end, 8);

        return view('livewire.admin.analytics.index', [
            'start' => $start,
            'end' => $end,
            'prevStart' => $prevStart,
            'prevEnd' => $prevEnd,
            'rangeLabel' => $rangeLabel,
            'summary' => $summary,
            'trends' => $trends,
            'topProducts' => $topProducts,
        ]);
    }
}
