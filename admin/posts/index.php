<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$posts = $pdo->query("
    SELECT p.id, p.title, p.slug, p.status, p.views, p.created_at, c.name as category_name, u.name as author_name 
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN users u ON p.author_id = u.id 
    ORDER BY p.created_at DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Posts</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create.php" class="btn btn-sm btn-outline-primary">Add New Post</a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo h($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Title</th>
                <th scope="col">Category</th>
                <th scope="col">Author</th>
                <th scope="col">Views</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
                <td><?php echo $post['id']; ?></td>
                <td><?php echo h($post['title']); ?></td>
                <td><?php echo h($post['category_name']); ?></td>
                <td><?php echo h($post['author_name']); ?></td>
                <td><?php echo number_format($post['views']); ?></td>
                <td>
                    <?php if ($post['status'] == 'published'): ?>
                        <span class="badge bg-success">Published</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Draft</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?php echo SITE_URL; ?>/news.php?slug=<?php echo h($post['slug']); ?>" class="btn btn-sm btn-info text-white" target="_blank">View</a>
                    <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="delete.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
