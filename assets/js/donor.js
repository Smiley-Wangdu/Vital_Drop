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
    // Only fill phone if PHP didn't already pre-fill it from the DB
    const phoneInput = document.getElementById("phone");
    if (phoneInput && !phoneInput.value) {
      phoneInput.value = currentDonor.phone || "";
    }

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
        <div>
          <strong>Eligible to Donate</strong>
          <div>Last donation: ${donorStatus.last_donation_date}</div>
        </div>
      </div>`;
  } else {
    const diff = Math.ceil((nextDate - today) / (1000 * 60 * 60 * 24));

    box.innerHTML = `
      <div class="eligibility-box warning">
        <div>
          <strong>Not Eligible</strong>
          <div>
            Next eligible: 
            <span class="next-date">${formatDate(nextDate)}</span>
            (<span class="days-left">${diff} days left</span>)
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
    submitBtn.classList.add("vd-disabled-style");
    submitBtn.textContent = "Not Eligible";
  } else {
    submitBtn.classList.remove("vd-disabled-style");
    submitBtn.textContent = "Submit Donation";
  }
}

// BLOOD SELECT (locked to registered blood group)
function selectBlood(group) {
  const btn = document.querySelector(`.vd-blood-btn[data-group="${group}"]`);

  // Only allow selecting if button is not disabled (i.e., it is the user's blood group)
  if (!btn || btn.disabled) return;

  document.querySelectorAll(".vd-blood-btn").forEach(b => {
    b.classList.toggle("vd-active", b.dataset.group === group);
  });

  const hidden = document.getElementById("bloodGroup");
  if (hidden) hidden.value = group;
}

// PREFILL BLOOD — compatibility check and auto-fill
function prefillBlood(requestedGroup, hospital, location) {
  if (!currentDonor) {
    showError("Please wait for profile data to load...");
    return;
  }

  const donorGroup = currentDonor.blood_group;

  // COMPATIBILITY CHECK
  // O- can donate to everyone. Others must match exactly (as per user restriction).
  const isCompatible = (donorGroup === "O-") || (donorGroup === requestedGroup);

  if (!isCompatible) {
    showError(`Your blood group (<strong>${donorGroup}</strong>) is not compatible with the requested group (<strong>${requestedGroup}</strong>).`);
    return;
  }

  // Clear previous errors
  clearError();

  // PREFILL
  setVal("hospital_name", hospital);
  setVal("location", location);

  // Success feedback
  showSuccess(`Compatible! Details for ${requestedGroup} request pre-filled.`);

  // Scroll to form
  const form = document.getElementById("donationForm");
  if (form) form.scrollIntoView({ behavior: "smooth", block: "center" });
}

// ERROR / SUCCESS BOX
function showError(msg) {
  const successBox = document.getElementById("donationSuccessBox");
  const overlay = document.getElementById("vd-modal-overlay");

  if (successBox) {
    successBox.classList.add("error-theme");
    successBox.style.display = "block";
    if (overlay) overlay.style.display = "block";
    const msgP = successBox.querySelector("p");
    if (msgP) msgP.innerHTML = msg;
    return;
  }

  const box = document.getElementById("warningBox");
  if (!box) return;

  box.style.display = "block";
  box.style.borderColor = "#ff4d4d";
  box.style.color = "#ff4d4d";
  box.innerHTML = msg;
}

function showSuccess(msg) {
  const successBox = document.getElementById("donationSuccessBox");
  const overlay = document.getElementById("vd-modal-overlay");

  if (successBox) {
    successBox.classList.remove("error-theme");
    successBox.style.display = "block";
    if (overlay) overlay.style.display = "block";
    const msgP = successBox.querySelector("p");
    if (msgP) msgP.innerHTML = msg;
    return;
  }

  const box = document.getElementById("warningBox");
  if (box) {
    box.style.display = "block";
    box.style.borderColor = "#1db954";
    box.style.color = "#1db954";
    box.innerHTML = msg;

    // auto hide after success
    setTimeout(() => {
      box.style.display = "none";
    }, 3000);
  }
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

  // ELIGIBILITY GUARD
  if (donorStatus && donorStatus.next_eligible_date) {
    const today = new Date();
    const nextDate = new Date(donorStatus.next_eligible_date);
    if (today < nextDate) {
      return showError(`You are not eligible yet. Next eligible date: <strong>${donorStatus.next_eligible_date}</strong>`);
    }
  }

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

      const form = document.getElementById("donationForm");
      if (form) form.reset();

      // Update eligibility data without page refresh
      await loadDonorData();
      renderEligibilityBox();
      checkFormEligibility();
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

function closeDonationMessage() {
  const box = document.getElementById("donationSuccessBox");
  const overlay = document.getElementById("vd-modal-overlay");
  if (box) box.style.display = "none";
  if (overlay) overlay.style.display = "none";
}

// Update showSuccess to also show the modal if present
const originalShowSuccess = showSuccess;
showSuccess = function (msg) {
  originalShowSuccess(msg);
  const successBox = document.getElementById("donationSuccessBox");
  if (successBox) {
    successBox.style.display = "block";
    const msgP = successBox.querySelector("p");
    if (msgP) msgP.textContent = msg;
  }
};
