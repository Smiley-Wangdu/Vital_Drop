<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: campaigns.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = :id");
$stmt->execute(['id' => $id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$campaign) {
    $_SESSION['message'] = "Campaign updated successfully!";
$_SESSION['msgType'] = "success";
header("Location: campaigns.php");
exit;
}

$error = "";
$selectedGroups = array_map('trim', explode(',', $campaign['blood_groups']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $time_range = trim($_POST['time_range']);
    $hospital_name = trim($_POST['hospital_name']);
    $blood_groups = isset($_POST['blood_groups']) ? implode(', ', $_POST['blood_groups']) : '';
    if (empty($name) || empty($location) || empty($time_range) || empty($blood_groups)) {
    $error = "Please fill all required fields.";
} 
elseif (!preg_match('/^\d{1,2}:\d{2}\s*(AM|PM)\s*-\s*\d{1,2}:\d{2}\s*(AM|PM)$/i', $time_range)) {
    $error = "Use format: 10:00 AM - 4:00 PM";
} 
else {
    preg_match_all('/(\d{1,2}):(\d{2})/', $time_range, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        if ($match[1] > 12 || $match[2] > 59) {
            $error = "Invalid time values.";
            break;
        }
    }
}

if (empty($error)) {
    $stmt = $pdo->prepare("UPDATE campaigns 
        SET name=:name, location=:location, time_range=:time_range, 
            blood_groups=:blood_groups, hospital_name=:hospital_name 
        WHERE id=:id");

    $stmt->execute([
        'name' => $name,
        'location' => $location,
        'time_range' => $time_range,
        'blood_groups' => $blood_groups,
        'hospital_name' => $hospital_name,
        'id' => $id
    ]);

    $_SESSION['message'] = "Campaign updated successfully!";
    $_SESSION['msgType'] = "success";

    header("Location: campaigns.php");
    exit;
}



    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("UPDATE campaigns SET name=:name, location=:location, time_range=:time_range, blood_groups=:blood_groups, hospital_name=:hospital_name WHERE id=:id");
            $stmt->execute([
                'name' => $name,
                'location' => $location,
                'time_range' => $time_range,
                'blood_groups' => $blood_groups,
                'hospital_name' => $hospital_name,
                'id' => $id
            ]);
            header("Location: campaigns.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Campaign — Vital Drop Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
</head>
<body class="admin-body dark">

    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="create-campaign-wrapper">
                <div class="campaign-form-card">
                    <div class="form-card-header">
                        <div class="form-icon"><iconify-icon icon="mdi:pencil"></iconify-icon></div>
                        <div>
                            <h2>Edit Campaign</h2>
                            <p class="form-subtitle">Update campaign details.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" class="campaign-form">
                        <label class="form-label">Required Blood Group (Select Multiple)</label>
                        <div class="blood-group-grid">
                            <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg): ?>
                            <label class="blood-group-option">
                                <input type="checkbox" name="blood_groups[]" value="<?php echo $bg; ?>" <?php echo in_array($bg, $selectedGroups) ? 'checked' : ''; ?>>
                                <span class="bg-option-label"><?php echo $bg; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <label class="checkbox-label select-all-label">
                            <input type="checkbox" id="selectAll"> Select All
                        </label>

                        <label class="form-label">Campaign Name <span class="required">*</span></label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($campaign['name']); ?>" required>

                        <label class="form-label">Hospital Name <span class="optional">optional</span></label>
                        <input type="text" name="hospital_name" value="<?php echo htmlspecialchars($campaign['hospital_name']); ?>">

                        <label class="form-label">Location <span class="required">*</span></label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($campaign['location']); ?>" required>

                        <label class="form-label">Time <span class="required">*</span></label>
                        <input type="text" name="time_range" value="<?php echo htmlspecialchars($campaign['time_range']); ?>" required>

                        <div class="form-buttons">
                            <button type="submit" class="btn-submit-campaign">Update Campaign</button>
                            <a href="campaigns.php" class="btn-cancel">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="blood_groups[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
</body>
</html>