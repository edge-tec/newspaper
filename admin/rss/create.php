<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdmin();

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
            // Check if feed URL already exists
            $stmt = $pdo->prepare("SELECT id FROM rss_sources WHERE feed_url = ?");
            $stmt->execute([$feed_url]);
            if ($stmt->fetch()) {
                setFlash('এই RSS ফিড ইউআরএল ইতিমধ্যে যুক্ত করা হয়েছে।', 'danger');
            } else {
                $stmt = $pdo->prepare("INSERT INTO rss_sources (source_name, feed_url, category_id, status) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$source_name, $feed_url, $category_id, $status])) {
                    setFlash('RSS উৎস সফলভাবে যুক্ত হয়েছে।', 'success');
                    redirect('/admin/rss/index.php');
                } else {
                    setFlash('RSS উৎস যুক্ত করতে সমস্যা হয়েছে।', 'danger');
                }
            }
        }
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">নতুন RSS উৎস যুক্ত করুন</h1>
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
                            <input type="text" class="form-control" name="source_name" required placeholder="যেমন: Prothom Alo, BBC Bangla">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">RSS ফিড ইউআরএল (URL) <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="feed_url" required placeholder="https://example.com/rss">
                            <div class="form-text">সঠিক XML/RSS ফিড লিংক প্রদান করুন।</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">ক্যাটাগরি <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo h($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">অবস্থা</label>
                                <select class="form-select" name="status">
                                    <option value="active">সক্রিয় (Active)</option>
                                    <option value="inactive">নিষ্ক্রিয় (Inactive)</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger px-4 fw-bold">সংরক্ষণ করুন</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
