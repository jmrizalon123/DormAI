(function () {
    var input = document.getElementById('heroImages');
    var preview = document.getElementById('heroImagesPreview');

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function () {
        preview.innerHTML = '';
        var files = input.files;

        if (!files || files.length === 0) {
            preview.classList.add('d-none');
            return;
        }

        preview.classList.remove('d-none');

        Array.from(files).forEach(function (file) {
            if (!file.type.startsWith('image/')) {
                return;
            }

            var col = document.createElement('div');
            col.className = 'col-6 col-md-4';

            var wrap = document.createElement('div');
            wrap.className = 'border rounded overflow-hidden';

            var ratio = document.createElement('div');
            ratio.className = 'ratio ratio-16x9';

            var img = document.createElement('img');
            img.className = 'object-fit-cover';
            img.alt = file.name;

            var caption = document.createElement('div');
            caption.className = 'small text-muted px-2 py-1 text-truncate';
            caption.textContent = file.name;

            ratio.appendChild(img);
            wrap.appendChild(ratio);
            wrap.appendChild(caption);
            col.appendChild(wrap);
            preview.appendChild(col);

            var reader = new FileReader();
            reader.onload = function (event) {
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    });
})();
