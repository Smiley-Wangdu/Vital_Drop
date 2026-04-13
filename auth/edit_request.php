<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$request_id = $_GET['id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'] ?? null;
    $blood_group = $_POST['blood_group'] ?? '';
    $hospital_name = $_POST['hospital_name'] ?? '';
    $location = $_POST['location'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $units_required = $_POST['units_required'] ?? 1;
    $urgency = $_POST['urgency'] ?? 'Normal';

    if ($request_id) {
        $stmt = $pdo->prepare("
            UPDATE blood_requests SET 
                hospital_name = :hospital_name,
                blood_group = :blood_group,
                location = :location,
                contact_number = :contact_number,
                units_required = :units_required,
                urgency = :urgency
            WHERE id = :id AND user_id = :user_id AND status = 'Active'
        ");
        
        $stmt->execute([
            'hospital_name' => $hospital_name,
            'blood_group' => $blood_group,
            'location' => $location,
            'contact_number' => $contact_number,
            'units_required' => $units_required,
            'urgency' => $urgency,
            'id' => $request_id,
            'user_id' => $_SESSION['user_id']
        ]);

        header("Location: dashboard.php");
        exit;
    }
}

if (!$request_id) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE id = :id AND user_id = :user_id AND status = 'Active'");
$stmt->execute(['id' => $request_id, 'user_id' => $_SESSION['user_id']]);
$req = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$req) {
    die("Request not found, already fulfilled, or access denied.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Request — Vital Drop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .edit-container { max-width: 600px; margin: 160px auto 80px; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        body.dark-mode .edit-container { background: #1e1e1e; box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
        .edit-container h2 { margin-bottom: 20px; color: #5a0000; }
        body.dark-mode .edit-container h2 { color: #ff4d4d; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        body.dark-mode .form-group input, body.dark-mode .form-group select { background: #333; border: 1px solid #444; color: #fff; }
        .btn { padding: 10px 20px; background: #a90000; color: #fff; text-decoration: none; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .btn:hover { background: #d32f2f; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="edit-container">
        <h2>Edit Blood Request</h2>
        <form method="POST" action="edit_request.php">
            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
            
            <div class="form-group">
                <label>Blood Group</label>
                <select name="blood_group" required>
                    <?php 
                    $bgs = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                    foreach($bgs as $bg) {
                        $sel = ($req['blood_group'] == $bg) ? 'selected' : '';
                        echo "<option value='$bg' $sel>$bg</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Hospital Name</label>
                <input type="text" name="hospital_name" value="<?php echo htmlspecialchars($req['hospital_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($req['location']); ?>" required>
            </div>

            <div class="form-group">
                <label>Contact Number</label>
                <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($req['contact_number']); ?>" required>
            </div>

            <div class="form-group">
                <label>Units Required</label>
                <input type="number" name="units_required" value="<?php echo htmlspecialchars($req['units_required']); ?>" min="1" required>
            </div>

            <div class="form-group">
                <label>Urgency Level</label>
                <select name="urgency" required>
                    <option value="Normal" <?php echo ($req['urgency'] == 'Normal') ? 'selected' : ''; ?>>Normal</option>
                    <option value="Emergency" <?php echo ($req['urgency'] == 'Emergency' || $req['urgency'] == 'Urgent') ? 'selected' : ''; ?>>Emergency</option>
                </select>
            </div>

            <button type="submit" class="btn">Save Changes</button>
            <a href="dashboard.php" style="margin-left: 10px; color: #666; text-decoration: none;">Cancel</a>
        </form>
    </div>
</body>
</html>
