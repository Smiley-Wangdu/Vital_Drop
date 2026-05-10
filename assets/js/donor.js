"use strict";

// GLOBAL STATE
let currentDonor = null;
let donorStatus = null;

// BOOTSTRAP
document.addEventListener("DOMContentLoaded", () => {
  const page = document.body.dataset.page;

  if (page === "dashboard") initDashboard();
  if (page === "donation-form") initDonationForm();
});

// LOAD DATA
async function loadDonorData() {
  try {
    const res = await fetch("../backend/donor/get-donor.php");
    const data = await res.json();

    if (data.success) {
      currentDonor = data.donor;
      donorStatus = data.status;
    }
  } catch (err) {
    console.error("loadDonorData failed:", err);
  }
}

// INIT FORM
async function initDonationForm() {
  await loadDonorData();

  if (currentDonor) {
    setVal("phone", currentDonor.phone || "");

    // FIX: don't overwrite user input if already filled from PHP
    const locInput = document.getElementById("location");
    if (locInput && !locInput.value) {
      locInput.value = currentDonor.location || "";
    }

    if (currentDonor.blood_group) {
      selectBlood(currentDonor.blood_group);
    }
  }

  renderEligibilityBox();
  checkFormEligibility();

  const form = document.getElementById("donationForm");
  if (form) form.addEventListener("submit", submitDonation);
}

// ELIGIBILITY BOX
function renderEligibilityBox() {
  const box = document.getElementById("eligibilityBox");
  if (!box || !donorStatus) return;

  const today = new Date();
  const nextDate = new Date(donorStatus.next_eligible_date);

  const formatDate = d => d.toISOString().split("T")[0];

  if (!donorStatus.last_donation_date) {
    box.innerHTML = `
      <div class="eligibility-box success">
        <i class="fa-solid fa-circle-check"></i>
        <div>
          <strong>Eligible to Donate</strong>
          <div>You are a first-time donor.</div>
        </div>
      </div>`;
    return;
  }

  const isEligible = today >= nextDate;

  if (isEligible) {
    box.innerHTML = `
      <div class="eligibility-box success">
        <i class="fa-solid fa-circle-check"></i>
        <div>
          <strong>Eligible to Donate</strong>
          <div>Last donation: ${donorStatus.last_donation_date}</div>
        </div>
      </div>`;
  } else {
    const diff = Math.ceil((nextDate - today) / (1000 * 60 * 60 * 24));

    box.innerHTML = `
      <div class="eligibility-box warning">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
          <strong>Not Eligible</strong>
          <div>
            Your next eligibility date is 
            <span class="next-date">${formatDate(nextDate)}</span>
            — only <span class="days-left">${diff} days</span> remaining.
          </div>
        </div>
      </div>`;
  }
}

// FORM ELIGIBILITY
function checkFormEligibility() {
  const submitBtn = document.getElementById("submitBtn");
  if (!submitBtn) return;

  if (!donorStatus || !donorStatus.last_donation_date) {
    submitBtn.disabled = false;
    submitBtn.textContent = "Submit Donation";
    return;
  }

  const today = new Date();
  const nextDate = new Date(donorStatus.next_eligible_date);

  if (today < nextDate) {
    submitBtn.disabled = true;
    submitBtn.textContent = "Not Eligible";
  } else {
    submitBtn.disabled = false;
    submitBtn.textContent = "Submit Donation";
  }
}

// LOOD SELECT
function selectBlood(group) {
  document.querySelectorAll(".vd-blood-btn").forEach(btn => {
    btn.classList.toggle("vd-active", btn.dataset.group === group);
  });

  const hidden = document.getElementById("bloodGroup");
  if (hidden) hidden.value = group;
}

// ERROR / SUCCESS BOX
function showError(msg) {
  const box = document.getElementById("warningBox");
  if (!box) return;

  box.style.display = "block";
  box.style.borderColor = "#ff4d4d";
  box.style.color = "#ff4d4d";
  box.innerHTML = msg;
}

function showSuccess(msg) {
  const box = document.getElementById("warningBox");
  if (!box) return;

  box.style.display = "block";
  box.style.borderColor = "#1db954";
  box.style.color = "#1db954";
  box.innerHTML = msg;

  // auto hide after success
  setTimeout(() => {
    box.style.display = "none";
  }, 3000);
}

function clearError() {
  const box = document.getElementById("warningBox");
  if (!box) return;

  box.style.display = "none";
  box.innerHTML = "";
}

// VALIDATION
function isValidPhone(phone) {
  return /^[0-9]{10}$/.test(phone.trim());
}

// SUBMIT DONATION
async function submitDonation(e) {
  e.preventDefault();
  clearError();

  const btn = document.getElementById("submitBtn");

  const blood =
    document.getElementById("bloodGroup")?.value ||
    document.querySelector(".vd-blood-btn.vd-active")?.dataset.group ||
    "";

  const phone = getVal("phone");
  const location = getVal("location");
  const hospital_name = getVal("hospital_name");
  const notes = getVal("notes");

// VALIDATION

  if (!blood) return showError("Please select blood group");

  if (!isValidPhone(phone))
    return showError("Phone number must be exactly 10 digits");

  if (!location) return showError("Please enter location");

  if (!hospital_name) return showError("Please enter hospital name");

  //REQUEST

  btn.disabled = true;
  btn.textContent = "Processing...";

  try {
    const res = await fetch("../backend/donor/submit-donation.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        blood_group: blood,
        phone,
        location,
        hospital_name,
        notes
      })
    });

    const result = await res.json();

    if (result.success) {
      showSuccess("Donation submitted successfully");
      btn.textContent = "Submitted";
    } else {
      showError(result.message || "Something went wrong");
      btn.disabled = false;
      btn.textContent = "Submit Donation";
    }
  } catch (err) {
    showError("Network error. Try again.");
    btn.disabled = false;
    btn.textContent = "Submit Donation";
  }
}

// HELPERS
function getVal(id) {
  return document.getElementById(id)?.value.trim() || "";
}

function setVal(id, val) {
  const el = document.getElementById(id);
  if (el) el.value = val;
}




// INIT PROFILE SETTINGS
function initProfileSettings() {
    const form = document.getElementById("profileSettingsForm");
    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        const btn = document.getElementById("saveProfileBtn");
        const feedback = document.getElementById("settingsFeedback");

        if (!btn || !feedback) return;

        btn.disabled = true;
        btn.textContent = "Saving...";

        const formData = new FormData(this);

        fetch("edit_profile_action.php", {
            method: "POST",
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                if (data.status === "success") {
                    feedback.style.color = "#27ae60";
                    feedback.textContent = data.message;
                    setTimeout(() => {
                        closeSettingsModal();
                        window.location.reload(); 
                    }, 1000);
                } else {
                    feedback.style.color = "#c0392b";
                    feedback.textContent = data.message;
                    btn.disabled = false;
                    btn.textContent = "Save Changes";
                }
            })
            .catch(() => {
                feedback.style.color = "#c0392b";
                feedback.textContent = "Network error. Please try again.";
                btn.disabled = false;
                btn.textContent = "Save Changes";
            });
    });
}

// MODAL CONTROLS
function openSettingsModal() {
    const modal = document.getElementById("vd-settings-modal");
    const modalBody = document.getElementById("vd-modal-body");
    
    if (!modal || !modalBody) return;

    modal.style.display = "flex";
    modalBody.innerHTML = "<div class='vd-loader-container'><p id='rb-loading'>Loading settings...</p></div>";

    fetch("profile-settings.php")
        .then(r => r.text())
        .then(html => {
            modalBody.innerHTML = html;
            // Initialize form logic after content is injected
            initProfileSettings();
        })
        .catch(() => {
            modalBody.innerHTML = "<p style='color:red; padding:20px; text-align:center;'>Failed to load settings.</p>";
        });
}

function closeSettingsModal() {
    const modal = document.getElementById("vd-settings-modal");
    if (modal) {
        modal.style.display = "none";
    }
}

// Close modal when clicking outside
window.addEventListener("click", function(e) {
    const modal = document.getElementById("vd-settings-modal");
    if (e.target === modal) {
        closeSettingsModal();
    }
});

