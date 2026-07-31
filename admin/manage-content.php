<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pageTitle = 'Manage Content';
$adminPage = 'content';
$pdo = getDBConnection();
$tab = sanitizeString($_GET['tab'] ?? 'about', 20);
$error = '';

$about = $pdo->query('SELECT * FROM about LIMIT 1')->fetch() ?: [];
$contact = $pdo->query('SELECT * FROM contact LIMIT 1')->fetch() ?: [];
$projects = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $section = $_POST['section'] ?? '';

    try {
        if ($section === 'about') {
            $fullname = sanitizeString($_POST['fullname'] ?? '', 100);
            $description = sanitizeString($_POST['description'] ?? '', 5000);
            $skills = sanitizeString($_POST['skills'] ?? '', 2000);
            $education = sanitizeString($_POST['education'] ?? '', 5000);
            $experience = sanitizeString($_POST['experience'] ?? '', 5000);
            $achievements = sanitizeString($_POST['achievements'] ?? '', 5000);

            if ($about) {
                $pdo->prepare('UPDATE about SET fullname=?, description=?, skills=?, education=?, experience=?, achievements=? WHERE id=?')
                    ->execute([$fullname, $description, $skills, $education, $experience, $achievements, $about['id']]);
            } else {
                $pdo->prepare('INSERT INTO about (fullname, description, skills, education, experience, achievements) VALUES (?,?,?,?,?,?)')
                    ->execute([$fullname, $description, $skills, $education, $experience, $achievements]);
            }
            setFlash('success', 'About section updated.');
            redirect(baseUrl() . '/admin/manage-content.php?tab=about');
        }

        if ($section === 'contact') {
            $phone = sanitizeString($_POST['phone'] ?? '', 30);
            $email = sanitizeString($_POST['email'] ?? '', 100);
            $facebook = sanitizeString($_POST['facebook'] ?? '', 255);
            $messenger = sanitizeString($_POST['messenger'] ?? '', 255);
            $linkedin = sanitizeString($_POST['linkedin'] ?? '', 255);
            $github = sanitizeString($_POST['github'] ?? '', 255);
            $twitter = sanitizeString($_POST['twitter'] ?? '', 255);

            if ($contact) {
                $pdo->prepare('UPDATE contact SET phone=?, email=?, facebook=?, messenger=?, linkedin=?, github=?, twitter=? WHERE id=?')
                    ->execute([$phone, $email, $facebook, $messenger, $linkedin, $github, $twitter, $contact['id']]);
            } else {
                $pdo->prepare('INSERT INTO contact (phone, email, facebook, messenger, linkedin, github, twitter) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$phone, $email, $facebook, $messenger, $linkedin, $github, $twitter]);
            }
            setFlash('success', 'Contact information updated.');
            redirect(baseUrl() . '/admin/manage-content.php?tab=contact');
        }

        if ($section === 'project') {
            $action = $_POST['action'] ?? 'create';
            $id = (int) ($_POST['id'] ?? 0);

            if ($action === 'delete' && $id > 0) {
                $stmt = $pdo->prepare('SELECT image_path, cloudinary_public_id FROM projects WHERE id = ?');
                $stmt->execute([$id]);
                $proj = $stmt->fetch();
                if ($proj && $proj['image_path']) {
                    deleteUploadedFile($proj['cloudinary_public_id']);
                }
                $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
                setFlash('success', 'Project deleted.');
                redirect(baseUrl() . '/admin/manage-content.php?tab=projects');
            }

            $title = sanitizeString($_POST['title'] ?? '', 150);
            $description = sanitizeString($_POST['description'] ?? '', 5000);
            $technologies = sanitizeString($_POST['technologies'] ?? '', 255);
            $projectLink = sanitizeString($_POST['project_link'] ?? '', 255);
            $imagePath = null;
            $cloudinaryPublicId = null;

            if (!empty($_FILES['project_image']['name'])) {
                $upload = saveUploadedImage($_FILES['project_image'], 'project');
                if (!$upload['valid']) {
                    $error = $upload['error'];
                } else {
                   $imagePath = $upload['path'];
                   $cloudinaryPublicId = $upload['filename'];
                }
            }

            if ($error === '' && $title !== '') {
                if ($action === 'update' && $id > 0) {
                    if ($imagePath) {
                        $old = $pdo->prepare('SELECT image_path, cloudinary_public_id FROM projects WHERE id = ?');
                        $old->execute([$id]);
                        $oldProj = $old->fetch();
                        if ($oldProj && $oldProj['image_path']) {
                            deleteUploadedFile($oldProj['cloudinary_public_id']);
                        }
                        $pdo->prepare('UPDATE projects SET title=?, description=?, technologies=?, project_link=?, image_path=?, cloudinary_public_id=? WHERE id=?')
                            ->execute([$title, $description, $technologies, $projectLink, $imagePath, $cloudinaryPublicId, $id]);
                    } else {
                        $pdo->prepare('UPDATE projects SET title=?, description=?, technologies=?, project_link=? WHERE id=?')
                            ->execute([$title, $description, $technologies, $projectLink, $id]);
                    }
                    setFlash('success', 'Project updated.');
                } else {
                    $pdo->prepare('INSERT INTO projects (title, description, technologies, project_link, image_path, cloudinary_public_id) VALUES (?,?,?,?,?,?)')
                        ->execute([$title, $description, $technologies, $projectLink, $imagePath, $cloudinaryPublicId]);
                    setFlash('success', 'Project added.');
                }
                redirect(baseUrl() . '/admin/manage-content.php?tab=projects');
            }
        }
    } catch (Throwable $e) {
        $error = 'Failed to save content.';
    }
}

$editProject = null;
if (isset($_GET['edit_project'])) {
    $stmt = $pdo->prepare('SELECT image_path FROM projects WHERE id = ?');
    $stmt->execute([(int) $_GET['edit_project']]);
    $editProject = $stmt->fetch() ?: null;
    $tab = 'projects';
}

$about = $pdo->query('SELECT * FROM about LIMIT 1')->fetch() ?: [];
$contact = $pdo->query('SELECT * FROM contact LIMIT 1')->fetch() ?: [];
$projects = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();
$flash = getFlash();

require_once dirname(__DIR__) . '/includes/admin-header.php';
?>

<h1 class="h3 mb-4">Manage Content</h1>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link <?= $tab === 'about' ? 'active' : '' ?>" href="?tab=about">About Me</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab === 'projects' ? 'active' : '' ?>" href="?tab=projects">Portfolio Projects</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab === 'contact' ? 'active' : '' ?>" href="?tab=contact">Contact Info</a></li>
</ul>

<?php if ($tab === 'about'): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="about">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="fullname" class="form-control" required value="<?= e($about['fullname'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Profile Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($about['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Skills (comma-separated)</label>
                    <textarea name="skills" class="form-control" rows="2"><?= e($about['skills'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Education</label>
                    <textarea name="education" class="form-control" rows="3"><?= e($about['education'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Experience</label>
                    <textarea name="experience" class="form-control" rows="4"><?= e($about['experience'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Achievements</label>
                    <textarea name="achievements" class="form-control" rows="3"><?= e($about['achievements'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save About Section</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($tab === 'contact'): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="contact">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mobile Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($contact['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= e($contact['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook" class="form-control" value="<?= e($contact['facebook'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Messenger URL</label>
                    <input type="url" name="messenger" class="form-control" value="<?= e($contact['messenger'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">LinkedIn URL</label>
                    <input type="url" name="linkedin" class="form-control" value="<?= e($contact['linkedin'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">GitHub URL</label>
                    <input type="url" name="github" class="form-control" value="<?= e($contact['github'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Twitter/X URL</label>
                    <input type="url" name="twitter" class="form-control" value="<?= e($contact['twitter'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save Contact Info</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($tab === 'projects'): ?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0"><?= $editProject ? 'Edit Project' : 'Add Project' ?></h5></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="project">
                    <input type="hidden" name="action" value="<?= $editProject ? 'update' : 'create' ?>">
                    <?php if ($editProject): ?><input type="hidden" name="id" value="<?= (int)$editProject['id'] ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Project Title</label>
                        <input type="text" name="title" class="form-control" required value="<?= e($editProject['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= e($editProject['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Technologies (comma-separated)</label>
                        <input type="text" name="technologies" class="form-control" value="<?= e($editProject['technologies'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project Link</label>
                        <input type="url" name="project_link" class="form-control" value="<?= e($editProject['project_link'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project Image</label>
                        <input type="file" name="project_image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    <button type="submit" class="btn btn-primary"><?= $editProject ? 'Update' : 'Add' ?> Project</button>
                    <?php if ($editProject): ?><a href="?tab=projects" class="btn btn-secondary">Cancel</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Projects List</h5></div>
            <div class="card-body p-0">
                <?php if (empty($projects)): ?>
                    <p class="text-muted p-3">No projects yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($projects as $proj): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= e($proj['title']) ?></strong>
                                <br><small class="text-muted"><?= e(substr($proj['description'] ?? '', 0, 60)) ?>...</small>
                            </div>
                            <div>
                                <a href="?tab=projects&edit_project=<?= (int)$proj['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete project?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="section" value="project">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$proj['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>
