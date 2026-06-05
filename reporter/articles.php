<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$userId = $_SESSION['user_id'];
$status = $_GET['status'] ?? null;

$query = "SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id WHERE p.author_id = ?";
$params = [$userId];

if ($status && in_array($status, ['published', 'pending', 'draft', 'rejected'])) {
    $query .= " AND p.status = ?";
    $params[] = $status;
}
$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$pageTitles = [
    'draft' => 'খসড়া সমূহ',
    'pending' => 'অপেক্ষমান খবর',
    'published' => 'প্রকাশিত খবর',
    'rejected' => 'বাতিলকৃত খবর'
];
$title = $status ? ($pageTitles[$status] ?? 'আমার সকল খবর') : 'আমার সকল খবর';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold"><?php echo $title; ?></h1>
        <a href="create.php" class="btn btn-danger"><i class="bi bi-pencil-square"></i> নতুন লিখুন</a>
    </div>

    <?php displayFlash(); ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">ছবি</th>
                        <th>শিরোনাম ও ক্যাটাগরি</th>
                        <th>অবস্থা</th>
                        <th>পঠিত</th>
                        <th>তারিখ</th>
                        <th class="text-end px-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td class="px-4">
                                <img src="<?php echo $post['image'] ? SITE_URL . '/' . $post['image'] : 'https://via.placeholder.com/60x40/eee/999?text=N'; ?>" 
                                     alt="" class="rounded" style="width:60px; height:40px; object-fit:cover;">
                            </td>
                            <td>
                                <div class="fw-medium mb-1 lh-sm" style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?php if ($post['status'] == 'published'): ?>
                                        <a href="<?php echo SITE_URL; ?>/news/<?php echo h($post['slug']); ?>" target="_blank" class="text-dark text-decoration-none"><?php echo h($post['title']); ?></a>
                                    <?php else: ?>
                                        <?php echo h($post['title']); ?>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border"><?php echo h($post['category_name'] ?? 'ক্যাটাগরি নেই'); ?></span>
                            </td>
                            <td><?php echo getStatusBadge($post['status']); ?></td>
                            <td><?php echo en2bn($post['views']); ?></td>
                            <td class="text-muted small"><?php echo timeAgo($post['created_at']); ?></td>
                            <td class="text-end px-4">
                                <?php if (in_array($post['status'], ['draft', 'rejected'])): ?>
                                    <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> সম্পাদন</a>
                                <?php else: ?>
                                    <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="প্রকাশিত বা অপেক্ষমান খবর সম্পাদন করা যায় না">
                                        <button class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-pencil"></i> সম্পাদন</button>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">কোনো খবর পাওয়া যায়নি।</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function(){
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
