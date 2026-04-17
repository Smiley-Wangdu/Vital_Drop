<?php
/**
 * DONATION FORM — donation-form.php
 * UPDATED: Added required indicators (*) and validation attributes.
 */
require_once '../includes/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Blood — Vital Drop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/donor-style.css">
</head>
<body data-page="donation-form">
<div class="app-container">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <img src="../images/logo.png" alt="Vital Drop" class="logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="logo-fallback" style="display:none;">V</div>
                <span class="brand-text">VITAL DROP</span>
            </div>

            <div class="user-info">
                <div class="user-avatar">👤</div>
                <div class="user-name" id="userName">
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </div>
                <div class="availability">
                    <span>Available:</span>
                    <div class="toggle" id="availToggle" onclick="toggleAvailability()">
                        <div class="toggle-knob"></div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="donor-dashboard.php" class="nav-link">Profile</a></li>
            <li class="nav-item"><a href="request_blood.php" class="nav-link">Request Blood</a></li>
            <li class="nav-item"><a href="donation-form.php" class="nav-link donate-blood active">Donate Blood</a></li>
            <li class="nav-item"><a href="notifications.php" class="nav-link">Notifications</a></li>
            <li class="nav-item"><a href="#" class="nav-link">Theme</a></li>
        </ul>

        <div class="logout-wrap">
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <div class="header-left">
                <a href="donor-dashboard.php" class="back-arrow" aria-label="Back">&#8592;</a>
                <div class="page-title-wrap">
                    <div class="page-icon">+</div>
                    <div>
                        <div class="page-title">Donate Blood</div>
                        <div class="page-subtitle">Help save lives by donating blood</div>
                    </div>
                </div>
            </div>
            <div class="header-right">
                Hello, <span id="headerUserName"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>!
            </div>
        </header>

        <div class="form-page">
            <div class="blood-requests-bar">
                <div class="bar-title">Current Blood Requests</div>
                <div class="requests-scroll" id="bloodRequestsScroll">
                    <div class="request-mini">
                        <div class="request-mini-header">
                            <span class="hospital-name">City Hospital</span>
                            <span class="badge badge-urgent">Urgent</span>
                        </div>
                        <div class="location">Kathmandu</div>
                        <div class="blood-type">A+</div>
                        <button class="btn-donate-now" onclick="prefillBlood('A+')">Donate Now</button>
                    </div>
                </div>
            </div>

            <div class="warning-box" id="warningBox" style="display:none;"></div>

            <div class="form-card">
                <h3 class="form-title">Donation Form</h3>

                <form id="donationForm" onsubmit="submitDonation(event)" novalidate>
                    <div class="form-grid">

                        <div class="form-group full-width">
                            <label class="form-label">Blood Group <span style="color:red;">*</span></label>
                            <div class="blood-grid">
                                <?php
                                $groups = ['A+','A-','B+','B-','O+','O-','AB+','AB-'];
                                foreach ($groups as $g): ?>
                                <button type="button" class="blood-btn" data-group="<?php echo $g; ?>" onclick="selectBlood('<?php echo $g; ?>')">
                                    <?php echo $g; ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="bloodGroup" name="blood_group" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="donorName">Donor Name <span style="color:red;">*</span></label>
                            <input type="text" class="form-input" id="donorName" name="donor_name" required autocomplete="name">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number <span style="color:red;">*</span></label>
                            <input type="tel" class="form-input" id="phone" name="phone" placeholder="98XXXXXXXX" 
                                   required pattern="[0-9]{10}" maxlength="10">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="address">Address <span style="color:red;">*</span></label>
                            <input type="text" class="form-input" id="address" name="address" required autocomplete="street-address">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="hospital">Hospital Name <span style="color:red;">*</span></label>
                            <input type="text" class="form-input" id="hospital" name="hospital_name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="location">Location</label>
                            <input type="text" class="form-input" id="location" name="location">
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label" for="notes">Extra Details</label>
                            <textarea class="form-input" id="notes" name="notes" placeholder="Any additional information..."></textarea>
                        </div>

                    </div>
                    <button type="submit" class="submit-btn" id="submitBtn">Submit Donation</button>
                </form>
            </div>
        </div>
    </main>
</div>

<div class="modal-overlay" id="modal">
    <div class="modal">
        <span class="modal-icon" id="modalIcon">✓</span>
        <h3 class="modal-title" id="modalTitle">Title</h3>
        <p class="modal-text" id="modalText">Message</p>
        <button class="modal-btn" id="modalBtn">OK</button>
    </div>
</div>

<script src="../js/donor.js"></script>
<script src="../assets/js/script.js"></script>

</body>
</html>