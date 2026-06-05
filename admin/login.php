<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (isLoggedIn()) {
    if (isReporter()) { redirect('/reporter/index.php'); }
    else { redirect('/admin/index.php'); }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = 'Your account is inactive. Please contact an admin.';
                } else {
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    if ($user['role'] === 'reporter') {
                        redirect('/reporter/index.php');
                    } else {
                        redirect('/admin/index.php');
                    }
                }
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="card shadow-sm border-0" style="max-width:400px; width:100%;">
        <div class="card-body p-4">
            <h2 class="text-center mb-1 fw-bold"><span class="text-danger">M</span>odern<span class="text-danger">N</span>ews</h2>
            <p class="text-center text-muted mb-4">Sign in to your account</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo h($error); ?></div>
            <?php endif; ?>
            <?php flashMessages(); ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-danger w-100">Login</button>
            </form>

            <p class="text-center mt-3 mb-0">New reporter? <a href="<?php echo SITE_URL; ?>/register.php" class="text-danger">Register here</a></p>
        </div>
    </div>
</body>
</html>
