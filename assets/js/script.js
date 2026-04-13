/* =====================================================
   VitalDrop - Main JavaScript File
   This file handles:
   1. Logout confirmation
   2. Sidebar open/close behaviour
   3. Filtering donor/campaign cards
   4. Show/Hide password functionality
===================================================== */


/* =====================================================
   1. LOGOUT CONFIRMATION
   -----------------------------------------------------
   When the logout button is clicked, ask the user
   if they really want to logout.
   If they click Cancel → stop logout
   If they click OK → redirect to logout.php
===================================================== */

// Get the logout button element
const logoutBtn = document.getElementById("logout");

// Some pages (like login/register) do not have a logout button,
// so we check if it exists before adding the event listener
if (logoutBtn) {

    logoutBtn.addEventListener("click", function(event) {

        // Show confirmation popup
        var confirmLogout = confirm("Are you sure you want to logout?");

        if (!confirmLogout) {

            // If user clicks "Cancel", prevent the logout
            event.preventDefault();

        } else {

            // If user clicks "OK", redirect to logout page
            window.location.href = "../auth/logout.php";

        }

    });

}



/* =====================================================
   2. SIDEBAR TOGGLE (OPEN / CLOSE)
   -----------------------------------------------------
   This controls the sidebar menu in the dashboard.
   - Clicking the menu icon opens/closes the sidebar
   - Clicking outside the sidebar closes it
===================================================== */

// Get elements
const toggle = document.getElementById("menuToggle"); // menu button
const sidebar = document.getElementById("sidebar");   // sidebar
const mainn = document.querySelector(".mainn");       // main content area

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



/* =====================================================
   3. FILTER CAMPAIGNS / DONORS
   -----------------------------------------------------
   Allows users to search and filter donor cards by:
   - donor name
   - blood group
   - location
===================================================== */

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



/* =====================================================
   4. PASSWORD SHOW / HIDE
   -----------------------------------------------------
   Allows users to click the eye icon to:
   - Show password
   - Hide password
===================================================== */

document.addEventListener("DOMContentLoaded", function(){

    // Select all eye icons used for password toggle
    const togglePasswords = document.querySelectorAll(".togglePassword");

    togglePasswords.forEach(icon => {

        icon.addEventListener("click", function(){

            // Get the input field before the icon
            const input = this.previousElementSibling;

            if(input.type === "password"){

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

/* =====================================================
   5. THEME TOGGLE (DARK / LIGHT)
   -----------------------------------------------------
===================================================== */
document.addEventListener("DOMContentLoaded", function() {
    const themeBtn = document.getElementById("theme-toggle");
    const body = document.body;
    
    if (themeBtn) {
        // Check local storage for theme preference
        const savedTheme = localStorage.getItem("vitaldrop_theme");
        if (savedTheme === "light") {
            body.classList.add("light-mode");
            themeBtn.checked = true;
        } else {
            // Default is dark
            body.classList.remove("light-mode");
            themeBtn.checked = false;
        }
        
        themeBtn.addEventListener("change", function() {
            if (themeBtn.checked) {
                body.classList.add("light-mode");
                localStorage.setItem("vitaldrop_theme", "light");
            } else {
                body.classList.remove("light-mode");
                localStorage.setItem("vitaldrop_theme", "dark");
            }
        });
    }
});
