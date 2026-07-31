(function () {
    'use strict';

    const baseUrl = window.APP_BASE_URL || '';
    const csrfToken = window.CSRF_TOKEN || '';

    // Image modal preview
    document.querySelectorAll('.gallery-thumb').forEach(function (img) {
        img.addEventListener('click', function () {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('imageModalImg');
            const modalTitle = document.getElementById('imageModalTitle');
            if (modal && modalImg) {
                modalImg.src = img.src;
                modalTitle.textContent = img.dataset.title || 'Image Preview';
                new bootstrap.Modal(modal).show();
            }
        });
    });

    // Load and display comments
    function loadComments() {
        const container = document.getElementById('commentsContainer');
        if (!container) return;

        fetch(baseUrl + '/comments.php')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success || !data.comments.length) {
                    container.innerHTML = '<p class="text-muted">No approved comments yet.</p>';
                    return;
                }
                container.innerHTML = data.comments.map(function (c) {
                    const date = new Date(c.created_at).toLocaleDateString();
                    return '<div class="comment-item card border-0 shadow-sm p-3 mb-2">' +
                        '<strong>' + escapeHtml(c.visitor_name) + '</strong>' +
                        '<small class="text-muted ms-2">' + date + '</small>' +
                        '<p class="mb-0 mt-2">' + escapeHtml(c.comment) + '</p></div>';
                }).join('');
            })
            .catch(function () {
                container.innerHTML = '<p class="text-muted">Unable to load comments.</p>';
            });
    }

    // Comment form submission
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(commentForm);
            const payload = {
                visitor_name: formData.get('visitor_name'),
                email: formData.get('email'),
                comment: formData.get('comment'),
                csrf_token: csrfToken
            };

            fetch(baseUrl + '/comments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        commentForm.reset();
                        alert(data.message);
                        loadComments();
                    } else {
                        alert(data.message || 'Failed to submit comment.');
                    }
                })
                .catch(function () {
                    alert('Failed to submit comment.');
                });
        });

        loadComments();
    }

    // Reactions
    function loadReactions() {
        fetch(baseUrl + '/reactions.php')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) return;
                Object.keys(data.counts).forEach(function (type) {
                    const el = document.querySelector('.reaction-count[data-type="' + type + '"]');
                    if (el) el.textContent = data.counts[type];
                });
                (data.user_reactions || []).forEach(function (type) {
                    const btn = document.querySelector('.reaction-btn[data-reaction="' + type + '"]');
                    if (btn) btn.classList.add('active');
                });
            })
            .catch(function () {});
    }

    document.querySelectorAll('.reaction-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.classList.contains('active')) return;

            fetch(baseUrl + '/reactions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    reaction_type: btn.dataset.reaction,
                    csrf_token: csrfToken
                })
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        btn.classList.add('active');
                        if (data.counts) {
                            Object.keys(data.counts).forEach(function (type) {
                                const el = document.querySelector('.reaction-count[data-type="' + type + '"]');
                                if (el) el.textContent = data.counts[type];
                            });
                        }
                    } else {
                        alert(data.message || 'Could not record reaction.');
                    }
                })
                .catch(function () {
                    alert('Failed to record reaction.');
                });
        });
    });

    loadReactions();

    // Share buttons
    document.querySelectorAll('.share-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const shareType = btn.dataset.share;
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);

            if (shareType === 'facebook') {
                window.open('https://www.facebook.com/sharer/sharer.php?u=' + url, '_blank', 'width=600,height=400');
            } else if (shareType === 'messenger') {
                window.open('https://www.facebook.com/dialog/send?link=' + url + '&app_id=0&redirect_uri=' + url, '_blank', 'width=600,height=400');
            } else if (shareType === 'copy') {
                navigator.clipboard.writeText(window.location.href).then(function () {
                    alert('Portfolio link copied to clipboard!');
                }).catch(function () {
                    prompt('Copy this link:', window.location.href);
                });
            }
        });
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
