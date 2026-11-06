@isset($exportReport)
    @php
        $exportQuery = array_filter([
            'from' => isset($from) ? $from->format('Y-m-d') : null,
            'to' => isset($to) ? $to->format('Y-m-d') : null,
        ]);
    @endphp
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a
            class="btn btn-outline-secondary btn-sm"
            href="{{ route('organization.reports.export', array_merge(['report' => $exportReport, 'format' => 'csv'], $exportQuery)) }}"
        >
            <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </a>
        <a
            class="btn btn-outline-secondary btn-sm"
            href="{{ route('organization.reports.export', array_merge(['report' => $exportReport, 'format' => 'pdf'], $exportQuery)) }}"
        >
            <i class="bi bi-filetype-pdf me-1"></i> Export PDF
        </a>
    </div>
@endisset
