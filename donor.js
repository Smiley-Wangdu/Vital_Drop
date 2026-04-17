/**
 * VITAL DROP — donor.js
 * Fixed: initDashboard, checkFormEligibility, toggleAvailability, logout, prefillBlood
 */

"use strict";

let currentDonor = null;
let donorStatus = null;

document.addEventListener("DOMContentLoaded", () => {
  const page = document.body.dataset.page;
  if (page === "dashboard") initDashboard();
  if (page === "donation-form") initDonationForm();
});

/* ─────────────────────────────────────────
   DATA LOADER
───────────────────────────────────────── */
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

/* ─────────────────────────────────────────
   DASHBOARD INIT  (was missing — caused spinner to never resolve)
───────────────────────────────────────── */
async function initDashboard() {
  await loadDonorData();

  // Populate profile card
  if (currentDonor) {
    setText("profileName", currentDonor.full_name || "");
    setText(
      "profileBlood",
      "Blood Group: " + (currentDonor.blood_group || "—"),
    );
  }

  // PB2: Total donations counter
  const totalEl = document.getElementById("totalDonations");
  if (totalEl) {
    totalEl.textContent = donorStatus
      ? (donorStatus.total_donations ?? 0)
      : "—";
  }

  // PB3 / PB5: Eligibility box
  renderEligibilityBox();

  // Availability toggle state
  syncToggle();
}

/* ─────────────────────────────────────────
   ELIGIBILITY BOX  (PB3 / PB5)
   Used on both dashboard and donation-form
───────────────────────────────────────── */
function renderEligibilityBox() {
  const box = document.getElementById("eligibilityBox");
  if (!box || !donorStatus) return;

  if (!donorStatus.last_donation_date) {
    box.innerHTML = `
      <div class="eligibility-box">
        <span style="font-size:22px;">✅</span>
        <div>
          <strong>Eligible to Donate</strong>
          <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
            No previous donations on record. You are a first-time donor.
          </div>
        </div>
      </div>`;
    return;
  }

  const today = new Date();
  const nextDate = new Date(donorStatus.next_eligible_date);
  const isEligible = today >= nextDate;

  if (isEligible) {
    box.innerHTML = `
      <div class="eligibility-box">
        <span style="font-size:22px;">✅</span>
        <div>
          <strong>Eligible to Donate</strong>
          <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
            Last donation: ${donorStatus.last_donation_date}
          </div>
        </div>
      </div>`;
  } else {
    const diff = Math.ceil((nextDate - today) / (1000 * 60 * 60 * 24));
    box.innerHTML = `
      <div class="eligibility-box warning">
        <span style="font-size:22px;">⚠️</span>
        <div>
          <strong style="color:var(--warning);">Not Yet Eligible</strong>
          <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
            You must wait 3 months between donations.
            Next eligible date: <strong style="color:var(--warning);">
              ${donorStatus.next_eligible_date}
            </strong> (${diff} day${diff !== 1 ? "s" : ""} remaining)
          </div>
        </div>
      </div>`;
  }
}

/* ─────────────────────────────────────────
   DONATION FORM INIT
───────────────────────────────────────── */
async function initDonationForm() {
  await loadDonorData();

  if (currentDonor) {
    setVal("donorName", currentDonor.full_name || "");
    setVal("phone", currentDonor.phone || "");
    setVal("address", currentDonor.address || "");
    if (currentDonor.blood_group) selectBlood(currentDonor.blood_group);
  }

  // PB5: show warning banner if not eligible
  checkFormEligibility();
}

/* ─────────────────────────────────────────
   ELIGIBILITY CHECK FOR DONATION FORM  (was missing — caused crash)
───────────────────────────────────────── */
function checkFormEligibility() {
  const warningBox = document.getElementById("warningBox");
  const submitBtn = document.getElementById("submitBtn");
  if (!warningBox || !donorStatus) return;

  if (!donorStatus.last_donation_date) return; // first-time donor, always eligible

  const today = new Date();
  const nextDate = new Date(donorStatus.next_eligible_date);

  if (today < nextDate) {
    const diff = Math.ceil((nextDate - today) / (1000 * 60 * 60 * 24));
    warningBox.style.display = "flex";
    warningBox.innerHTML = `
      <span class="warning-icon">⚠️</span>
      <div class="warning-content">
        <h4>Donation Not Allowed Yet</h4>
        <p>You must wait at least 3 months between donations.</p>
        <div class="warning-date">
          Next eligible: ${donorStatus.next_eligible_date} (${diff} day${diff !== 1 ? "s" : ""} left)
        </div>
      </div>`;

    // Disable the submit button so the form cannot be submitted
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Not Eligible Yet";
    }
  }
}

/* ─────────────────────────────────────────
   BLOOD GROUP SELECTOR
───────────────────────────────────────── */
function selectBlood(group) {
  document.querySelectorAll(".blood-btn").forEach((btn) => {
    btn.classList.toggle("selected", btn.dataset.group === group);
  });
  setVal("bloodGroup", group);
}

// Pre-fill blood group from "Donate Now" buttons in the requests bar
function prefillBlood(group) {
  // was missing — caused JS error
  selectBlood(group);
  document.querySelector(".form-card")?.scrollIntoView({ behavior: "smooth" });
}

/* ─────────────────────────────────────────
   SUBMIT DONATION
───────────────────────────────────────── */
async function submitDonation(e) {
  e.preventDefault();

  const bloodGroup = getVal("bloodGroup");
  const phone = getVal("phone");
  const hospital = getVal("hospital"); // id="hospital" in the HTML
  const address = getVal("address");
  const phoneRegex = /^[0-9]{10}$/;

  if (!bloodGroup || !phone || !hospital || !address) {
    showModal(
      "error",
      "Missing Info",
      "Please fill in all required fields (*).",
      "OK",
      null,
    );
    return;
  }

  if (!phoneRegex.test(phone)) {
    showModal(
      "error",
      "Invalid Phone",
      "Phone number must be exactly 10 digits.",
      "OK",
      null,
    );
    return;
  }

  const btn = document.getElementById("submitBtn");
  btn.disabled = true;
  btn.textContent = "Processing…";

  const payload = {
    blood_group: bloodGroup,
    phone: phone,
    address: address,
    hospital_name: hospital,
    location: getVal("location"),
    notes: getVal("notes"),
  };

  try {
    const res = await fetch("../backend/donor/submit-donation.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const result = await res.json();

    if (result.success) {
      showModal(
        "success",
        "Success!",
        "Your donation has been submitted.",
        "Go to Dashboard",
        "donor-dashboard.php",
      );
    } else {
      showModal(
        "error",
        "Error",
        result.message || "Something went wrong.",
        "Try Again",
        null,
      );
      btn.disabled = false;
      btn.textContent = "Submit Donation";
    }
  } catch (err) {
    console.error("submitDonation error:", err);
    showModal(
      "error",
      "Network Error",
      "Could not reach the server. Please try again.",
      "OK",
      null,
    );
    btn.disabled = false;
    btn.textContent = "Submit Donation";
  }
}

/* ─────────────────────────────────────────
   AVAILABILITY TOGGLE  (was missing — caused JS error on click)
───────────────────────────────────────── */
async function toggleAvailability() {
  const toggle = document.getElementById("availToggle");
  if (!toggle) return;

  const isNowActive = !toggle.classList.contains("active");
  toggle.classList.toggle("active", isNowActive);

  try {
    await fetch("../backend/donor/update-availability.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ is_available: isNowActive ? 1 : 0 }),
    });
  } catch (err) {
    console.error("toggleAvailability failed:", err);
    // Revert toggle on failure
    toggle.classList.toggle("active", !isNowActive);
  }
}

// Sync toggle visual to loaded donor status
function syncToggle() {
  const toggle = document.getElementById("availToggle");
  if (toggle && donorStatus) {
    toggle.classList.toggle("active", !!donorStatus.is_available);
  }
}

/* ─────────────────────────────────────────
   LOGOUT  (was missing — caused JS error on click)
───────────────────────────────────────── */
function logout() {
  if (confirm("Are you sure you want to logout?")) {
    window.location.href = "../auth/logout.php";
  }
}

/* ─────────────────────────────────────────
   HELPERS
───────────────────────────────────────── */
function getVal(id) {
  return document.getElementById(id)?.value.trim() || "";
}

function setVal(id, val) {
  const el = document.getElementById(id);
  if (el) el.value = val;
}

function setText(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val;
}

function showModal(type, title, text, btnText, redirect) {
  const overlay = document.getElementById("modal");
  if (!overlay) return;

  const icon = type === "success" ? "✓" : "✕";
  const iconEl = document.getElementById("modalIcon");
  if (iconEl) {
    iconEl.textContent = icon;
    iconEl.style.color =
      type === "success" ? "var(--success)" : "var(--danger)";
  }

  document.getElementById("modalTitle").textContent = title;
  document.getElementById("modalText").textContent = text;

  const btn = document.getElementById("modalBtn");
  btn.textContent = btnText;
  btn.onclick = () => {
    overlay.classList.remove("active");
    if (redirect) window.location.href = redirect;
  };

  overlay.classList.add("active");
}
