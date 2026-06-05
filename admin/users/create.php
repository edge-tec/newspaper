<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('নিরাপত্তা ত্রুটি (Invalid CSRF token)।', 'danger');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'reporter';
        $status = $_POST['status'] ?? 'active';

        if (empty($name) || empty($email) || empty($password)) {
            setFlash('নাম, ইমেইল এবং পাসওয়ার্ড দেওয়া আবশ্যক।', 'danger');
        } else {
            // Check email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                setFlash('এই ইমেইল দিয়ে ইতিমধ্যেই একাউন্ট আছে।', 'danger');
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $hash, $role, $status])) {
                    setFlash('ব্যবহারকারী সফলভাবে যুক্ত হয়েছে।', 'success');
                    redirect('/admin/users/index.php');
                } else {
                    setFlash('ব্যবহারকারী যুক্ত করতে সমস্যা হয়েছে।', 'danger');
                }
            }
        }
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">নতুন ব্যবহারকারী যুক্ত করুন</h1>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
    </div>

    <?php displayFlash(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo h(generateCsrfToken()); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">নাম <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="পুরো নাম">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ইমেইল <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required placeholder="email@example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">পাসওয়ার্ড <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">ভূমিকা (Role)</label>
                                <select class="form-select" name="role">
                                    <option value="reporter">রিপোর্টার</option>
                                    <option value="editor">এডিটর</option>
                                    <option value="admin">অ্যাডমিন</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">অবস্থা</label>
                                <select class="form-select" name="status">
                                    <option value="active">সক্রিয় (Active)</option>
                                    <option value="inactive">নিষ্ক্রিয় (Inactive)</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger px-4 fw-bold">সংরক্ষণ করুন</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
