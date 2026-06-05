<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdmin();

$stmt = $pdo->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Helper for Role translate
function getRoleBadge($role) {
    switch ($role) {
        case 'admin': return '<span class="badge bg-danger">অ্যাডমিন</span>';
        case 'editor': return '<span class="badge bg-info text-dark">এডিটর</span>';
        case 'reporter': return '<span class="badge bg-primary">রিপোর্টার</span>';
        default: return '<span class="badge bg-secondary">'.h($role).'</span>';
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">ব্যবহারকারীগণ</h1>
        <a href="create.php" class="btn btn-danger"><i class="bi bi-person-plus"></i> নতুন যুক্ত করুন</a>
    </div>

    <?php displayFlash(); ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">নাম</th>
                        <th>ইমেইল</th>
                        <th>ভূমিকা (Role)</th>
                        <th>অবস্থা</th>
                        <th>যোগদানের তারিখ</th>
                        <th class="text-end px-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-4 fw-medium">
                                <i class="bi bi-person-circle text-secondary me-2"></i> <?php echo h($user['name']); ?>
                            </td>
                            <td class="text-muted"><?php echo h($user['email']); ?></td>
                            <td><?php echo getRoleBadge($user['role']); ?></td>
                            <td><?php echo getStatusBadge($user['status']); ?></td>
                            <td class="text-muted small"><?php echo timeAgo($user['created_at']); ?></td>
                            <td class="text-end px-4">
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?php echo SITE_URL; ?>/admin/users/delete.php?id=<?php echo $user['id']; ?>')"><i class="bi bi-trash"></i></button>
                                <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">কোনো ব্যবহারকারী পাওয়া যায়নি।</td></tr>
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
