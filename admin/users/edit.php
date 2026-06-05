<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied.';
    redirect('/admin/index.php');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    redirect('/admin/users/index.php');
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('/admin/users/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'reporter';

        if (empty($name) || empty($email)) {
            $_SESSION['error'] = 'Name and email are required.';
        } else {
            // Check if email exists for another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Email already exists for another user.';
            } else {
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?");
                    $params = [$name, $email, $hashedPassword, $role, $id];
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
                    $params = [$name, $email, $role, $id];
                }
                
                if ($stmt->execute($params)) {
                    $_SESSION['success'] = 'User updated successfully.';
                    redirect('/admin/users/index.php');
                } else {
                    $_SESSION['error'] = 'Failed to update user.';
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit User</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php" class="btn btn-sm btn-outline-secondary">Back to Users</a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo h($user['name']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo h($user['email']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
                <small class="text-muted">Leave blank to keep current password.</small>
            </div>
            
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="reporter" <?php echo $user['role'] === 'reporter' ? 'selected' : ''; ?>>Reporter</option>
                    <option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
