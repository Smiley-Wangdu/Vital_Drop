<?php
session_start();
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // If not logged in, redirect to login
    if (!isset($_SESSION['user_id'])) {
        // You could store post data in session to restore after login, but for simplicity:
        $_SESSION['error_msg'] = "Please login to request blood.";
        header("Location: ../auth/login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $blood_group = $_POST['blood_group'] ?? '';
    $hospital_name = $_POST['hospital_name'] ?? '';
    $location = $_POST['location'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $units_required = $_POST['units_required'] ?? 1;
    $urgency = $_POST['urgency'] ?? 'Normal';

    if ($urgency === 'Emergency') {
        $urgency = 'Urgent';
    }

    // Validation
    if (empty($blood_group) || empty($hospital_name) || empty($location) || empty($contact_number)) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Please fill in all required fields.']);
            exit;
        }
        die("Please fill in all required fields.");
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO blood_requests 
            (user_id, hospital_name, blood_group, location, contact_number, units_required, urgency, expires_at) 
            VALUES (:user_id, :hospital_name, :blood_group, :location, :contact_number, :units_required, :urgency, DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");

        $stmt->execute([
            'user_id' => $user_id,
            'hospital_name' => $hospital_name,
            'blood_group' => $blood_group,
            'location' => $location,
            'contact_number' => $contact_number,
            'units_required' => $units_required,
            'urgency' => $urgency
        ]);

        $request_id = $pdo->lastInsertId();

        // Return JSON for AJAX requests (dashboard)
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'request_id' => $request_id]);
            exit;
        }

        // Normal redirect for non-AJAX
        header("Location: compatible_donors.php?request_id=" . $request_id);
        exit;

    } catch (PDOException $e) {
        die("Error creating request: " . $e->getMessage());
    }
} else {
    // Output HTML fragment for AJAX loading in dashboard
    ?>
    <div class="tabs-content-wrapper" id="request-blood-form-wrap">
        <div class="tab-header">
            <div class="icon-wrapper red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            </div>
            <div>
                <h2>Request Blood</h2>
                <p>Submit urgent blood requirement details</p>
            </div>
        </div>

        <form id="ajaxRequestBloodForm" class="contact-form" method="POST" action="../public/request_blood_action.php">
            <div class="form-group blood-type-group">
                <label>Required Blood Group</label>
                <div class="blood-type-grid">
                    <label class="blood-radio"><input type="radio" name="blood_group" value="A+" required><span>A+</span></label>
                    <label class="blood-radio"><input type="radio" name="blood_group" value="A-"><span>A-</span></label>
                    <label class="blood-radio"><input type="radio" name="blood_group" value="B+"><span>B+</span></label>
                    <label class="blood-radio"><input type="radio" name="blood_group" value="B-"><span>B-</span></label>
                    <label class="blood-radio"><input type="radio" name="blood_group" value="O+"><span>O+</span></label>
                    <label class="blood-radio"><input type="radio" name="blood_group" value="O-"><span>O-</span></label>
                    <label class="blood-radio"><input type="radio" name="blood_group" value="AB+"><span>AB+</span></label>
                    <label class="blood-radio"><input type="radio" name="blood_group" value="AB-"><span>AB-</span></label>
                </div>
            </div>

            <div class="form-group">
                <label>Hospital Name</label>
                <input type="text" name="hospital_name" placeholder="Enter hospital name" required>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="City, State or Full Address" required>
            </div>

            <div class="form-group">
                <label>Urgency Level</label>
                <div class="urgency-grid">
                    <label class="urgency-radio">
                        <input type="radio" name="urgency" value="Normal" checked>
                        <span class="urgency-btn">Normal</span>
                    </label>
                    <label class="urgency-radio">
                        <input type="radio" name="urgency" value="Emergency">
                        <span class="urgency-btn outline">Emergency</span>
                    </label>
                </div>
            </div>

            <div class="form-row-group" style="display: flex; gap: 1rem;">
                <div class="form-group" style="flex: 2;">
                    <label>Contact Number</label>
                    <input type="tel" name="contact_number" placeholder="9800000000" pattern="[0-9]{10}" maxlength="10" minlength="10"
                        title="Please enter a valid 10-digit phone number"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Number of Units</label>
                    <input type="number" name="units_required" min="1" value="1" placeholder="1" required>
                </div>
            </div>

            <button type="submit" class="submit-btn red-btn">Submit Blood Request</button>
        </form>
    </div>
    <?php
}
?>
