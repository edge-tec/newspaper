<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdmin();

$id = $_GET['id'] ?? null;
if (!$id) redirect('/admin/rss/index.php');

$stmt = $pdo->prepare("SELECT * FROM rss_sources WHERE id = ?");
$stmt->execute([$id]);
$source = $stmt->fetch();

if (!$source) redirect('/admin/rss/index.php');

$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('নিরাপত্তা ত্রুটি (Invalid CSRF token)।', 'danger');
    } else {
        $source_name = trim($_POST['source_name'] ?? '');
        $feed_url = trim($_POST['feed_url'] ?? '');
        $category_id = $_POST['category_id'] ?? null;
        $status = $_POST['status'] ?? 'active';

        if (empty($source_name) || empty($feed_url) || empty($category_id)) {
            setFlash('সবগুলো ঘর পূরণ করা আবশ্যক।', 'danger');
        } else {
            $stmt = $pdo->prepare("SELECT id FROM rss_sources WHERE feed_url = ? AND id != ?");
            $stmt->execute([$feed_url, $id]);
            if ($stmt->fetch()) {
                setFlash('এই RSS ফিড ইউআরএল অন্য একটি উৎসে ব্যবহৃত হচ্ছে।', 'danger');
            } else {
                $stmt = $pdo->prepare("UPDATE rss_sources SET source_name = ?, feed_url = ?, category_id = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$source_name, $feed_url, $category_id, $status, $id])) {
                    setFlash('RSS উৎস সফলভাবে আপডেট হয়েছে।', 'success');
                    redirect('/admin/rss/index.php');
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
        <h1 class="h3 fw-bold">RSS উৎস সম্পাদনা</h1>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
    </div>

    <?php displayFlash(); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">উৎসের নাম (Source Name) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="source_name" value="<?php echo h($source['source_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">RSS ফিড ইউআরএল (URL) <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="feed_url" value="<?php echo h($source['feed_url']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">ক্যাটাগরি <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $source['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo h($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">অবস্থা</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo $source['status'] === 'active' ? 'selected' : ''; ?>>সক্রিয় (Active)</option>
                                    <option value="inactive" <?php echo $source['status'] === 'inactive' ? 'selected' : ''; ?>>নিষ্ক্রিয় (Inactive)</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4 fw-bold">আপডেট করুন</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
