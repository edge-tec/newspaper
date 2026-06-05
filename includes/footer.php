<footer class="bg-dark text-light pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 mb-3">
                <h4 class="fw-bold mb-3" style="font-family:'Playfair Display',serif;"><span class="text-danger">M</span>odern<span class="text-danger">N</span>ews</h4>
                <p class="text-white-50 small"><?php echo SITE_DESC; ?> We provide the latest and most reliable news from around the world, combining original reporting with curated headlines from trusted sources.</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white-50 fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white-50 fs-5"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-white-50 fs-5"><i class="bi bi-youtube"></i></a>
                    <a href="#" class="text-white-50 fs-5"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-semibold mb-3 text-uppercase" style="font-size:13px;">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>" class="text-white-50 text-decoration-none">Home</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">About Us</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Contact</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Privacy Policy</a></li>
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>/register.php" class="text-white-50 text-decoration-none">Become a Reporter</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6 class="text-white fw-semibold mb-3 text-uppercase" style="font-size:13px;">Categories</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($navCategories as $cat): ?>
                        <a href="<?php echo SITE_URL; ?>/category/<?php echo h($cat['slug']); ?>" class="badge bg-secondary bg-opacity-50 text-decoration-none py-2 px-3"><?php echo h($cat['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-3">
                <h6 class="text-white fw-semibold mb-3 text-uppercase" style="font-size:13px;">Newsletter</h6>
                <p class="text-white-50 small">Stay updated with breaking news delivered to your inbox.</p>
                <form class="d-flex gap-2">
                    <input type="email" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Your email">
                    <button type="button" class="btn btn-danger btn-sm flex-shrink-0">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="border-top border-secondary pt-3 mt-4 text-center">
            <p class="text-white-50 small mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
