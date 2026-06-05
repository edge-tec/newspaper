<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid CSRF token.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $category_id = $_POST['category_id'] ?? '';
        $short_description = trim($_POST['short_description'] ?? '');
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        $breaking_news = isset($_POST['breaking_news']) ? 1 : 0;
        $author_id = $_SESSION['user_id'];
        $slug = createSlug($title);
        $imagePath = null;

        if (empty($title) || empty($category_id) || empty($content)) {
            $_SESSION['error'] = 'Please fill in all required fields.';
        } else {
            // Check if slug exists
            $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                // Append random string to slug
                $slug .= '-' . bin2hex(random_bytes(3));
            }

            // Image upload handling
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $fileName = preg_replace("/[^a-zA-Z0-9.\-_]/", "", $fileName);
                $targetFile = $uploadDir . $fileName;
                $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($fileType, $allowedTypes)) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        $imagePath = 'assets/uploads/' . $fileName;
                    } else {
                        $_SESSION['error'] = 'Failed to upload image.';
                    }
                } else {
                    $_SESSION['error'] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.';
                }
            }

            if (!isset($_SESSION['error'])) {
                $stmt = $pdo->prepare("INSERT INTO posts (title, slug, short_description, content, image, category_id, author_id, breaking_news, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$title, $slug, $short_description, $content, $imagePath, $category_id, $author_id, $breaking_news, $status])) {
                    $_SESSION['success'] = 'Post created successfully.';
                    redirect('/admin/posts/index.php');
                } else {
                    $_SESSION['error'] = 'Failed to create post.';
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add Post</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php" class="btn btn-sm btn-outline-secondary">Back to Posts</a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
    
    <div class="row">
        <div class="col-md-8">
            <div class="mb-3">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            
            <div class="mb-3">
                <label for="short_description" class="form-label">Short Description</label>
                <textarea class="form-control" id="short_description" name="short_description" rows="2"></textarea>
            </div>
            
            <div class="mb-3">
                <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                <!-- In a real app, integrate TinyMCE or CKEditor here -->
                <textarea class="form-control" id="content" name="content" rows="15" required></textarea>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Publish Options</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="breaking_news" name="breaking_news" value="1">
                        <label class="form-check-label" for="breaking_news">Mark as Breaking News</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Save Post</button>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-header">Category <span class="text-danger">*</span></div>
                <div class="card-body">
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo h($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-header">Featured Image</div>
                <div class="card-body">
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>
