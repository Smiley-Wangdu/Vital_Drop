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
$is_urgent = $req['urgency'] === 'Urgent';

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
$sql = "SELECT id, first_name, last_name, location, email FROM users WHERE blood_group IN ($inQuery) AND id != ?";
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/contact.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="donors-container">
        <?php if ($is_urgent): ?>
            <div
                style="background:#4d0000;border:1px solid #a90000;border-radius:8px;padding:1rem 1.5rem;margin-bottom:1.5rem;animation:pulse-brd 1.8s ease-in-out infinite">
                <strong style="color:#ff4d4d;font-size:1rem;display:block;margin-bottom:.25rem">Emergency Request —
                    Prioritized</strong>
                <span style="color:#ffb3b3;font-size:.85rem">This request is marked as Emergency. Please contact a
                    compatible donor as soon as possible.</span>
            </div>
            <style>
                @keyframes pulse-brd {

                    0%,
                    100% {
                        box-shadow: 0 0 0 0 rgba(169, 0, 0, .5)
                    }

                    50% {
                        box-shadow: 0 0 0 8px rgba(169, 0, 0, 0)
                    }
                }
            </style>
        <?php endif; ?>
        <h2>Compatible Donors for <?php echo htmlspecialchars($needed_blood); ?></h2>
        <p style="color: #aaa; margin-bottom: 2rem;">Your blood request has been successfully created. We found
            <?php echo count($donors); ?> potential donor(s) near you.</p>

        <?php if (isset($_GET['mail'])): ?>
            <?php if ($_GET['mail'] === 'success'): ?>
                <div style="background:#004d00;border:1px solid #00a900;border-radius:8px;padding:1rem 1.5rem;margin-bottom:1.5rem;">
                    <strong style="color:#4dff4d;font-size:1rem;display:block;margin-bottom:.25rem">Email Sent Successfully</strong>
                    <span style="color:#b3ffb3;font-size:.85rem">The donor has been notified of your request. Please wait for them to respond.</span>
                </div>
            <?php elseif ($_GET['mail'] === 'error'): ?>
                <div style="background:#4d0000;border:1px solid #a90000;border-radius:8px;padding:1rem 1.5rem;margin-bottom:1.5rem;">
                    <strong style="color:#ff4d4d;font-size:1rem;display:block;margin-bottom:.25rem">Failed to Send Email</strong>
                    <span style="color:#ffb3b3;font-size:.85rem">There was an issue sending the email to the donor. Please try again later.</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (count($donors) > 0): ?>
            <?php foreach ($donors as $donor): ?>
                <div class="donor-card">
                    <div class="donor-info">
                        <h3><?php echo htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']); ?></h3>
                        <p>Location: <?php echo htmlspecialchars($donor['location']); ?></p>
                    </div>
                    <div>
                        <a href="contact_donor_action.php?request_id=<?php echo $request_id; ?>&donor_id=<?php echo $donor['id']; ?>" class="contact-btn">Contact Donor</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No compatible donors found at the moment. We will notify you if someone registers.</p>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="../user/dashboard.php" class="dashboard-link">&larr; Go to Dashboard</a>
        </div>
    </div>

</body>

</html>