            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var currentPath = window.location.pathname + window.location.search;
            document.querySelectorAll('#sidebarMenu .nav-link').forEach(function(link) {
                var href = link.getAttribute('href');
                if (href && currentPath.indexOf(href.replace(window.location.origin, '')) !== -1) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
