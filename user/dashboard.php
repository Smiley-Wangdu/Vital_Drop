<?php
session_start();
require '../config/db.php';

// SECURITY 

// Session check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['regen'])) {
    session_regenerate_id(true);
    $_SESSION['regen'] = true;
}

// CSRF token (for future POST actions like Join)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Escape function for XSS protection
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// FETCH USER 
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If somehow user not found
if (!$user) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

// FETCH CAMPAIGNS 
// Using prepared statements (safe from SQL injection)
$stmt = $pdo->prepare("SELECT id, title, location, time FROM campaigns ORDER BY time ASC");
$stmt->execute();
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vital Drop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <h2>VITAL DROP</h2>

        <div class="profile">
            <div class="avatar">
                <i class="fa-solid fa-user fa-3x"></i>
            </div>
            <p><?php echo e($user['name']); ?></p>
        </div>

        <ul>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="request_blood.php">Request Blood</a></li>
            <li><a href="donate_blood.php">Donate Blood</a></li>
            <li><a href="campaigns.php">Campaigns</a></li>
            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="theme.php">Theme</a></li>
        </ul>

        <a href="../auth/logout.php">
            <button id="logout">Logout</button>
        </a>
    </div>

    <!-- MAIN -->
    <div class="mainn">

        <!-- HEADER -->
        <div class="header">

            <!-- LEFT -->
            <div class="header-left">
                <img src="../images/logo.png" class="logo">
            </div>

            <!-- RIGHT -->
            <div class="header-right">
                <h3>Hello, <?php echo e($user['name']); ?>!</h3>
                <i class="fa-solid fa-user profile-icon" id="menuToggle"></i>
            </div>

        </div>

        <!-- SEARCH -->
        <div class="search-box">
            <div class="input-group">
                <input type="text" id="donorSearch" placeholder="Search for donors">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>

            <select id="bloodGroup">
                <option value="">Filter by blood group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>

            <div class="input-group">
                <input type="text" id="locationSearch" placeholder="Filter by location">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
        </div>

        <!-- CAMPAIGNS -->
        <h2 id="campaign">Upcoming Campaigns!</h2>

        <div class="cards-wrapper">
            <div class="cards">
                <?php foreach ($campaigns as $row): ?>
                    <div class="card" data-blood="<?php echo e($row['blood_group'] ?? ''); ?>" data-location="<?php echo e($row['location']); ?>">
                        <h3><?php echo e($row['title']); ?></h3>
                        <p>Location: <?php echo e($row['location']); ?></p>
                        <p>Time: <?php echo e($row['time']); ?></p>
                        <!-- Use GET with campaign id for security -->
                        <a href="campaigns.php?id=<?php echo e($row['id']); ?>&csrf=<?php echo $_SESSION['csrf_token']; ?>" class="join-btn">Join</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
