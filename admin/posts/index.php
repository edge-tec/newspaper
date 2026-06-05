<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdminOrEditor();

$status = $_GET['status'] ?? null;
$query = "SELECT p.*, c.name as category_name, u.name as author_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.author_id = u.id";
$params = [];

if ($status && in_array($status, ['published', 'pending', 'draft', 'rejected'])) {
    $query .= " WHERE p.status = ?";
    $params[] = $status;
}
$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">সংবাদ সমূহ</h1>
        <a href="create.php" class="btn btn-danger"><i class="bi bi-pencil-square"></i> নতুন সংবাদ</a>
    </div>

    <?php displayFlash(); ?>
    <?php if (isset($_SESSION['success'])) { echo "<div class='alert alert-success'>".h($_SESSION['success'])."</div>"; unset($_SESSION['success']); } ?>
    <?php if (isset($_SESSION['error'])) { echo "<div class='alert alert-danger'>".h($_SESSION['error'])."</div>"; unset($_SESSION['error']); } ?>

    <!-- Filter Pills -->
    <ul class="nav nav-pills mb-4">
      <li class="nav-item">
        <a class="nav-link <?php echo !$status ? 'active bg-secondary' : 'text-secondary'; ?>" href="index.php">সকল</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $status=='published' ? 'active bg-success' : 'text-success'; ?>" href="index.php?status=published">প্রকাশিত</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $status=='pending' ? 'active bg-warning text-dark' : 'text-warning'; ?>" href="index.php?status=pending">অপেক্ষমান</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $status=='draft' ? 'active bg-secondary' : 'text-secondary'; ?>" href="index.php?status=draft">খসড়া</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $status=='rejected' ? 'active bg-danger' : 'text-danger'; ?>" href="index.php?status=rejected">বাতিল</a>
      </li>
    </ul>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ছবি</th>
                        <th>শিরোনাম</th>
                        <th>লেখক</th>
                        <th>অবস্থা</th>
                        <th>তারিখ</th>
                        <th class="text-end px-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td class="px-3">
                                <img src="<?php echo $post['image'] ? SITE_URL . '/' . $post['image'] : 'https://via.placeholder.com/60x40/eee/999?text=N'; ?>" 
                                     alt="" class="rounded" style="width:50px; height:40px; object-fit:cover;">
                            </td>
                            <td>
                                <div class="fw-medium mb-1 lh-sm" style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?php echo h($post['title']); ?>
                                </div>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border"><?php echo h($post['category_name'] ?? 'ক্যাটাগরি নেই'); ?></span>
                            </td>
                            <td><?php echo h($post['author_name']); ?></td>
                            <td><?php echo getStatusBadge($post['status']); ?></td>
                            <td class="text-muted small"><?php echo timeAgo($post['created_at']); ?></td>
                            <td class="text-end px-4">
                                <a href="<?php echo SITE_URL; ?>/admin/posts/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?php echo SITE_URL; ?>/admin/posts/delete.php?id=<?php echo $post['id']; ?>')"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">কোনো সংবাদ পাওয়া যায়নি।</td></tr>
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
