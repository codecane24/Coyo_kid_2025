// Dashboard Animations and Interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations for stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.2}s`;
        card.classList.add('floating');
    });

    // Initialize progress bars with animation
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });

    // Quick action cards hover effect
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.action-icon').style.transform = 'rotate(0) scale(1.1)';
        });
        card.addEventListener('mouseleave', function() {
            this.querySelector('.action-icon').style.transform = 'rotate(-5deg) scale(1)';
        });
    });

    // Order rows hover effect
    const orderRows = document.querySelectorAll('.order-row');
    orderRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(10px)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
        });
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
            this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.05)';
        });
    });

    // Feature cards progress animation
    const featureCards = document.querySelectorAll('.feature-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBar = entry.target.querySelector('.progress-bar');
                if (progressBar) {
                    progressBar.style.width = progressBar.dataset.progress || '0%';
                }
            }
        });
    }, { threshold: 0.5 });

    featureCards.forEach(card => observer.observe(card));

    // Welcome section parallax effect
    const welcomeSection = document.querySelector('.welcome-section');
    if (welcomeSection) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            welcomeSection.style.backgroundPositionY = scrolled * 0.5 + 'px';
        });
    }

    // Animated counters for stats
    function animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = value.toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Initialize stat counters
    const statNumbers = document.querySelectorAll('.stat-info h3');
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        animateValue(stat, 0, finalValue, 2000);
    });

    // Add smooth scrolling to view-all button
    const viewAllBtn = document.querySelector('.view-all-btn');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const ordersSection = document.querySelector('.orders-section');
            ordersSection.scrollIntoView({ behavior: 'smooth' });
        });
    }

    // Add notification system
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Example usage of notifications
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.querySelector('i').classList.contains('fa-eye') ? 'View' : 'Download';
            showNotification(`${action} action initiated`);
        });
    });
});
