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
    const noResults = document.getElementById("noResults");

    let visibleCount = 0; // track visible cards

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
            visibleCount++; // increment if visible
        } else {
            // Hide card
            card.style.display = "none";
        }
    });

    // SHOW / HIDE "No Results"
    if (visibleCount === 0) {
        noResults.style.display = "block";
    } else {
        noResults.style.display = "none";
    }
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

/* AJAX SUBMISSION FOR REQUEST BLOOD FORM */
document.addEventListener("submit", function (e) {
    if (e.target && e.target.id === "ajaxRequestBloodForm") {
        e.preventDefault();
        console.warn("Handled by dashboard.php AJAX. This listener is intentionally inactive.");
    }
});

// Dashboard's JS
document.addEventListener("DOMContentLoaded", function () {
    var dynamicContent = document.getElementById("dynamic-content");
    if (!dynamicContent) return;

    function ajaxLoad(url, onLoaded) {
        dynamicContent.innerHTML = "<p id='rb-loading'>Loading…</p>";

        fetch(url)
            .then(r => r.text())
            .then(html => {
                dynamicContent.innerHTML = html;
                if (onLoaded) onLoaded();
            })
            .catch(() => {
                dynamicContent.innerHTML = "<p style='color:#ff4d4d;padding:2rem;text-align:center;'>Failed to load.</p>";
            });
    }

    // DASHBOARD 
    let originalDashboardHTML = dynamicContent.innerHTML;

    const dashboardLink = document.querySelector('a[href="dashboard.php"]');
    if (dashboardLink) {
        dashboardLink.addEventListener("click", function (e) {
            e.preventDefault();

            // Restore original dashboard dynamicContent.innerHTML = originalDashboardHTML;

            // Show search again const searchBox = document.querySelector(".search-box");
            if (searchBox) searchBox.style.display = "flex";
        });
    }

    // PROFILE 
    const profileLink = document.getElementById("sidebar-profile");
    if (profileLink) {
        profileLink.addEventListener("click", function (e) {
            e.preventDefault();

            document.querySelector(".search-box").style.display = "none";

            ajaxLoad("donor-dashboard.php", function () {
                if (typeof initDashboard === "function") {
                    initDashboard();
                }
            });
        });
    }

    // REQUEST BLOOD
    const requestBloodLink = document.getElementById("sidebar-request-blood");
    if (requestBloodLink) {
        requestBloodLink.addEventListener("click", function (e) {
            e.preventDefault();

            document.querySelector(".search-box").style.display = "none";

            ajaxLoad("../public/request_blood_action.php", function () {
                const form = document.getElementById("ajaxRequestBloodForm");
                if (!form) return;

                form.addEventListener("submit", function (e) {
                    e.preventDefault();

                    const btn = form.querySelector('button[type="submit"]');
                    btn.disabled = true;
                    btn.textContent = "Submitting…";

                    fetch("../public/request_blood_action.php", {
                        method: "POST",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: new FormData(form)
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                window.location.href = "../public/compatible_donors.php?request_id=" + data.request_id;
                            } else {
                                alert(data.error || "Error");
                                btn.disabled = false;
                                btn.textContent = "Submit Blood Request";
                            }
                        });
                });
            });
        });
    }

    // MY REQUESTS 
    const myRequestsLink = document.getElementById("sidebar-my-requests");
    if (myRequestsLink) {
        myRequestsLink.addEventListener("click", function (e) {
            e.preventDefault();

            document.querySelector(".search-box").style.display = "none";

            ajaxLoad("../user/my_requests.php");
        });
    }

    // DONATE BLOOD
    const donateLink = document.getElementById("sidebar-donate-blood");
    if (donateLink) {
        donateLink.addEventListener("click", function (e) {
            e.preventDefault();

            document.querySelector(".search-box").style.display = "none";

            ajaxLoad("donation-form.php", function () {
                const waitForForm = setInterval(() => {
                    const form = document.getElementById("donationForm");

                    if (form) {
                        clearInterval(waitForForm);

                        console.log("donationForm FOUND → initializing");

                        if (typeof initDonationForm === "function") {
                            initDonationForm();
                        }
                    } else {
                        console.warn("donationForm NOT FOUND → waiting for AJAX render...");
                    }
                }, 50);
            });
        });
    }
});

// Global Handlers for Injected My Requests HTML 
window.mrReload = function () {
    var dc = document.getElementById("dynamic-content");
    if (!dc) return;

    dc.innerHTML = "<p id='rb-loading'>Loading…</p>";

    fetch("../user/my_requests.php")
        .then(function (r) {
            return r.text();
        })
        .then(function (html) {
            dc.innerHTML = html;
        });
};

window.mrToggleEdit = function (id) {
    var f = document.getElementById("mr-edit-" + id);
    if (f) f.classList.toggle("open");
};

window.mrUpdate = function (id, action) {
    let label = action === "cancel" ? "cancel" : "mark this request as fulfilled";

    const ok = confirm("Are you sure you want to " + label + " your request ?");

    if (!ok) {
        return;
    }

    fetch("../user/my_requests.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            action: action,
            id: id
        })
    })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                let card = document.getElementById("mr-card-" + id);

                if (card) {
                    card.style.transition = "opacity .3s ease";
                    card.style.opacity = "0.4";
                }

                setTimeout(window.mrReload, 400);
            } else {
                alert(d.error || "Failed to update request.");
            }
        })
        .catch(() => {
            alert("Network error. Please try again.");
        });
};

window.mrSaveEdit = function (id) {
    var bg = document.getElementById("ef-bg-" + id).value;
    var ur = document.getElementById("ef-ur-" + id).value;
    var hn = document.getElementById("ef-hn-" + id).value;
    var loc = document.getElementById("ef-loc-" + id).value;
    var cn = document.getElementById("ef-cn-" + id).value;

    var ug = document.getElementById("ef-ug-" + id);

    if (!ug || !ug.value) {
        alert("Please select urgency level.");
        return;
    }

    var btn = document.querySelector("#mr-edit-" + id + " .mr-ef-save");

    if (btn) {
        btn.disabled = true;
        btn.textContent = "Saving…";
    }

    fetch("../user/my_requests.php", {
        method: "POST",
        body: new URLSearchParams({
            action: "edit",
            id: id,
            blood_group: bg,
            units_required: ur,
            hospital_name: hn,
            location: loc,
            contact_number: cn,
            urgency: ug.value
        })
    })
        .then(function (r) {
            return r.json();
        })
        .then(function (d) {
            if (d.success) {
                window.mrReload();
            } else {
                alert(d.error || "Failed to save.");
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = "Save Changes";
                }
            }
        })
        .catch(function () {
            alert("Network error. Please try again.");
            if (btn) {
                btn.disabled = false;
                btn.textContent = "Save Changes";
            }
        });
};

window.loadSection = async function (type) {
    const container = document.getElementById("profile-dynamic-content");

    if (!container) return;

    container.innerHTML = "<p id='rb-loading'>Loading…</p>";

    let url = "";

    if (type === "requests") url = "../user/my_requests.php";
    else if (type === "donations") url = "../user/my_donations.php";
    else if (type === "campaigns") url = "../user/my_campaigns.php";
    else {
        container.innerHTML = "<p style='color:red'>Invalid section</p>";
        return;
    }

    try {
        const res = await fetch(url);
        container.innerHTML = await res.text();
    } catch {
        container.innerHTML = "<p style='color:red'>Failed to load</p>";
    }
};

function toggleMrMenu(id) {
    const menu = document.getElementById("mr-menu-" + id);

    if (!menu) return;

    // close all other open menus first (optional but clean)
    document.querySelectorAll(".mr-menu").forEach(m => {
        if (m.id !== "mr-menu-" + id) {
            m.style.display = "none";
        }
    });

    // toggle current menu
    if (menu.style.display === "block") {
        menu.style.display = "none";
    } else {
        menu.style.display = "block";
    }
}

// Close menu when clicking outside
document.addEventListener("click", function (e) {
    if (!e.target.closest(".mr-menu-wrapper")) {
        document.querySelectorAll(".mr-menu").forEach(menu => {
            menu.style.display = "none";
        });
    }
});





// SAFE TOGGLE
function toggleDashboardJoinForm(id) {
    const form = document.getElementById("dashboard-join-form-" + id);
    if (!form) return;

    const isHidden = window.getComputedStyle(form).display === "none";
    form.style.display = isHidden ? "block" : "none";
}

// HANDLE JOIN FORM SUBMIT (FIXED + STABLE)
document.addEventListener("submit", function (e) {

    const form = e.target;
    if (!form.classList.contains("dashboard-join-form")) return;

    e.preventDefault();

    const idInput = form.querySelector("input[name='campaign_id']");
    const campaignId = idInput ? idInput.value : "";
    const campaignName = form.elements["campaign_name"]?.value?.trim() || "";
    const location = form.elements["location"]?.value?.trim() || "";

    const errorBox = form.querySelector(".dj-error");
    const successBox = form.querySelector(".dj-success");

    const phone = form.elements["phone"]?.value?.trim();

    const phoneRegex = /^98\d{8}$/;

    if (!phoneRegex.test(phone)) {
        if (errorBox) {
            errorBox.style.display = "block";
            errorBox.innerText = "Phone must be 10 digits and start with 98.";
        }
        return;
    }

    if (errorBox) {
        errorBox.style.display = "none";
        errorBox.innerText = "";
    }

    if (successBox) {
        successBox.style.display = "none";
        successBox.innerHTML = "";
    }

    if (campaignName.length === 0 || location.length === 0) {
        if (errorBox) {
            errorBox.style.display = "block";
            errorBox.innerText = "All fields are required.";
        }
        return;
    }

    fetch("../public/join_campaign_action.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            campaign_id: campaignId,
            campaign_name: campaignName,
            location: location
        })
    })
        .then(r => r.text())
        .then(result => {

            result = result.trim();

            let message = "";

            if (result === "success") {
                message = "You have successfully joined the campaign.";
            }
            else if (result === "already_joined") {
                message = `<p style="color:#f1c40f; margin:0;">
        You have already joined this campaign.
    </p>`;

            }
            else {
                if (errorBox) {
                    errorBox.style.display = "block";
                    errorBox.innerText = "Server error: " + result;
                }
                return;
            }

            if (successBox) {
                successBox.style.display = "block";
                successBox.innerHTML = `
    <div class="success-msg">${message}</div>
    <button type="button" class="ok-btn">OK</button>
`;
            }

        })
        .catch(err => {
            if (errorBox) {
                errorBox.style.display = "block";
                errorBox.innerText = "Network error";
            }
        });
});

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("ok-btn")) {
        const form = e.target.closest("form");
        if (!form) return;

        form.style.display = "none";
    }
});