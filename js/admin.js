/* ========================================
   VITAL DROP — ADMIN PANEL JS
   ======================================== */

// Toggle modal visibility
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
    }
}

// Close modal on backdrop click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});

// Close modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
    }
});

// Draw Pie Chart (pure Canvas — no library needed)
function drawPieChart(canvasId, donors, receivers) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const total = donors + receivers;
    
    if (total === 0) {
        // Draw empty state
        ctx.beginPath();
        ctx.arc(140, 140, 100, 0, Math.PI * 2);
        ctx.fillStyle = '#222';
        ctx.fill();
        ctx.fillStyle = '#666';
        ctx.font = '14px Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('No data', 140, 145);
        return;
    }

    const centerX = 140;
    const centerY = 140;
    const radius = 100;

    const slices = [
        { value: donors, color: '#cc0000', label: 'Donors' },
        { value: receivers, color: '#333', label: 'Receivers' }
    ];

    let startAngle = -Math.PI / 2; // Start from top

    // Animated draw
    let progress = 0;
    const animDuration = 1000;
    const startTime = performance.now();

    function animate(currentTime) {
        progress = Math.min((currentTime - startTime) / animDuration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // easeOut cubic

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        let currentAngle = -Math.PI / 2;

        slices.forEach((slice, i) => {
            const sliceAngle = (slice.value / total) * Math.PI * 2 * eased;

            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
            ctx.closePath();
            ctx.fillStyle = slice.color;
            ctx.fill();

            // Subtle border between slices
            ctx.strokeStyle = '#0a0a0a';
            ctx.lineWidth = 2;
            ctx.stroke();

            currentAngle += sliceAngle;
        });

        // Inner circle for donut effect
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius * 0.55, 0, Math.PI * 2);
        ctx.fillStyle = '#151515';
        ctx.fill();

        // Center text
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 24px Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(total, centerX, centerY - 6);
        ctx.fillStyle = '#888';
        ctx.font = '11px Inter, sans-serif';
        ctx.fillText('Total', centerX, centerY + 14);

        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }

    requestAnimationFrame(animate);
}

// Auto-dismiss alerts after 4 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'all 0.4s ease';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });

    // Animate bar fills on load
    const bars = document.querySelectorAll('.bar-fill');
    bars.forEach(bar => {
        const targetWidth = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = targetWidth;
        }, 300);
    });

    // Animate stat values (count up)
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(el => {
        const target = parseInt(el.textContent);
        if (isNaN(target)) return;
        
        let current = 0;
        const duration = 1000;
        const increment = target / (duration / 16);
        
        const counter = setInterval(() => {
            current += increment;
            if (current >= target) {
                el.textContent = target;
                clearInterval(counter);
            } else {
                el.textContent = Math.floor(current);
            }
        }, 16);
    });

    // Theme Toggle Logic
    const themeToggleBtn = document.getElementById('admin-theme-toggle');
    const themeDropdown = document.getElementById('theme-dropdown');
    const themeOptions = document.querySelectorAll('.theme-option');

    if (themeToggleBtn && themeDropdown) {
        // Toggle dropdown
        themeToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            themeDropdown.classList.toggle('open');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!themeDropdown.contains(e.target) && !themeToggleBtn.contains(e.target)) {
                themeDropdown.classList.remove('open');
            }
        });

        // Theme option click
        themeOptions.forEach(option => {
            option.addEventListener('click', function() {
                const theme = this.getAttribute('data-theme');
                applyTheme(theme);
                themeDropdown.classList.remove('open');
            });
        });
    }

    function applyTheme(theme) {
        if (theme === 'light') {
            document.body.classList.remove('dark');
            document.body.classList.add('light');
        } else {
            document.body.classList.remove('light');
            document.body.classList.add('dark');
        }
        localStorage.setItem('adminTheme', theme);

        // Update active state in dropdown
        themeOptions.forEach(opt => {
            if (opt.getAttribute('data-theme') === theme) {
                opt.classList.add('active-theme');
            } else {
                opt.classList.remove('active-theme');
            }
        });
    }

    // Initialize active state based on current theme
    const currentTheme = localStorage.getItem('adminTheme') || 'dark';
    themeOptions.forEach(opt => {
        if (opt.getAttribute('data-theme') === currentTheme) {
            opt.classList.add('active-theme');
        }
    });
});

// Immediately apply theme to prevent flash if possible (script is at bottom so it applies fast)
(function() {
    const savedTheme = localStorage.getItem('adminTheme') || 'dark';
    if (savedTheme === 'light') {
        document.body.classList.remove('dark');
        document.body.classList.add('light');
    } else {
        document.body.classList.remove('light');
        document.body.classList.add('dark');
    }
})();
