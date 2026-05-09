<?php
require_once '../includes/session.php';
requireLogin();
require_once '../config/db.php';

/* HANDLE AJAX DONATION SUBMIT (FIXED) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    echo json_encode([
        "success" => false,
        "message" => "Use backend/donor/submit-donation.php for submission"
    ]);
    exit;
}

/* FETCH USER DETAILS */
$donor_name = '';
$user_blood_group = '';
$user_location = '';
$user_phone = '';

try {
    $stmt = $pdo->prepare("
        SELECT first_name, last_name, blood_group, location
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $donor_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $user_blood_group = $user['blood_group'] ?? '';
        $user_location = $user['location'] ?? '';
    }

} catch (PDOException $e) {
    error_log("User fetch error: " . $e->getMessage());
}

/* FETCH ACTIVE BLOOD REQUESTS */
try {
    $stmt = $pdo->prepare("
        SELECT hospital_name, blood_group, location, urgency
        FROM blood_requests
        WHERE status = 'Active'
        AND user_id != ?
        AND (expires_at IS NULL OR expires_at > NOW())
        ORDER BY 
            CASE WHEN urgency = 'Urgent' THEN 0 ELSE 1 END,
            id DESC
    ");

    $stmt->execute([$_SESSION['user_id']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $requests = [];
}
?>

<body data-page="donation-form">

    <main class="vd-main-content">

        <div class="tabs-content-wrapper vd-donate-wrapper">

            <div class="vd-page-title-wrap">
                <div class="vd-page-icon">+</div>
                <div>
                    <div class="vd-page-title">Donate Blood</div>
                    <div class="vd-page-subtitle">Help save lives by donating blood</div>
                </div>
            </div>

            <!-- ELIGIBILITY BOX -->
            <div id="eligibilityBox"></div>

            <!-- REQUESTS -->
            <div class="vd-blood-requests-bar">
                <div class="vd-bar-title vd-form-title">Active Blood Requests</div>

                <div class="vd-requests-scroll">

                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $r): ?>
                            <div class="vd-request-mini">

                                <div class="vd-request-mini-header">
                                    <span class="vd-hospital-name">
                                        <?php echo htmlspecialchars($r['hospital_name']); ?>
                                    </span>

                                    <span class="vd-badge vd-badge-urgent">
                                        <?php echo htmlspecialchars($r['urgency']); ?>
                                    </span>
                                </div>

                                <div class="vd-location">
                                    <?php echo htmlspecialchars($r['location']); ?>
                                </div>

                                <div class="vd-blood-type">
                                    <?php echo htmlspecialchars($r['blood_group']); ?>
                                </div>

                                <button type="button" class="vd-btn-donate-now"
                                    onclick="prefillBlood('<?php echo $r['blood_group']; ?>', '<?php echo addslashes($r['hospital_name']); ?>', '<?php echo addslashes($r['location']); ?>')">
                                    Donate Now
                                </button>

                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="vd-request-mini">
                            <p style="color:#aaa;">No active requests</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- FORM -->
            <div class="vd-form-card">

                <h3 class="vd-form-title">Donation Form</h3>

                <form id="donationForm" method="POST">

                    <div class="vd-form-grid">

                        <!-- BLOOD GROUP -->
                        <div class="vd-form-group full-width">
                            <label>Blood Group <span style="color: red;">*</span></label>

                            <div class="vd-blood-grid">
                                <?php
                                $groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
                                foreach ($groups as $g):
                                    $isMatch = ($g === $user_blood_group);
                                    $active  = $isMatch ? 'vd-active' : '';
                                    $disabled = $isMatch ? '' : 'disabled';
                                    ?>
                                    <button type="button"
                                        class="vd-blood-btn <?php echo $active; ?>"
                                        data-group="<?php echo $g; ?>"
                                        <?php echo $disabled; ?>
                                        title="<?php echo $isMatch ? 'Your registered blood group' : 'You can only donate your own blood group'; ?>">
                                        <?php echo $g; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <p class="vd-blood-group-note">
                                🔒 Blood group is locked to your registered profile.
                            </p>

                            <input type="hidden" id="bloodGroup" name="blood_group"
                                value="<?php echo htmlspecialchars($user_blood_group); ?>">
                        </div>

                        <!-- NAME -->
                        <div class="vd-form-group">
                            <label>Donor Name <span style="color: red;">*</span></label>
                            <input type="text" value="<?php echo htmlspecialchars($donor_name); ?>" readonly>
                        </div>

                        <!-- PHONE -->
                        <div class="vd-form-group">
                            <label>Phone <span style="color: red;">*</span></label>
                            <input type="tel" id="phone" name="phone" placeholder="Enter 10-digit number" required>
                        </div>

                        <!-- LOCATION -->
                        <div class="vd-form-group">
                            <label>Location <span style="color: red;">*</span></label>
                            <input type="text" id="location" name="location"
                                value="<?php echo htmlspecialchars($user_location); ?>" required>
                        </div>

                        <!-- HOSPITAL -->
                        <div class="vd-form-group">
                            <label>Hospital <span style="color: red;">*</span></label>
                            <input type="text" id="hospital_name" name="hospital_name" placeholder="Enter Hospital name" required>
                        </div>

                        <!-- NOTES -->
                        <div class="vd-form-group full-width">
                            <label>Notes</label>
                            <textarea name="notes"></textarea>
                        </div>

                    </div>

                    <button type="submit" id="submitBtn">Submit Donation</button>

                    <div id="warningBox" class="vd-error-box" style="display:none;"></div>

                    <div id="donationSuccessBox" class="vd-success-box" style="display:none;">
                        <p>Donation submitted successfully!</p>
                        <button type="button" id="okBtn" onclick="closeDonationMessage()">OK</button>
                    </div>

                </form>

            </div>

        </div>
    </main>

    <div id="vd-modal-overlay"></div>
    <script src="../assets/js/donor.js"></script>

</body>