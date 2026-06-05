<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdminOrEditor();

$id = $_GET['id'] ?? null;
if (!$id) redirect('/admin/categories/index.php');

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) redirect('/admin/categories/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('নিরাপত্তা ত্রুটি (Invalid CSRF token)।', 'danger');
    } else {
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        if (empty($name)) {
            setFlash('ক্যাটাগরির নাম দেওয়া আবশ্যক।', 'danger');
        } else {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetch()) {
                setFlash('এই নামের ক্যাটাগরি ইতিমধ্যে রয়েছে। অন্য নাম দিন।', 'danger');
            } else {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$name, $slug, $status, $id])) {
                    setFlash('ক্যাটাগরি সফলভাবে আপডেট হয়েছে।', 'success');
                    redirect('/admin/categories/index.php');
                } else {
                    setFlash('ক্যাটাগরি আপডেট করতে সমস্যা হয়েছে।', 'danger');
                }
            }
        }
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">ক্যাটাগরি সম্পাদনা</h1>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
    </div>

    <?php displayFlash(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ক্যাটাগরির নাম <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?php echo h($category['name']); ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">অবস্থা</label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo $category['status'] === 'active' ? 'selected' : ''; ?>>সক্রিয় (Active)</option>
                                <option value="inactive" <?php echo $category['status'] === 'inactive' ? 'selected' : ''; ?>>নিষ্ক্রিয় (Inactive)</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4 fw-bold">আপডেট করুন</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
