<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid CSRF token.';
    } else {
        $title             = trim($_POST['title'] ?? '');
        $category_id       = $_POST['category_id'] ?? '';
        $short_description = trim($_POST['short_description'] ?? '');
        $content           = $_POST['content'] ?? '';
        $action            = $_POST['action'] ?? 'draft'; // 'draft' or 'submit'
        $slug              = createSlug($title);
        $imagePath         = null;

        if (empty($title) || empty($category_id) || empty($content)) {
            $_SESSION['error'] = 'Title, category, and content are required.';
        } else {
            // Ensure unique slug
            $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $slug .= '-' . bin2hex(random_bytes(3));
            }

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName   = time() . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($_FILES['image']['name']));
                $targetFile = $uploadDir . $fileName;
                $ext        = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

                if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        $imagePath = 'assets/uploads/' . $fileName;
                    }
                } else {
                    $_SESSION['error'] = 'Only JPG, PNG, GIF, WEBP images are allowed.';
                }
            }

            if (!isset($_SESSION['error'])) {
                $status = ($action === 'submit') ? 'pending' : 'draft';
                $stmt = $pdo->prepare("INSERT INTO posts (title, slug, short_description, content, image, category_id, author_id, status) VALUES (?,?,?,?,?,?,?,?)");
                if ($stmt->execute([$title, $slug, $short_description, $content, $imagePath, $category_id, $_SESSION['user_id'], $status])) {
                    $_SESSION['success'] = ($status === 'pending') ? 'Article submitted for review!' : 'Draft saved successfully.';
                    redirect('/reporter/articles.php?status=' . $status);
                } else {
                    $_SESSION['error'] = 'Failed to save article.';
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Write New Article</h1>
</div>

<?php flashMessages(); ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">

    <div class="row">
        <div class="col-lg-8">
            <div class="mb-3">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder="Enter article title..." required>
            </div>
            <div class="mb-3">
                <label for="short_description" class="form-label">Short Description</label>
                <textarea class="form-control" id="short_description" name="short_description" rows="2" placeholder="Brief summary..."></textarea>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                <textarea class="form-control" id="content" name="content" rows="18" placeholder="Write your article content here..." required></textarea>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Publish</div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Save as draft or submit for editor/admin review before publication.</p>
                    <div class="d-grid gap-2">
                        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                            <i class="bi bi-file-earmark me-1"></i> Save Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="btn btn-danger">
                            <i class="bi bi-send me-1"></i> Submit for Review
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Category <span class="text-danger">*</span></div>
                <div class="card-body">
                    <select class="form-select" name="category_id" required>
                        <option value="">Select category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo h($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Featured Image</div>
                <div class="card-body">
                    <input type="file" class="form-control" name="image" accept="image/*">
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>
