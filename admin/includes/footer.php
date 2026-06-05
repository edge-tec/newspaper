            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple script to highlight active menu item based on current URL
        document.addEventListener("DOMContentLoaded", function() {
            var currentPath = window.location.pathname;
            var navLinks = document.querySelectorAll('#sidebarMenu .nav-link');
            navLinks.forEach(function(link) {
                if (link.getAttribute('href') && currentPath.indexOf(link.getAttribute('href')) !== -1) {
                    link.classList.add('active');
                } else if (currentPath === '/admin/' || currentPath === '/admin/index.php') {
                    if(link.getAttribute('href').endsWith('index.php') && !link.getAttribute('href').includes('posts') && !link.getAttribute('href').includes('categories') && !link.getAttribute('href').includes('users')) {
                        link.classList.add('active');
                    }
                }
            });
        });
    </script>
</body>
</html>
