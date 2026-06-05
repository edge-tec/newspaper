// Custom JS for Modern News

document.addEventListener("DOMContentLoaded", function() {
    // Add logic for lazy loading images if needed (though browser native loading="lazy" is often sufficient now)
    
    // Focus search input when modal opens
    var searchModal = document.getElementById('searchModal');
    if (searchModal) {
        searchModal.addEventListener('shown.bs.modal', function () {
            var searchInput = searchModal.querySelector('input[name="q"]');
            if(searchInput) {
                searchInput.focus();
            }
        });
    }
});
