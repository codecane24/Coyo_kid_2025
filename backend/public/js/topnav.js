// Top Navigation Functionality
$(document).ready(function() {
    // Mobile menu toggle
    $('.mobile-toggle').click(function() {
        $(this).toggleClass('active');
        $('.sidebar-nav').toggleClass('show');

        // Animate hamburger icon
        if ($(this).hasClass('active')) {
            $('.toggle-icon').css('transform', 'rotate(45deg)');
            $('.toggle-icon::before').css('transform', 'rotate(90deg)');
            $('.toggle-icon::after').css('opacity', '0');
        } else {
            $('.toggle-icon').css('transform', 'rotate(0)');
            $('.toggle-icon::before').css('transform', 'rotate(0)');
            $('.toggle-icon::after').css('opacity', '1');
        }
    });

    // User Profile Dropdown
    $('.user-profile').click(function(e) {
        e.stopPropagation();
        $(this).find('.dropdown-menu').toggleClass('show');
    });

    // Close dropdown when clicking outside
    $(document).click(function() {
        $('.dropdown-menu').removeClass('show');
    });

    // Theme Toggle
    let isDark = localStorage.getItem('darkTheme') === 'true';

    function toggleTheme() {
        isDark = !isDark;
        localStorage.setItem('darkTheme', isDark);
        applyTheme();
    }

    function applyTheme() {
        if (isDark) {
            $('body').addClass('dark-theme');
            $('.theme-toggle i').removeClass('glyphicon-adjust').addClass('glyphicon-sun');
        } else {
            $('body').removeClass('dark-theme');
            $('.theme-toggle i').removeClass('glyphicon-sun').addClass('glyphicon-adjust');
        }
    }

    $('.theme-toggle').click(toggleTheme);
    applyTheme();

    // Cart Count Updates
    function updateCartCount() {
        $.ajax({
            url: '/user/cart-count',
            method: 'GET',
            success: function(response) {
                $('#cartCount').text(response.count || 0);
            },
            error: function() {
                console.log('Failed to fetch cart count');
            }
        });
    }

    // Needs Count Updates (for vendors)
    function updateNeedsCount() {
        if ($('#needCount').length) {
            $.ajax({
                url: '/user/needs-count',
                method: 'GET',
                success: function(response) {
                    $('#needCount').text(response.count || 0);
                },
                error: function() {
                    console.log('Failed to fetch needs count');
                }
            });
        }
    }

    // Initial load
    updateCartCount();
    updateNeedsCount();

    // Refresh counts every 30 seconds
    setInterval(function() {
        updateCartCount();
        updateNeedsCount();
    }, 30000);

    // Search Input Enhancement
    $('.search-input').on('focus', function() {
        $(this).closest('.search-wrapper').addClass('focused');
    }).on('blur', function() {
        $(this).closest('.search-wrapper').removeClass('focused');
    });
});
