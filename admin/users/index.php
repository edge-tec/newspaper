<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied.';
    redirect('/admin/index.php');
}

$users = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Users</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create.php" class="btn btn-sm btn-outline-primary">Add New User</a>
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
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th scope="col">Date Registered</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo h($user['name']); ?></td>
                <td><?php echo h($user['email']); ?></td>
                <td>
                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'editor' ? 'warning text-dark' : 'info text-dark'); ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </td>
                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
