<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdmin();

$stmt = $pdo->query("SELECT r.*, c.name as category_name FROM rss_feeds r LEFT JOIN categories c ON r.category_id = c.id ORDER BY r.source_name ASC");
$sources = $stmt->fetchAll();
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">RSS সংবাদ উৎস</h1>
        <div>
            <a href="create.php" class="btn btn-danger"><i class="bi bi-plus-lg"></i> নতুন উৎস যুক্ত করুন</a>
        </div>
    </div>

    <?php displayFlash(); ?>
    <?php if (isset($_SESSION['success'])) { echo "<div class='alert alert-success'>".h($_SESSION['success'])."</div>"; unset($_SESSION['success']); } ?>
    <?php if (isset($_SESSION['error'])) { echo "<div class='alert alert-danger'>".h($_SESSION['error'])."</div>"; unset($_SESSION['error']); } ?>

    <div class="alert alert-info shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1">অটোমেটিক খবর আনার জন্য Cron Job URL:</h6>
            <code class="fs-6 bg-light text-dark px-2 py-1 rounded border"><?php echo SITE_URL; ?>/cron/fetch_rss.php</code>
            <p class="mb-0 mt-1 small text-muted">আপনার সার্ভারের Cron Job সেটিংসে এই লিংকটি প্রতি ৩০ মিনিট বা ১ ঘণ্টা পরপর রান করার জন্য সেট করে দিন।</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">উৎস (Source)</th>
                        <th>ক্যাটাগরি</th>
                        <th>RSS ফিড ইউআরএল (URL)</th>
                        <th>অবস্থা</th>
                        <th class="text-end px-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($sources) > 0): ?>
                        <?php foreach ($sources as $source): ?>
                        <tr>
                            <td class="px-4 fw-medium"><?php echo h($source['source_name']); ?></td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border"><?php echo h($source['category_name'] ?? 'None'); ?></span></td>
                            <td>
                                <a href="<?php echo h($source['feed_url']); ?>" target="_blank" class="text-primary text-decoration-none small" style="word-break: break-all;">
                                    <?php echo h($source['feed_url']); ?>
                                </a>
                            </td>
                            <td><?php echo getStatusBadge($source['status']); ?></td>
                            <td class="text-end px-4">
                                <a href="edit.php?id=<?php echo $source['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?php echo SITE_URL; ?>/admin/rss/delete.php?id=<?php echo $source['id']; ?>')"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">কোনো RSS উৎস পাওয়া যায়নি।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(url) {
    Swal.fire({
        title: 'আপনি কি নিশ্চিত?',
        text: "ডিলিট করার পর এটি আর ফিরে পাওয়া যাবে না!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'হ্যাঁ, ডিলিট করুন!',
        cancelButtonText: 'বাতিল'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
