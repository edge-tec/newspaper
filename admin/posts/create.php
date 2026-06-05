<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdminOrEditor();

$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('নিরাপত্তা ত্রুটি (Invalid CSRF token)।', 'danger');
    } else {
        $title = trim($_POST['title'] ?? '');
        $category_id = $_POST['category_id'] ?? '';
        $short_description = trim($_POST['short_description'] ?? '');
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'published';
        $breaking_news = isset($_POST['breaking_news']) ? 1 : 0;
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        if (empty($slug)) $slug = 'news-' . time();

        if (empty($title) || empty($category_id) || empty($content)) {
            setFlash('অনুগ্রহ করে আবশ্যকীয় তথ্যগুলো পূরণ করুন।', 'danger');
        } else {
            // Check if slug exists
            $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $slug .= '-' . time();
            }

            // Image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $fileName = uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                        $imagePath = 'assets/uploads/' . $fileName;
                    } else {
                        setFlash('ছবি আপলোড করতে সমস্যা হয়েছে।', 'danger');
                    }
                } else {
                    setFlash('শুধুমাত্র JPG, PNG, GIF, এবং WEBP ফাইল সাপোর্ট করে।', 'danger');
                }
            }

            if (!isset($_SESSION['flash'])) {
                $stmt = $pdo->prepare("INSERT INTO posts (title, slug, short_description, content, image, category_id, author_id, breaking_news, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$title, $slug, $short_description, $content, $imagePath, $_SESSION['user_id'], $category_id, $breaking_news, $status])) {
                    setFlash('সংবাদটি সফলভাবে যুক্ত হয়েছে।', 'success');
                    redirect('/admin/posts/index.php');
                } else {
                    setFlash('সংবাদ যুক্ত করতে সমস্যা হয়েছে।', 'danger');
                }
            }
        }
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">নতুন সংবাদ যুক্ত করুন</h1>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
    </div>

    <?php displayFlash(); ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
        
        <div class="row g-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">শিরোনাম <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">সারসংক্ষেপ</label>
                            <textarea name="short_description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">বিস্তারিত <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="15" required></textarea>
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
                            <select name="status" class="form-select">
                                <option value="published">প্রকাশিত</option>
                                <option value="draft">খসড়া</option>
                            </select>
                        </div>
                        <div class="mb-4 form-check">
                            <input type="checkbox" name="breaking_news" class="form-check-input" id="breaking" value="1">
                            <label class="form-check-label" for="breaking">ব্রেকিং নিউজ হিসেবে মার্ক করুন</label>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold">পাবলিশ করুন</button>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold">ক্যাটাগরি <span class="text-danger">*</span></div>
                    <div class="card-body">
                        <select name="category_id" class="form-select" required>
                            <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo h($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold">ছবি</div>
                    <div class="card-body">
                        <input type="file" name="image" class="form-control" accept="image/jpeg, image/png, image/webp">
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
