(function () {
    var root = document.querySelector('[data-da-hero-slideshow]');

    if (!root) {
        return;
    }

    var slides = root.querySelectorAll('.da-marketplace-hero__photo');

    if (slides.length < 2) {
        return;
    }

    var intervalMs = 6000;
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var index = 0;
    var timerId = null;

    var textSyncEls = document.querySelectorAll('[data-da-hero-text-sync]');
    var textFadeOutMs = 280;
    var textFadeInDelayMs = 40;

    function setHeroTextFaded(on) {
        textSyncEls.forEach(function (el) {
            el.classList.toggle('is-faded', on);
        });
    }

    function applySlide(nextIndex) {
        slides[index].classList.remove('is-active');
        index = ((nextIndex % slides.length) + slides.length) % slides.length;
        slides[index].classList.add('is-active');
    }

    function goTo(nextIndex) {
        if (textSyncEls.length > 0 && !prefersReduced) {
            setHeroTextFaded(true);
            window.setTimeout(function () {
                applySlide(nextIndex);
                window.setTimeout(function () {
                    setHeroTextFaded(false);
                }, textFadeInDelayMs);
            }, textFadeOutMs);
        } else {
            applySlide(nextIndex);
        }
    }

    function next() {
        goTo(index + 1);
    }

    function prev() {
        goTo(index - 1);
    }

    function stopTimer() {
        if (timerId !== null) {
            window.clearInterval(timerId);
            timerId = null;
        }
    }

    function startTimer() {
        stopTimer();
        if (!prefersReduced) {
            timerId = window.setInterval(next, intervalMs);
        }
    }

    document.querySelectorAll('[data-da-hero-slide]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var dir = btn.getAttribute('data-da-hero-slide');
            if (dir === 'prev') {
                prev();
            } else if (dir === 'next') {
                next();
            }
            startTimer();
        });
    });

    if (!prefersReduced) {
        startTimer();
    }
})();
