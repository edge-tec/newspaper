<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
requireAdminOrEditor();

$id = $_GET['id'] ?? null;
if (!$id) {
    redirect('/admin/categories/index.php');
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    redirect('/admin/categories/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $slug = createSlug($name);

        if (empty($name)) {
            $_SESSION['error'] = 'Category name is required.';
        } else {
            // Check if slug exists for another category
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Category with this name already exists.';
            } else {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$name, $slug, $status, $id])) {
                    $_SESSION['success'] = 'Category updated successfully.';
                    redirect('/admin/categories/index.php');
                } else {
                    $_SESSION['error'] = 'Something went wrong.';
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Category</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php" class="btn btn-sm btn-outline-secondary">Back to Categories</a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo h($category['name']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?php echo $category['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $category['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Category</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
