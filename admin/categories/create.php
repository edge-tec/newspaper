<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireAdminOrEditor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('নিরাপত্তা ত্রুটি (Invalid CSRF token)।', 'danger');
    } else {
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        if (empty($name)) {
            setFlash('ক্যাটাগরির নাম দেওয়া আবশ্যক।', 'danger');
        } else {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                setFlash('এই নামের ক্যাটাগরি ইতিমধ্যে রয়েছে। অন্য নাম দিন।', 'danger');
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, status) VALUES (?, ?, ?)");
                if ($stmt->execute([$name, $slug, $status])) {
                    setFlash('ক্যাটাগরি সফলভাবে তৈরি হয়েছে।', 'success');
                    redirect('/admin/categories/index.php');
                } else {
                    setFlash('ক্যাটাগরি তৈরি করতে সমস্যা হয়েছে।', 'danger');
                }
            }
        }
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">নতুন ক্যাটাগরি তৈরি করুন</h1>
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
                            <label class="form-label fw-semibold">ক্যাটাগরির নাম <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="যেমন: খেলাধুলা">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">অবস্থা</label>
                            <select class="form-select" name="status">
                                <option value="active">সক্রিয় (Active)</option>
                                <option value="inactive">নিষ্ক্রিয় (Inactive)</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-danger px-4 fw-bold">সংরক্ষণ করুন</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
