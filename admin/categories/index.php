<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdminOrEditor();

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">ক্যাটাগরি সমূহ</h1>
        <a href="create.php" class="btn btn-danger"><i class="bi bi-plus-lg"></i> নতুন ক্যাটাগরি</a>
    </div>

    <?php displayFlash(); ?>
    <?php if (isset($_SESSION['success'])) { echo "<div class='alert alert-success'>".h($_SESSION['success'])."</div>"; unset($_SESSION['success']); } ?>
    <?php if (isset($_SESSION['error'])) { echo "<div class='alert alert-danger'>".h($_SESSION['error'])."</div>"; unset($_SESSION['error']); } ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">নাম</th>
                        <th>স্লাগ (Slug)</th>
                        <th>অবস্থা</th>
                        <th class="text-end px-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($categories) > 0): ?>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="px-4 fw-medium"><?php echo h($category['name']); ?></td>
                            <td class="text-muted"><?php echo h($category['slug']); ?></td>
                            <td><?php echo getStatusBadge($category['status']); ?></td>
                            <td class="text-end px-4">
                                <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?php echo SITE_URL; ?>/admin/categories/delete.php?id=<?php echo $category['id']; ?>')"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">কোনো ক্যাটাগরি পাওয়া যায়নি।</td></tr>
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
