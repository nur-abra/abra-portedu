(function () {
    'use strict';

    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput && previewContainer && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = imageInput.files[0];
            if (!file) {
                previewContainer.style.display = 'none';
                return;
            }

            const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowed.includes(file.type)) {
                alert('Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
                imageInput.value = '';
                previewContainer.style.display = 'none';
                return;
            }

            if (file.size > 5242880) {
                alert('File exceeds 5MB limit.');
                imageInput.value = '';
                previewContainer.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }
})();
