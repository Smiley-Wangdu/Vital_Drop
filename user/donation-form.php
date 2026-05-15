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
        AND (expires_at IS NULL OR expires_at > NOW())
        ORDER BY 
            CASE WHEN urgency = 'Urgent' THEN 0 ELSE 1 END,
            id DESC
    ");

    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $requests = [];
}
?>

<body data-page="donation-form">

    <main class="vd-main-content">

        <div class="vd-donation-panel">
            <!-- LEFT COLUMN: FORM -->
            <div class="vd-donation-main">
                <!-- ELIGIBILITY BOX -->
                <div id="eligibilityBox"></div>

                <div class="vd-form-card">
                    <h2 class="vd-form-title">Donation Form</h2>

                    <form id="donationForm" method="POST">
                        <div class="vd-form-grid">
                            <!-- BLOOD GROUP -->
                            <div class="vd-form-group full-width">
                                <label>Blood Group</label>
                                <div class="vd-blood-grid">
                                    <?php
                                    $groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
                                    foreach ($groups as $g):
                                        $active = ($g === $user_blood_group) ? 'vd-active' : '';
                                        ?>
                                        <button type="button" class="vd-blood-btn <?php echo $active; ?>"
                                            data-group="<?php echo $g; ?>" onclick="selectBlood('<?php echo $g; ?>')">
                                            <?php echo $g; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" id="bloodGroup" name="blood_group"
                                    value="<?php echo htmlspecialchars($user_blood_group); ?>">
                            </div>

                            <!-- NAME & PHONE -->
                            <div class="vd-form-group">
                                <label>Donor Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($donor_name); ?>" readonly>
                            </div>
                            <div class="vd-form-group">
                                <label>Phone</label>
                                <input type="tel" id="phone" name="phone" placeholder="98XXXXXXXX" required>
                            </div>

                            <!-- LOCATION & HOSPITAL -->
                            <div class="vd-form-group">
                                <label>Location</label>
                                <input type="text" id="location" name="location"
                                    value="<?php echo htmlspecialchars($user_location); ?>" placeholder="e.g. Kathmandu" required>
                            </div>
                            <div class="vd-form-group">
                                <label>Hospital</label>
                                <input type="text" id="hospital_name" name="hospital_name" placeholder="Target Hospital" required>
                            </div>

                            <!-- NOTES -->
                            <div class="vd-form-group full-width">
                                <label>Notes</label>
                                <textarea name="notes" placeholder="Any additional information..."></textarea>
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

            <!-- RIGHT COLUMN: SIDEBAR -->
            <div class="vd-donation-sidebar">
                <!-- ACTIVE REQUESTS -->
                <div class="vd-sidebar-card">
                    <h3 class="vd-sidebar-title">Active Blood Requests</h3>
                    <div class="vd-requests-scroll-vertical">
                        <?php if (!empty($requests)): ?>
                            <?php foreach (array_slice($requests, 0, 3) as $r): ?>
                                <div class="vd-request-card-mini">
                                    <div class="vd-req-header">
                                        <strong><?php echo htmlspecialchars($r['hospital_name']); ?></strong>
                                        <span class="vd-req-urgency"><?php echo htmlspecialchars($r['urgency']); ?></span>
                                    </div>
                                    <div class="vd-req-loc"><?php echo htmlspecialchars($r['location']); ?></div>
                                    <div class="vd-req-blood-wrap">
                                        <span class="vd-req-blood-type"><?php echo htmlspecialchars($r['blood_group']); ?></span>
                                        <button type="button" class="vd-btn-quick-donate" onclick="prefillBlood('<?php echo $r['blood_group']; ?>')">Donate Now</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="vd-no-req">No urgent requests currently.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- BLOOD COMPATIBILITY -->
                <div class="vd-sidebar-widget-card vd-compatibility-card">
                    <div class="vd-widget-header">
                        <h3 class="vd-section-title"><i class="fa-solid fa-arrows-to-circle"></i> Compatibility</h3>
                        <span class="vd-badge-pill"><?= htmlspecialchars($user_blood_group ?: 'O+') ?></span>
                    </div>
                    <div class="vd-compatibility-content">
                        <div class="vd-comp-row">
                            <span class="vd-comp-label">Can Donate To</span>
                            <div class="vd-comp-tags">
                                <?php 
                                    $bg = $user_blood_group ?: 'O+';
                                    $give = [];
                                    if ($bg == 'O+') $give = ['O+', 'A+', 'B+', 'AB+'];
                                    elseif ($bg == 'O-') $give = ['Everyone'];
                                    elseif ($bg == 'A+') $give = ['A+', 'AB+'];
                                    elseif ($bg == 'A-') $give = ['A+', 'A-', 'AB+', 'AB-'];
                                    elseif ($bg == 'B+') $give = ['B+', 'AB+'];
                                    elseif ($bg == 'B-') $give = ['B+', 'B-', 'AB+', 'AB-'];
                                    elseif ($bg == 'AB+') $give = ['AB+'];
                                    elseif ($bg == 'AB-') $give = ['AB+', 'AB-'];
                                    
                                    foreach($give as $t) echo "<span class='vd-tag vd-tag-give'>$t</span>";
                                ?>
                            </div>
                        </div>
                        <div class="vd-comp-divider"></div>
                        <div class="vd-comp-row">
                            <span class="vd-comp-label">Can Receive From</span>
                            <div class="vd-comp-tags">
                                <?php 
                                    $receive = [];
                                    if ($bg == 'O+') $receive = ['O+', 'O-'];
                                    elseif ($bg == 'O-') $receive = ['O-'];
                                    elseif ($bg == 'A+') $receive = ['A+', 'A-', 'O+', 'O-'];
                                    elseif ($bg == 'A-') $receive = ['A-', 'O-'];
                                    elseif ($bg == 'B+') $receive = ['B+', 'B-', 'O+', 'O-'];
                                    elseif ($bg == 'B-') $receive = ['B-', 'O-'];
                                    elseif ($bg == 'AB+') $receive = ['Everyone'];
                                    elseif ($bg == 'AB-') $receive = ['AB-', 'A-', 'B-', 'O-'];
                                    
                                    foreach($receive as $t) echo "<span class='vd-tag vd-tag-receive'>$t</span>";
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HEALTH TIPS -->
                <div class="vd-sidebar-widget-card vd-health-tips-card">
                    <h3 class="vd-section-title"><i class="fa-solid fa-heart-pulse"></i> Donor Health Tips</h3>
                    <div class="vd-tips-container">
                        <div class="vd-tip-card">
                            <div class="vd-tip-icon"><i class="fa-solid fa-glass-water"></i></div>
                            <div class="vd-tip-text">
                                <strong>Hydration</strong>
                                <p>Drink 500ml of water before donation.</p>
                            </div>
                        </div>
                        <div class="vd-tip-card">
                            <div class="vd-tip-icon"><i class="fa-solid fa-apple-whole"></i></div>
                            <div class="vd-tip-text">
                                <strong>Iron Boost</strong>
                                <p>Eat iron-rich foods like spinach.</p>
                            </div>
                        </div>
                        <div class="vd-tip-card">
                            <div class="vd-tip-icon"><i class="fa-solid fa-bed"></i></div>
                            <div class="vd-tip-text">
                                <strong>Rest Well</strong>
                                <p>Get 8 hours of sleep tonight.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/donor.js"></script>

    <?php /* CHATBOT WIDGET: Floating assistant — only renders for logged-in users */ ?>
    <?php include __DIR__ . '/../includes/chatbot_widget.php'; ?>
</body>