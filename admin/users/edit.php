<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdmin();

$id = $_GET['id'] ?? null;
if (!$id) redirect('/admin/users/index.php');

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) redirect('/admin/users/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('নিরাপত্তা ত্রুটি (Invalid CSRF token)।', 'danger');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'reporter';
        $status = $_POST['status'] ?? 'active';

        if (empty($name) || empty($email)) {
            setFlash('নাম ও ইমেইল দেওয়া আবশ্যক।', 'danger');
        } else {
            // Check if email belongs to someone else
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                setFlash('এই ইমেইল দিয়ে ইতিমধ্যেই অন্য একটি একাউন্ট আছে।', 'danger');
            } else {
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, role=?, status=? WHERE id=?");
                    $result = $stmt->execute([$name, $email, $hash, $role, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, status=? WHERE id=?");
                    $result = $stmt->execute([$name, $email, $role, $status, $id]);
                }

                if ($result) {
                    setFlash('ব্যবহারকারীর তথ্য সফলভাবে আপডেট হয়েছে।', 'success');
                    redirect('/admin/users/index.php');
                } else {
                    setFlash('আপডেট করতে সমস্যা হয়েছে।', 'danger');
                }
            }
        }
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">ব্যবহারকারী সম্পাদনা</h1>
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
                            <input type="text" class="form-control" name="name" value="<?php echo h($user['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ইমেইল <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="<?php echo h($user['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">নতুন পাসওয়ার্ড</label>
                            <input type="password" class="form-control" name="password">
                            <div class="form-text">পাসওয়ার্ড পরিবর্তন করতে না চাইলে এটি ফাঁকা রাখুন।</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">ভূমিকা (Role)</label>
                                <select class="form-select" name="role" <?php echo ($id == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                    <option value="reporter" <?php echo $user['role'] === 'reporter' ? 'selected' : ''; ?>>রিপোর্টার</option>
                                    <option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>এডিটর</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>অ্যাডমিন</option>
                                </select>
                                <?php if($id == $_SESSION['user_id']) echo '<input type="hidden" name="role" value="'.h($user['role']).'">'; ?>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">অবস্থা</label>
                                <select class="form-select" name="status" <?php echo ($id == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>সক্রিয় (Active)</option>
                                    <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>নিষ্ক্রিয় (Inactive)</option>
                                </select>
                                <?php if($id == $_SESSION['user_id']) echo '<input type="hidden" name="status" value="'.h($user['status']).'">'; ?>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4 fw-bold">আপডেট করুন</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
