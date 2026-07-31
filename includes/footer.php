    </main>
    <footer class="footer bg-dark text-light py-5 mt-auto">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="text-primary">Portfolio</h5>
                    <p class="text-muted">Showcasing professional work, skills, and achievements.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= e(baseUrl()) ?>/index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="<?= e(baseUrl()) ?>/about.php" class="text-muted text-decoration-none">About</a></li>
                        <li><a href="<?= e(baseUrl()) ?>/portfolio.php" class="text-muted text-decoration-none">Portfolio</a></li>
                        <li><a href="<?= e(baseUrl()) ?>/contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Share Portfolio</h5>
                    <div class="share-buttons d-flex gap-2">
                        <button class="btn btn-outline-light btn-sm share-btn" data-share="facebook" title="Share on Facebook">
                            <i class="bi bi-facebook"></i>
                        </button>
                        <button class="btn btn-outline-light btn-sm share-btn" data-share="messenger" title="Share on Messenger">
                            <i class="bi bi-messenger"></i>
                        </button>
                        <button class="btn btn-outline-light btn-sm share-btn" data-share="copy" title="Copy Link">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <p class="text-center text-muted mb-0">&copy; <?= date('Y') ?> Portfolio Management System. All rights reserved.</p>
        </div>
    </footer>

    <div class="reaction-bar fixed-bottom bg-white border-top shadow-lg py-2">
        <div class="container d-flex justify-content-center align-items-center gap-3">
            <span class="text-muted small d-none d-sm-inline">React:</span>
            <button class="btn btn-outline-primary btn-sm reaction-btn" data-reaction="like">
                <i class="bi bi-hand-thumbs-up"></i> Like <span class="reaction-count" data-type="like">0</span>
            </button>
            <button class="btn btn-outline-danger btn-sm reaction-btn" data-reaction="love">
                <i class="bi bi-heart"></i> Love <span class="reaction-count" data-type="love">0</span>
            </button>
            <button class="btn btn-outline-success btn-sm reaction-btn" data-reaction="helpful">
                <i class="bi bi-lightbulb"></i> Helpful <span class="reaction-count" data-type="helpful">0</span>
            </button>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalTitle">Image Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" alt="Preview" id="imageModalImg" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script>
        window.APP_BASE_URL = <?= json_encode(baseUrl()) ?>;
        window.CSRF_TOKEN = <?= json_encode(generateCsrfToken()) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= assetUrl('js/main.js') ?>"></script>
</body>
</html>
