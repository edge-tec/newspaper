<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

if (isLoggedIn()) redirect('/admin/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($name) && !empty($email) && !empty($password)) {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                setFlash('এই ইমেইল দিয়ে ইতিমধ্যেই একটি একাউন্ট রয়েছে।', 'danger');
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'reporter')")->execute([$name, $email, $hash]);
                setFlash('রেজিস্ট্রেশন সফল হয়েছে! এখন লগইন করুন।', 'success');
                redirect('/admin/login.php');
            }
        } else {
            setFlash('সবগুলো ঘর পূরণ করা আবশ্যক।', 'danger');
        }
    }
}

$pageTitle = 'রিপোর্টার রেজিস্ট্রেশন';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-badge fs-1 text-danger"></i>
                        <h2 class="fw-bold mt-2" style="font-family:'Playfair Display',serif;">রিপোর্টার হোন</h2>
                        <p class="text-muted">আমাদের নিউজপোর্টালে খবর লিখতে রেজিস্ট্রেশন করুন</p>
                    </div>

                    <?php displayFlash(); ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">আপনার নাম</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">ইমেইল ঠিকানা</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">পাসওয়ার্ড</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">রেজিস্ট্রেশন করুন</button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted mb-0">আগে থেকেই একাউন্ট আছে? <a href="<?php echo SITE_URL; ?>/admin/login.php" class="text-danger fw-semibold text-decoration-none">লগইন করুন</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
