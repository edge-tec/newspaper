<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isReporter()) redirect('/reporter/index.php');
    else redirect('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($email) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT id, name, password, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'inactive') {
                    $error = "আপনার একাউন্টটি নিষ্ক্রিয় করা হয়েছে। অ্যাডমিনের সাথে যোগাযোগ করুন।";
                } else {
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Route based on role
                    if ($user['role'] === 'reporter') {
                        redirect('/reporter/index.php');
                    } else {
                        redirect('/admin/index.php');
                    }
                }
            } else {
                $error = "ভুল ইমেইল বা পাসওয়ার্ড।";
            }
        } else {
            $error = "ইমেইল এবং পাসওয়ার্ড দিতে হবে।";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>লগইন — <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Noto+Serif+Bengali:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', 'Noto Serif Bengali', sans-serif; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-sm" style="width: 100%; max-width: 400px;">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-danger">লগইন</h2>
            <p class="text-muted">আপনার ড্যাশবোর্ডে প্রবেশ করুন</p>
        </div>

        <?php displayFlash(); ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
            <div class="mb-3">
                <label class="form-label fw-semibold">ইমেইল ঠিকানা</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">পাসওয়ার্ড</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold">লগইন করুন</button>
        </form>
        
        <div class="text-center mt-4 pt-3 border-top">
            <a href="<?php echo SITE_URL; ?>" class="text-muted text-decoration-none">← ওয়েবসাইটে ফিরে যান</a>
        </div>
    </div>
</div>

</body>
</html>
