document.querySelectorAll("[data-sampul-preview]").forEach(function (input) {
    var preview = document.getElementById(input.dataset.sampulPreview);
    var status = document.getElementById(input.dataset.sampulStatus);
    var objectUrl = null;

    if (!preview || !status) {
        return;
    }

    input.addEventListener("change", function () {
        if (objectUrl !== null) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }

        var file = input.files && input.files[0];
        if (!file) {
            preview.hidden = true;
            status.textContent = "Belum ada file dipilih.";
            return;
        }

        objectUrl = URL.createObjectURL(file);
        preview.onload = function () {
            var scale = Math.min(1, 160 / preview.naturalWidth, 240 / preview.naturalHeight);
            preview.width = Math.max(1, Math.round(preview.naturalWidth * scale));
            preview.height = Math.max(1, Math.round(preview.naturalHeight * scale));
            preview.hidden = false;
            status.textContent = "Pratinjau sampul yang dipilih:";
        };
        preview.onerror = function () {
            preview.hidden = true;
            status.textContent = "Pratinjau gagal dimuat. Pilih file gambar yang valid.";
        };
        preview.src = objectUrl;
    });
});
