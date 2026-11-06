(function () {
    function formatPeso(value) {
        if (value >= 1000 && value % 1000 === 0) {
            return '₱' + (value / 1000) + 'k';
        }

        return '₱' + Number(value).toLocaleString('en-PH');
    }

    function initRateRange(root) {
        var minInput = root.querySelector('.da-rate-range__input--min');
        var maxInput = root.querySelector('.da-rate-range__input--max');
        var fill = root.querySelector('.da-rate-range__fill');
        var valueLabel = root.querySelector('[data-da-rate-range-label]');
        var hiddenMin = root.querySelector('input[name="min_rate"]');
        var hiddenMax = root.querySelector('input[name="max_rate"]');

        if (!minInput || !maxInput || !fill) {
            return;
        }

        var floor = parseInt(root.dataset.floor || '0', 10);
        var ceil = parseInt(root.dataset.ceil || '15000', 10);
        var step = parseInt(root.dataset.step || '500', 10);
        var span = Math.max(ceil - floor, step);

        function clampPair(changed) {
            var minVal = parseInt(minInput.value, 10);
            var maxVal = parseInt(maxInput.value, 10);

            if (minVal > maxVal - step) {
                if (changed === 'min') {
                    minInput.value = String(Math.max(floor, maxVal - step));
                } else {
                    maxInput.value = String(Math.min(ceil, minVal + step));
                }
            }
        }

        function paint(changed) {
            clampPair(changed);

            var minVal = parseInt(minInput.value, 10);
            var maxVal = parseInt(maxInput.value, 10);
            var left = ((minVal - floor) / span) * 100;
            var right = ((maxVal - floor) / span) * 100;

            fill.style.left = left + '%';
            fill.style.width = Math.max(0, right - left) + '%';

            if (valueLabel) {
                var minLabel = minVal <= floor ? 'Any' : formatPeso(minVal);
                var maxLabel = maxVal >= ceil ? 'Any' : formatPeso(maxVal);
                valueLabel.textContent = minLabel + ' – ' + maxLabel;
            }

            if (hiddenMin) {
                hiddenMin.value = minVal <= floor ? '' : String(minVal);
            }

            if (hiddenMax) {
                hiddenMax.value = maxVal >= ceil ? '' : String(maxVal);
            }
        }

        minInput.addEventListener('input', function () {
            paint('min');
        });
        maxInput.addEventListener('input', function () {
            paint('max');
        });

        paint();
    }

    function boot() {
        document.querySelectorAll('[data-da-rate-range]').forEach(initRateRange);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
