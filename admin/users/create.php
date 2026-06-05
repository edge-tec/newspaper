<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied.';
    redirect('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'reporter';

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields.';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Email already exists.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $hashedPassword, $role])) {
                    $_SESSION['success'] = 'User created successfully.';
                    redirect('/admin/users/index.php');
                } else {
                    $_SESSION['error'] = 'Failed to create user.';
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add User</h1>
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
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="reporter">Reporter</option>
                    <option value="editor">Editor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Save User</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
