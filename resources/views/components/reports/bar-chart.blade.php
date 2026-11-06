@props([
    'id',
    'labels' => [],
    'datasets' => [],
    'height' => '280px',
])

<div style="height: {{ $height }};">
    <canvas id="{{ $id }}"></canvas>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById(@json($id));
            if (!el || typeof Chart === 'undefined') {
                return;
            }

            new Chart(el, {
                type: 'bar',
                data: {
                    labels: @json($labels),
                    datasets: @json($datasets),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true },
                    },
                },
            });
        });
    </script>
@endpush
