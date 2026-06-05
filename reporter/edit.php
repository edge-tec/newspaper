<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$userId = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;
if (!$id) redirect('/reporter/articles.php');

// Fetch post - ensure it belongs to reporter and is NOT published/pending
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND author_id = ? AND status IN ('draft', 'rejected')");
$stmt->execute([$id, $userId]);
$post = $stmt->fetch();

if (!$post) {
    setFlash("এই খবরটি সম্পাদনযোগ্য নয়। এটি হয়তো প্রকাশিত হয়েছে বা আপনার লেখা নয়।", "warning");
    redirect('/reporter/articles.php');
}

$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $title      = trim($_POST['title'] ?? '');
        $categoryId = $_POST['category_id'] ?? null;
        $shortDesc  = trim($_POST['short_description'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $action     = $_POST['action'] ?? 'draft'; // 'draft' or 'submit'
        
        $status = ($action === 'submit') ? 'pending' : 'draft';

        if (!empty($title) && !empty($categoryId) && !empty($content)) {
            
            $imagePath = $post['image']; // Keep old
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array(strtolower($ext), $allowed)) {
                    $filename = uniqid() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../../assets/uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        // Delete old image
                        if ($post['image'] && file_exists(__DIR__ . '/../../' . $post['image'])) {
                            unlink(__DIR__ . '/../../' . $post['image']);
                        }
                        $imagePath = 'assets/uploads/' . $filename;
                    }
                }
            }

            // Update
            $stmt = $pdo->prepare("UPDATE posts SET title=?, short_description=?, content=?, image=?, category_id=?, status=?, admin_message=NULL WHERE id=?");
            if ($stmt->execute([$title, $shortDesc, $content, $imagePath, $categoryId, $status, $id])) {
                $msg = ($status === 'pending') ? "খবরটি পর্যালোচনার জন্য পাঠানো হয়েছে।" : "খবরটি খসড়া হিসেবে সংরক্ষিত হয়েছে।";
                setFlash($msg, 'success');
                redirect('/reporter/articles.php');
            } else {
                setFlash("আপডেট করতে সমস্যা হয়েছে।", 'danger');
            }
        } else {
            setFlash("শিরোনাম, ক্যাটাগরি এবং বিস্তারিত খবর আবশ্যক।", 'warning');
        }
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">খবর সম্পাদনা</h1>
        <a href="articles.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
    </div>

    <?php displayFlash(); ?>

    <?php if ($post['status'] === 'rejected' && !empty($post['admin_message'])): ?>
    <div class="alert alert-danger shadow-sm mb-4">
        <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>অ্যাডমিন এই খবরটি বাতিল করেছেন</h5>
        <p class="mb-0 mt-2"><strong>কারণ:</strong> <?php echo nl2br(h($post['admin_message'])); ?></p>
        <hr>
        <p class="mb-0 small">আপনি খবরটি সংশোধন করে পুনরায় পর্যালোচনার জন্য জমা দিতে পারবেন।</p>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
                
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">শিরোনাম <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg fs-5" required value="<?php echo h($post['title']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">সারসংক্ষেপ</label>
                            <textarea name="short_description" class="form-control" rows="2"><?php echo h($post['short_description']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">বিস্তারিত খবর <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="12" required><?php echo h($post['content']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded mb-3">
                            <label class="form-label fw-semibold">ক্যাটাগরি <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $post['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo h($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="bg-light p-3 rounded mb-4">
                            <label class="form-label fw-semibold">ছবি</label>
                            <?php if ($post['image']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo SITE_URL . '/' . $post['image']; ?>" class="img-fluid rounded border" alt="Current Image">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control mb-2" accept="image/jpeg, image/png, image/webp">
                            <small class="text-muted d-block">নতুন ছবি দিলে আগেরটি মুছে যাবে।</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="submit" class="btn btn-danger btn-lg fw-bold">
                                <i class="bi bi-send"></i> পর্যালোচনার জন্য পাঠান
                            </button>
                            <button type="submit" name="action" value="draft" class="btn btn-outline-secondary fw-bold">
                                <i class="bi bi-save"></i> পরিবর্তন সেভ করুন
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
