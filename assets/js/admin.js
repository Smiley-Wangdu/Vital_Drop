/* VITAL DROP — ADMIN PANEL JS (FIXED) */

document.addEventListener("DOMContentLoaded", function () {

    initLogoutModal();
    initAlerts();
    initBars();
    initCounters();
    initTheme();

});

/* LOGOUT MODAL */
function initLogoutModal() {
    const logoutBtn = document.getElementById("logout");
    const modal = document.getElementById("logoutModal");
    const cancelBtn = document.getElementById("cancelLogout");
    const confirmBtn = document.getElementById("confirmLogout");

    if (!logoutBtn || !modal || !cancelBtn || !confirmBtn) return;

    logoutBtn.addEventListener("click", function (e) {
        e.preventDefault();
        modal.style.display = "flex";
    });

    cancelBtn.addEventListener("click", function () {
        modal.style.display = "none";
    });

    confirmBtn.addEventListener("click", function () {
        window.location.href = "../auth/logout.php";
    });

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
}

// MODAL UTILITY
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = modal.style.display === "none" ? "flex" : "none";
    }
}

// ALERT AUTO DISMISS
function initAlerts() {
    const alerts = document.querySelectorAll(".alert");

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = "0";
            alert.style.transform = "translateY(-10px)";
            alert.style.transition = "all 0.4s ease";
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });
}

// BAR ANIMATION
function initBars() {
    const bars = document.querySelectorAll(".bar-fill");

    bars.forEach(bar => {
        const targetWidth = bar.style.width;
        bar.style.width = "0%";

        setTimeout(() => {
            bar.style.width = targetWidth;
        }, 300);
    });
}

// COUNT UP ANIMATION
function initCounters() {
    const statValues = document.querySelectorAll(".stat-value");

    statValues.forEach(el => {
        const target = parseInt(el.textContent);

        if (isNaN(target)) return;

        let current = 0;
        const duration = 1000;
        const step = target / (duration / 16);

        const counter = setInterval(() => {
            current += step;

            if (current >= target) {
                el.textContent = target;
                clearInterval(counter);
            } else {
                el.textContent = Math.floor(current);
            }
        }, 16);
    });
}

// THEME SYSTEM (FIXED - NO DUPLICATE RUN)
function initTheme() {
    const themeToggleBtn = document.getElementById("admin-theme-toggle");
    const themeDropdown = document.getElementById("theme-dropdown");
    const themeOptions = document.querySelectorAll(".theme-option");

    applySavedTheme(); // apply once only

    if (themeToggleBtn && themeDropdown) {

        themeToggleBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            themeDropdown.classList.toggle("open");
        });

        document.addEventListener("click", function (e) {
            if (!themeDropdown.contains(e.target) && !themeToggleBtn.contains(e.target)) {
                themeDropdown.classList.remove("open");
            }
        });

        themeOptions.forEach(option => {
            option.addEventListener("click", function () {
                const theme = this.getAttribute("data-theme");
                applyTheme(theme);
                themeDropdown.classList.remove("open");
            });
        });
    }

    // set active state
    const currentTheme = localStorage.getItem("adminTheme") || "dark";

    themeOptions.forEach(opt => {
        opt.classList.toggle(
            "active-theme",
            opt.getAttribute("data-theme") === currentTheme
        );
    });
}

/* Apply theme */
function applyTheme(theme) {
    document.body.classList.toggle("light", theme === "light");
    document.body.classList.toggle("dark", theme !== "light");

    localStorage.setItem("adminTheme", theme);
}

/* Apply saved theme ONCE */
function applySavedTheme() {
    const saved = localStorage.getItem("adminTheme") || "dark";

    document.body.classList.toggle("light", saved === "light");
    document.body.classList.toggle("dark", saved !== "light");
}

// PIE CHART (SAFE FIX)
function drawPieChart(canvasId, donors, receivers, realTotal = null) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    donors = Number(donors) || 0;
    receivers = Number(receivers) || 0;

    const ctx = canvas.getContext("2d");
    const sum = donors + receivers;
    const centerDisplay = realTotal !== null ? realTotal : sum;

    const centerX = 140;
    const centerY = 140;
    const radius = 100;

    if (sum === 0) {
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.fillStyle = "#222";
        ctx.fill();

        ctx.fillStyle = "#666";
        ctx.font = "14px Inter, sans-serif";
        ctx.textAlign = "center";
        ctx.fillText("No data", centerX, centerY + 5);
        return;
    }

    const slices = [
        { value: donors, color: "#cc0000" },
        { value: receivers, color: "#333" }
    ];

    let start = -Math.PI / 2;
    const startTime = performance.now();
    const duration = 1000;

    function animate(time) {
        const progress = Math.min((time - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        let current = start;

        slices.forEach(slice => {
            const angle = (slice.value / sum) * Math.PI * 2 * eased;

            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, current, current + angle);
            ctx.closePath();
            ctx.fillStyle = slice.color;
            ctx.fill();

            ctx.strokeStyle = "#0a0a0a";
            ctx.lineWidth = 2;
            ctx.stroke();

            current += angle;
        });

        // inner circle
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius * 0.55, 0, Math.PI * 2);
        ctx.fillStyle = "#151515";
        ctx.fill();

        // text
        ctx.fillStyle = "#fff";
        ctx.font = "bold 24px Inter";
        ctx.textAlign = "center";
        ctx.fillText(centerDisplay, centerX, centerY - 5);

        ctx.fillStyle = "#888";
        ctx.font = "12px Inter";
        ctx.fillText("Users", centerX, centerY + 15);

        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }

    requestAnimationFrame(animate);
}