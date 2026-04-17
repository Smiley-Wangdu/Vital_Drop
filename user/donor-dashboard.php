<?php
/**
 * DONOR DASHBOARD — donor-dashboard.php
 * PB2: Total donation count from donor_status.total_donations
 * PB3: Eligibility status (3-month check)
 * PB5: Warning banner with next eligible date
 *
 * NOTE: Total Received & Attended Campaigns are placeholders —
 *       those columns are owned by the Request & Campaign modules.
 *       They will call their own APIs and inject into these elements.
 */

require_once '../includes/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile — Vital Drop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/donor-style.css">
</head>
<body data-page="dashboard">
<div class="app-container">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <div class="sidebar-header">

            <!-- Brand -->
            <div class="brand">
                <img src="../images/logo.png" alt="Vital Drop"
                     class="logo-img"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="logo-fallback" style="display:none;">V</div>
                <span class="brand-text">VITAL DROP</span>
            </div>

            <!-- User -->
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
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
            </li>
            <li class="nav-item">
                <a href="donor-dashboard.php" class="nav-link active">Profile</a>
            </li>
            <li class="nav-item">
                <a href="request_blood.php" class="nav-link">Request Blood</a>
            </li>
            <li class="nav-item">
                <a href="donation-form.php" class="nav-link donate-blood">Donate Blood</a>
            </li>
            <li class="nav-item">
                <a href="notifications.php" class="nav-link">Notifications</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">Theme</a>
            </li>
        </ul>

        <div class="logout-wrap">
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <main class="main-content">

        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <a href="#" class="back-arrow" aria-label="Back">&#8592;</a>
                <div class="page-title-wrap">
                    <div class="page-icon">👤</div>
                    <div>
                        <div class="page-title">User Profile</div>
                    </div>
                </div>
            </div>
            <div class="header-right">
                Hello, <span id="headerUserName"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>!
            </div>
        </header>

        <!-- Page Content -->
        <div class="content">

            <!-- PB3 / PB5: Eligibility Status — rendered by JS -->
            <div id="eligibilityBox">
                <div class="spinner"></div>
            </div>

            <!-- Profile + Stats row -->
            <div class="profile-section">

                <!-- Profile Card -->
                <div class="profile-card">
                    <div class="profile-avatar">👤</div>
                    <div class="profile-name" id="profileName">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </div>
                    <div class="profile-blood" id="profileBlood">Blood Group: —</div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">

                    <!-- PB2: YOUR total donations (from donor_status table) -->
                    <div class="stat-card">
                        <div class="stat-label">Total Donations</div>
                        <!-- PB2: populated by donor.js → donorStatus.total_donations -->
                        <div class="stat-value" id="totalDonations">
                            <div class="spinner"></div>
                        </div>
                        <button class="stat-btn" onclick="window.location.href='donation-history.php'">
                            Donate History
                        </button>
                    </div>

                    <!--
                        INTEGRATION POINT — Total Received
                        Owner: Request Blood module developer
                        They should fetch their own count and set:
                            document.getElementById('totalReceived').textContent = count;
                    -->
                    <div class="stat-card">
                        <div class="stat-label">Total Received</div>
                        <div class="stat-value" id="totalReceived">—</div>
                        <button class="stat-btn" onclick="window.location.href='request_blood.php'">
                            Request History
                        </button>
                    </div>

                    <!--
                        INTEGRATION POINT — Attended Campaigns
                        Owner: Campaign module developer
                        They should fetch their own count and set:
                            document.getElementById('attendedCampaigns').textContent = count;
                    -->
                    <div class="stat-card">
                        <div class="stat-label">Attended Campaigns</div>
                        <div class="stat-value" id="attendedCampaigns">—</div>
                        <button class="stat-btn" onclick="window.location.href='campaigns.php'">
                            Campaign History
                        </button>
                    </div>

                </div><!-- /stats-grid -->
            </div><!-- /profile-section -->

            <!-- Request History Section -->
            <!-- INTEGRATION: The "Request Blood" developer populates #requestList -->
            <div class="section-header">
                <h3 class="section-title">Request History</h3>
                <a href="request_blood.php" class="btn">View All</a>
            </div>

            <div class="request-list" id="requestList">
                <!-- Placeholder — Request module developer replaces this -->
                <div class="request-card">
                    <div class="request-info">
                        <h4>Blood Group Required: A+</h4>
                        <div class="request-meta">
                            <span>Urgency:</span>
                            <span class="badge badge-urgent">Urgent</span>
                        </div>
                    </div>
                    <div class="request-actions">
                        <button class="btn-small btn-edit">Edit</button>
                        <button class="btn-small btn-delete">Delete</button>
                    </div>
                </div>
                <div class="request-card">
                    <div class="request-info">
                        <h4>Blood Group Required: A+</h4>
                        <div class="request-meta">
                            <span>Urgency:</span>
                            <span class="badge badge-urgent">Urgent</span>
                        </div>
                    </div>
                    <div class="request-actions">
                        <button class="btn-small btn-edit">Edit</button>
                        <button class="btn-small btn-delete">Delete</button>
                    </div>
                </div>
                <div class="request-card">
                    <div class="request-info">
                        <h4>Blood Group Required: A+</h4>
                        <div class="request-meta">
                            <span>Urgency:</span>
                            <span class="badge badge-urgent">Urgent</span>
                        </div>
                    </div>
                    <div class="request-actions">
                        <button class="btn-small btn-edit">Edit</button>
                        <button class="btn-small btn-delete">Delete</button>
                    </div>
                </div>
            </div>

        </div><!-- /content -->
    </main>
</div>

<!-- Modal -->
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