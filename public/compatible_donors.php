<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$request_id = $_GET['request_id'] ?? null;

if (!$request_id) {
    die("Invalid request.");
}

// Fetch the request
$stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE id = :id AND user_id = :user_id");
$stmt->execute(['id' => $request_id, 'user_id' => $_SESSION['user_id']]);
$req = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$req) {
    die("Request not found or access denied.");
}

$needed_blood = $req['blood_group'];

// Map compatible blood groups
$compatibility = [
    'A+' => ['A+', 'A-', 'O+', 'O-'],
    'A-' => ['A-', 'O-'],
    'B+' => ['B+', 'B-', 'O+', 'O-'],
    'B-' => ['B-', 'O-'],
    'AB+' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
    'AB-' => ['AB-', 'A-', 'B-', 'O-'],
    'O+' => ['O+', 'O-'],
    'O-' => ['O-']
];

$compatible_groups = $compatibility[$needed_blood] ?? [$needed_blood];

// Create dynamic place holders for IN clause
$inQuery = implode(',', array_fill(0, count($compatible_groups), '?'));

// Fetch compatible donors (excluding the user themselves)
$sql = "SELECT id, name, location, email FROM users WHERE blood_group IN ($inQuery) AND id != ?";
$params = $compatible_groups;
$params[] = $_SESSION['user_id'];

$donorStmt = $pdo->prepare($sql);
$donorStmt->execute($params);
$donors = $donorStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compatible Donors — Vital Drop</title>
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
        .dashboard-link { display: inline-block; margin-top: 2rem; color: #fff; font-weight: 500; }
        .dashboard-link:hover { color: #a90000; }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="donors-container">
        <h2>Compatible Donors for <?php echo htmlspecialchars($needed_blood); ?></h2>
        <p style="color: #aaa; margin-bottom: 2rem;">Your blood request has been successfully created. We found <?php echo count($donors); ?> potential donor(s) near you.</p>

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
            <p>No compatible donors found at the moment. We will notify you if someone registers.</p>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="../auth/dashboard.php" class="dashboard-link">&larr; Go to Dashboard</a>
        </div>
    </div>

</body>
</html>
