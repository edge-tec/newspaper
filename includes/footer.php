<footer class="bg-dark text-light pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 mb-3">
                <h4 class="fw-bold mb-3" style="font-family:'Noto Serif Bengali',serif;">মডার্ন <span class="text-danger">নিউজ</span></h4>
                <p class="text-white-50 small"><?php echo SITE_DESC; ?> আমরা আপনাকে দিচ্ছি দেশে এবং দেশের বাইরের সর্বশেষ ও বিশ্বাসযোগ্য খবর। আমাদের নিজস্ব রিপোর্টারদের পাশাপাশি অন্যান্য নির্ভরযোগ্য মাধ্যম থেকে খবর সংগ্রহ করা হয়।</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white-50 fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white-50 fs-5"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-white-50 fs-5"><i class="bi bi-youtube"></i></a>
                    <a href="#" class="text-white-50 fs-5"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-semibold mb-3 text-uppercase" style="font-size:14px;">প্রয়োজনীয় লিংক</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>" class="text-white-50 text-decoration-none">হোম</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">আমাদের সম্পর্কে</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">যোগাযোগ</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">গোপনীয়তা নীতি</a></li>
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>/register.php" class="text-white-50 text-decoration-none">রিপোর্টার হোন</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6 class="text-white fw-semibold mb-3 text-uppercase" style="font-size:14px;">ক্যাটাগরি</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($navCategories as $cat): ?>
                        <a href="<?php echo SITE_URL; ?>/category/<?php echo h($cat['slug']); ?>" class="badge bg-secondary bg-opacity-50 text-decoration-none py-2 px-3"><?php echo h($cat['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-3">
                <h6 class="text-white fw-semibold mb-3 text-uppercase" style="font-size:14px;">নিউজলেটার</h6>
                <p class="text-white-50 small">প্রতিদিনের বাছাই করা খবর ইমেইলে পেতে সাবস্ক্রাইব করুন।</p>
                <form class="d-flex gap-2">
                    <input type="email" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="আপনার ইমেইল">
                    <button type="button" class="btn btn-danger btn-sm flex-shrink-0">সাবস্ক্রাইব</button>
                </form>
            </div>
        </div>
        <div class="border-top border-secondary pt-3 mt-4 text-center">
            <p class="text-white-50 small mb-0">&copy; <?php echo en2bn(date('Y')); ?> <?php echo SITE_NAME; ?>. সর্বস্বত্ব সংরক্ষিত।</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
