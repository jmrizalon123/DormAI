(function () {
    window.dormaiGoogleMapsQueue = window.dormaiGoogleMapsQueue || [];

    window.DormaiWhenGoogleMapsReady = function (fn) {
        if (typeof fn !== 'function') {
            return;
        }

        if (window.dormaiGoogleMapsReady && window.google && window.google.maps) {
            fn();
            return;
        }

        window.dormaiGoogleMapsQueue.push(fn);
    };

    window.DormaiInitGoogleMaps = function () {
        window.dormaiGoogleMapsReady = true;
        window.dormaiGoogleMapsQueue.forEach(function (fn) {
            try {
                fn();
            } catch (error) {
                console.error('DormAI Google Maps init failed', error);
            }
        });
        window.dormaiGoogleMapsQueue = [];
    };
})();
