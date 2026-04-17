/* LOGOUT CONFIRMATION */

const logoutBtn = document.getElementById("logout");
const modal = document.getElementById("logoutModal");
const cancelBtn = document.getElementById("cancelLogout");
const confirmBtn = document.getElementById("confirmLogout");

if (logoutBtn) {

    logoutBtn.addEventListener("click", function (event) {
        event.preventDefault(); // stop default action
        modal.style.display = "flex"; // show popup
    });

    cancelBtn.addEventListener("click", function () {
        modal.style.display = "none"; // close popup
    });

    confirmBtn.addEventListener("click", function () {
        window.location.href = "../auth/logout.php"; // logout
    });

    // click outside modal closes it
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
}

/* SIDEBAR TOGGLE (OPEN / CLOSE) */

// Get elements
const toggle = document.getElementById("menuToggle"); // menu button
const sidebar = document.getElementById("sidebar"); // sidebar
const mainn = document.querySelector(".mainn"); // main content area

// Check elements exist before using them
if (toggle && sidebar && mainn) {

    // When menu icon is clicked
    toggle.addEventListener("click", (e) => {

        // Prevent click from triggering document click event
        e.stopPropagation();

        // Toggle sidebar visibility
        sidebar.classList.toggle("active");

        // Shift main content when sidebar opens
        mainn.classList.toggle("shift");

    });


    // Close sidebar when clicking outside
    document.addEventListener("click", (e) => {

        if (!sidebar.contains(e.target) && e.target !== toggle) {

            sidebar.classList.remove("active");
            mainn.classList.remove("shift");

        }

    });

}


/* FILTER CAMPAIGNS / DONORS */

// Get filter inputs
const donorInput = document.getElementById("donorSearch");
const bloodSelect = document.getElementById("bloodGroup");
const locationInput = document.getElementById("locationSearch");

// Get all donor/campaign cards
const cards = document.querySelectorAll(".card");


// Function to filter cards
function filterCards() {

    // Get user search values
    const donorValue = donorInput.value.toLowerCase();
    const bloodValue = bloodSelect.value;
    const locationValue = locationInput.value.toLowerCase();

    // Loop through each card
    cards.forEach(card => {

        // Get card information
        const title = card.querySelector("h3").textContent.toLowerCase();
        const location = card.querySelector("p").textContent.toLowerCase();

        // Blood group stored as data attribute in HTML
        const bloodGroup = card.getAttribute("data-blood") || "";

        // Check if card matches search filters
        if (
            (title.includes(donorValue) || donorValue === "") &&
            (location.includes(locationValue) || locationValue === "") &&
            (bloodGroup === bloodValue || bloodValue === "")
        ) {

            // Show card
            card.style.display = "block";

        } else {

            // Hide card
            card.style.display = "none";

        }

    });

}

// Trigger filtering when user types or selects filter
if (donorInput && bloodSelect && locationInput) {

    donorInput.addEventListener("input", filterCards);
    bloodSelect.addEventListener("change", filterCards);
    locationInput.addEventListener("input", filterCards);

}


/* PASSWORD SHOW / HIDE */

document.addEventListener("DOMContentLoaded", function () {

    // Select all eye icons used for password toggle
    const togglePasswords = document.querySelectorAll(".togglePassword");

    togglePasswords.forEach(icon => {

        icon.addEventListener("click", function () {

            // Get the input field before the icon
            const input = this.previousElementSibling;

            if (input.type === "password") {

                // Show password
                input.type = "text";

                // Change icon to eye-slash
                this.classList.remove("fa-eye");
                this.classList.add("fa-eye-slash");

            } else {

                // Hide password
                input.type = "password";

                // Change icon back to eye
                this.classList.remove("fa-eye-slash");
                this.classList.add("fa-eye");

            }

        });

    });

});

document.addEventListener("DOMContentLoaded", function () {

    const mainArea = document.querySelector(".mainn");
    const dashboardLink = document.querySelector('a[href="dashboard.php"]');

    // Save original dashboard HTML once
    const originalDashboard = mainArea.innerHTML;

    if (dashboardLink) {
        dashboardLink.addEventListener("click", function (e) {
            e.preventDefault();

            // Restore dashboard content only
            mainArea.innerHTML = originalDashboard;
        });
    }

});





/* AJAX REQUEST BLOOD FORM LOADING */
document.addEventListener("DOMContentLoaded", function () {
    const requestBloodLink = document.querySelector('a[href="request_blood.php"]');
    if (requestBloodLink) {
        requestBloodLink.addEventListener("click", function (e) {
            e.preventDefault();
            const mainnArea = document.querySelector(".mainn");

            // Revert active class from other sidebar links
            document.querySelectorAll(".sidebar ul li a").forEach(link => {
                link.classList.remove("active");
            });
            this.classList.add("active");

            if (mainnArea) {
                fetch("../public/request_blood_action.php")
                    .then(response => response.text())
                    .then(html => {
                        // Hide dashboard specific elements
                        const searchBox = mainnArea.querySelector(".search-box");
                        const campaignHead = document.getElementById("campaign");
                        const cardsWrapper = mainnArea.querySelector(".cards-wrapper");

                        if (searchBox) searchBox.style.display = "none";
                        if (campaignHead) campaignHead.style.display = "none";
                        if (cardsWrapper) cardsWrapper.style.display = "none";

                        // Remove old dynamic content if exists
                        let dynamicContent = document.getElementById("dynamicAjaxContent");
                        if (dynamicContent) {
                            dynamicContent.remove();
                        }

                        // Create wrapper for the new content to keep it organized
                        dynamicContent = document.createElement("div");
                        dynamicContent.id = "dynamicAjaxContent";
                        dynamicContent.innerHTML = html;

                        mainnArea.appendChild(dynamicContent);

                        // Execute scripts in injected HTML
                        const scripts = dynamicContent.querySelectorAll("script");
                        scripts.forEach(script => {
                            const newScript = document.createElement("script");
                            newScript.textContent = script.textContent;
                            document.body.appendChild(newScript);
                        });
                    })
                    .catch(err => {
                        console.error('Error loading request form:', err);
                    });
            }
        });
    }
});

/* AJAX SUBMISSION FOR REQUEST BLOOD FORM */
document.addEventListener("submit", function(e) {
    if (e.target && e.target.id === "ajaxRequestBloodForm") {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = "Submitting...";
        submitBtn.disabled = true;

        const formData = new FormData(form);

        fetch(form.action, {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('.donors-container');
            const dynamicContainer = document.getElementById("dynamicAjaxContent");
            
            if (newContent && dynamicContainer) {
                // Adjust margin so it fits perfectly in dashboard instead of having huge top margin
                newContent.style.margin = "2rem auto";
                
                // Get relevant styles
                let styleStr = "";
                doc.querySelectorAll("style").forEach(s => styleStr += s.outerHTML);
                
                dynamicContainer.innerHTML = styleStr + newContent.outerHTML;
            } else if (dynamicContainer) {
                // Fallback for error messages from die()
                dynamicContainer.innerHTML = `<div style="padding: 2rem; color: #ff4d4d; background: #1a1a1a; border-radius: 8px; margin: 2rem auto; max-width: 600px; text-align: center;">${html}</div>`;
            }
        })
        .catch(err => {
            console.error("Error submitting request:", err);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }
});