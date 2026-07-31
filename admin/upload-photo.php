<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pageTitle = 'Photo Management';
$adminPage = 'photos';
$pdo = getDBConnection();

$categories = ['Projects', 'Certificates', 'Achievements', 'Personal Gallery'];
$error = '';
$editImage = null;

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM portfolio_images WHERE id = ?');
    $stmt->execute([$editId]);
    $editImage = $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT image_path, cloudinary_public_id FROM portfolio_images WHERE id = ?');
        $stmt->execute([$id]);
        $img = $stmt->fetch();
        if ($img) {
            deleteUploadedFile($img['cloudinary_public_id']);
            $pdo->prepare('DELETE FROM portfolio_images WHERE id = ?')->execute([$id]);
            setFlash('success', 'Image deleted successfully.');
        }
        redirect(baseUrl() . '/admin/upload-photo.php');
    }

    $title = sanitizeString($_POST['title'] ?? '', 150);
    $description = sanitizeString($_POST['description'] ?? '', 2000);
    $category = sanitizeString($_POST['category'] ?? '', 50);
    $id = (int) ($_POST['id'] ?? 0);

    if ($title === '') {
        $error = 'Title is required.';
    } elseif (!in_array($category, $categories, true)) {
        $error = 'Invalid category selected.';
    } else {
        $imagePath = null;
        $cloudinaryPublicId = null;

        if (!empty($_FILES['image']['name'])) {
            $upload = saveUploadedImage($_FILES['image'], 'portfolio');

            if (!$upload['valid']) {
                $error = $upload['error'];
            } else {
               $imagePath = $upload['path'];
               $cloudinaryPublicId = $upload['filename'];    
            }

        }

        if ($error === '') {
            if ($action === 'update' && $id > 0) {
                if ($imagePath) {
                    $old = $pdo->prepare('SELECT image_path, cloudinary_public_id FROM portfolio_images WHERE id = ?');
                    $old->execute([$id]);
                    $oldImg = $old->fetch();
                    if ($oldImg) {
                        deleteUploadedFile($oldImg['cloudinary_public_id']);
                    }
                    
                    $pdo->prepare('UPDATE portfolio_images SET title=?, description=?, category=?, image_path=?, cloudinary_public_id=? WHERE id=?')
                        ->execute([$title, $description, $category, $imagePath, $cloudinaryPublicId, $id]);
                } else {
                    $pdo->prepare('UPDATE portfolio_images SET title=?, description=?, category=? WHERE id=?')
                        ->execute([$title, $description, $category, $id]);
                }
                setFlash('success', 'Image updated successfully.');
            } else {
                if (!$imagePath) {
                   $error = 'Please upload an image.';
                } else {
                   $pdo->prepare( 'INSERT INTO portfolio_images (title, description, image_path, category, cloudinary_public_id) VALUES (?, ?, ?, ?, ?)')
                       ->execute([ $title, $description, $imagePath, $category, $cloudinaryPublicId ]);

                   setFlash('success', 'Image uploaded successfully.');
                }
            }

            if ($error === '') {
                redirect(baseUrl() . '/admin/upload-photo.php');
            }
        }
    }
}

$images = $pdo->query('SELECT * FROM portfolio_images ORDER BY uploaded_date DESC')->fetchAll();
$flash = getFlash();

require_once dirname(__DIR__) . '/includes/admin-header.php';
?>

<h1 class="h3 mb-4">Photo Management</h1>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><?= $editImage ? 'Edit Photo' : 'Upload Photo' ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="<?= $editImage ? 'update' : 'create' ?>">
                    <?php if ($editImage): ?>
                        <input type="hidden" name="id" value="<?= (int) $editImage['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required maxlength="150"
                               value="<?= e($editImage['title'] ?? $_POST['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" maxlength="2000"><?= e($editImage['description'] ?? $_POST['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= ($editImage['category'] ?? $_POST['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image <?= $editImage ? '(leave empty to keep current)' : '' ?></label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" id="imageInput" <?= $editImage ? '' : 'required' ?>>
                        <div class="form-text">Max 5MB. Allowed: JPG, PNG, GIF, WEBP</div>
                    </div>
                    <div class="mb-3" id="previewContainer" style="display:none">
                        <label class="form-label">Preview</label>
                        <img id="imagePreview" src="" alt="Preview" class="img-fluid rounded border" style="max-height:200px">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i><?= $editImage ? 'Update' : 'Upload' ?>
                    </button>
                    <?php if ($editImage): ?>
                        <a href="<?= e(baseUrl()) ?>/admin/upload-photo.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Uploaded Photos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($images)): ?>
                    <p class="text-muted mb-0">No photos uploaded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Preview</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($images as $img): ?>
                                <tr>
                                    <td>
                                        <img src="<?= e(uploadUrl($img['image_path'])) ?>" alt="" class="rounded" style="width:60px;height:60px;object-fit:cover">
                                    </td>
                                    <td><?= e($img['title']) ?></td>
                                    <td><span class="badge bg-primary"><?= e($img['category']) ?></span></td>
                                    <td><small><?= e(date('M j, Y', strtotime($img['uploaded_date']))) ?></small></td>
                                    <td>
                                        <a href="?edit=<?= (int) $img['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this image?')">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>
