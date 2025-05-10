// script.js
$(document).ready(function() {
    // Load dashboard by default
    loadPage('dashboard');

    // Handle navigation clicks
    $('.nav-item').click(function(e) {
        e.preventDefault();
        $('.nav-item').removeClass('active');
        $(this).addClass('active');
        
        const page = $(this).data('page');
        loadPage(page);
    });

    function loadPage(page) {
        $.ajax({
            url: `pages/${page}.php`,
            success: function(response) {
                $('#content-area').html(response);
            },
            error: function() {
                $('#content-area').html('Error loading page');
            }
        });
    }
});