<?php
session_start();
require '../config/db.php';

$blood_group = $_POST['blood_group'] ?? '';
$email = $_POST['email'] ?? ''; // Currently unused for search, but available.

if (empty($blood_group)) {
    die("Please select a blood group.");
}

$stmt = $pdo->prepare("SELECT name, location, email FROM users WHERE blood_group = :bg");
$stmt->execute(['bg' => $blood_group]);
$donors = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Donors — Vital Drop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .donors-container {
            max-width: 800px;
            margin: 4rem auto;
            padding: 2rem;
            background: #111;
            border-radius: 12px;
            color: #fff;
            border: 1px solid #222;
        }
        .donor-card {
            background: #1a1a1a;
            border: 1px solid #333;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .donor-info h3 { margin: 0 0 0.5rem 0; color: #fff; }
        .donor-info p { margin: 0; color: #aaa; font-size: 0.9rem; }
        .contact-btn {
            background: #a90000;
            color: #fff;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }
        .contact-btn:hover { background: #d32f2f; }
        .back-link { display: inline-block; margin-top: 2rem; color: #fff; font-weight: 500; }
        .back-link:hover { color: #a90000; }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="donors-container">
        <h2>Donors available for <?php echo htmlspecialchars($blood_group); ?></h2>
        <p style="color: #aaa; margin-bottom: 2rem;">Found <?php echo count($donors); ?> potential donor(s).</p>

        <?php if(count($donors) > 0): ?>
            <?php foreach($donors as $donor): ?>
                <div class="donor-card">
                    <div class="donor-info">
                        <h3><?php echo htmlspecialchars($donor['name']); ?></h3>
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

    <script src="../js/animations.js"></script>
</body>
</html>
