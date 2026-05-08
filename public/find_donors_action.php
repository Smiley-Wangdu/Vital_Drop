<?php
session_start();
require '../config/db.php';

$blood_group = $_POST['blood_group'] ?? '';
$email = $_POST['email'] ?? ''; 

if (empty($blood_group)) {
    die("Please select a blood group.");
}

$stmt = $pdo->prepare("SELECT first_name, last_name, location, email FROM users WHERE blood_group = :bg");
$stmt->execute(['bg' => $blood_group]);
$donors = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Donors — Vital Drop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/contact.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="donors-container">
        <h2>Donors available for <?php echo htmlspecialchars($blood_group); ?></h2>
        <p style="color: #aaa; margin-bottom: 2rem;">Found <?php echo count($donors); ?> potential donor(s).</p>

        <?php if (count($donors) > 0): ?>
            <?php foreach ($donors as $donor): ?>
                <div class="donor-card">
                    <div class="donor-info">
                        <h3>
                            <?php echo htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']); ?>
                        </h3>
                        <p>Location: <?php echo htmlspecialchars($donor['location']); ?></p>
                    </div>
                    <div>
                        <a href="mailto:<?php echo htmlspecialchars($donor['email']); ?>" class="contact-btn">Contact Donor</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No donors found. Consider creating a <a href="contact.php" style="color:red">Blood Request</a>.</p>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="contact.php" class="back-link">&larr; Back to Search</a>
        </div>
    </div>

    <script src="../assets/js/animations.js"></script>
</body>

</html>