<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdminOrEditor();

$id = $_GET['id'] ?? null;
if (!$id) redirect('/admin/posts/index.php');

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) redirect('/admin/posts/index.php');

$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('নিরাপত্তা ত্রুটি (Invalid CSRF token)।', 'danger');
    } else {
        $title = trim($_POST['title'] ?? '');
        $category_id = $_POST['category_id'] ?? '';
        $short_description = trim($_POST['short_description'] ?? '');
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        $breaking_news = isset($_POST['breaking_news']) ? 1 : 0;
        $admin_message = trim($_POST['admin_message'] ?? '');
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $imagePath = $post['image'];

        if (empty($title) || empty($category_id) || empty($content)) {
            setFlash('অনুগ্রহ করে আবশ্যকীয় তথ্যগুলো পূরণ করুন।', 'danger');
        } else {
            $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetch()) {
                $slug .= '-' . time();
            }

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $fileName = uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                        $imagePath = 'assets/uploads/' . $fileName;
                        if ($post['image'] && file_exists(__DIR__ . '/../../' . $post['image'])) {
                            unlink(__DIR__ . '/../../' . $post['image']);
                        }
                    }
                }
            }

            if (!isset($_SESSION['flash'])) {
                $stmt = $pdo->prepare("UPDATE posts SET title=?, slug=?, short_description=?, content=?, image=?, category_id=?, breaking_news=?, status=?, admin_message=? WHERE id=?");
                if ($stmt->execute([$title, $slug, $short_description, $content, $imagePath, $category_id, $breaking_news, $status, $admin_message, $id])) {
                    setFlash('সংবাদটি সফলভাবে আপডেট হয়েছে।', 'success');
                    redirect('/admin/posts/index.php');
                } else {
                    setFlash('আপডেট করতে সমস্যা হয়েছে।', 'danger');
                }
            }
        }
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">সংবাদ সম্পাদনা</h1>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
    </div>

    <?php displayFlash(); ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
        
        <div class="row g-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">শিরোনাম <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg" value="<?php echo h($post['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">সারসংক্ষেপ</label>
                            <textarea name="short_description" class="form-control" rows="2"><?php echo h($post['short_description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">বিস্তারিত <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="15" required><?php echo h($post['content']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold">পাবলিশ অপশন</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">অবস্থা</label>
                            <select name="status" class="form-select" id="statusSelect" onchange="toggleRejectMessage()">
                                <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>প্রকাশিত</option>
                                <option value="pending" <?php echo $post['status'] === 'pending' ? 'selected' : ''; ?>>অপেক্ষমান</option>
                                <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>খসড়া</option>
                                <option value="rejected" <?php echo $post['status'] === 'rejected' ? 'selected' : ''; ?>>বাতিল</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="rejectMessageDiv" style="display: <?php echo $post['status'] === 'rejected' ? 'block' : 'none'; ?>">
                            <label class="form-label text-danger fw-semibold">বাতিল করার কারণ (রিপোর্টারকে দেখানোর জন্য)</label>
                            <textarea name="admin_message" class="form-control border-danger" rows="2" placeholder="রিপোর্টারকে জানান কেন এটি বাতিল করা হয়েছে..."><?php echo h($post['admin_message'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="breaking_news" class="form-check-input" id="breaking" value="1" <?php echo $post['breaking_news'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="breaking">ব্রেকিং নিউজ হিসেবে মার্ক করুন</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">আপডেট করুন</button>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold">ক্যাটাগরি <span class="text-danger">*</span></div>
                    <div class="card-body">
                        <select name="category_id" class="form-select" required>
                            <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $post['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo h($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold">ছবি</div>
                    <div class="card-body">
                        <?php if ($post['image']): ?>
                            <div class="mb-3">
                                <img src="<?php echo SITE_URL . '/' . $post['image']; ?>" class="img-fluid rounded border" alt="Current Image">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control mb-2" accept="image/jpeg, image/png, image/webp">
                        <small class="text-muted">নতুন ছবি দিলে আগেরটি মুছে যাবে।</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<script>
function toggleRejectMessage() {
    var status = document.getElementById('statusSelect').value;
    document.getElementById('rejectMessageDiv').style.display = (status === 'rejected') ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
