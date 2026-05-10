<?php
session_start();
require '../config/db.php';

// SECURITY
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Session protection
if (!isset($_SESSION['regen'])) {
    session_regenerate_id(true);
    $_SESSION['regen'] = true;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Escape function
function e($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// FETCH USER
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

// FETCH CAMPAIGNS (UPDATED FOR NEW DB STRUCTURE)
$stmt = $pdo->prepare("
    SELECT id, name, location, time_range, blood_groups 
    FROM campaigns 
    ORDER BY id DESC
");
$stmt->execute();
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Vital Drop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/contact.css">
    <link rel="stylesheet" href="../assets/css/donor-style.css">
    <!-- Iconify icon library (for theme toggle sun/moon icons) -->
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
</head>

<body>

    <div class="container">

        <!-- SIDEBAR -->
        <div class="sidebar" id="sidebar">
            <h1 class="sidebar-logo">VITAL DROP</h1>
            <div class="sidebar-avatar-container">
                <div class="sidebar-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
            <p class="sidebar-username"><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></p>

            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fa-solid fa-house"></i> Dashboard</a></li>
                <li><a href="#" id="sidebar-profile"><i class="fa-solid fa-user"></i> Profile</a></li>
                <li><a href="#" id="sidebar-request-blood"><i class="fa-solid fa-hand-holding-droplet"></i> Request Blood</a></li>
                <li><a href="#" id="sidebar-donate-blood"><i class="fa-solid fa-heart-pulse"></i> Donate Blood</a></li>
                <li><a href="notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            </ul>

            <div class="sidebar-footer">
                <button id="logout" class="logout-btn">Logout</button>
            </div>
        </div>

        <!-- MAIN -->
        <div class="mainn">

            <!-- HEADER -->
            <div class="header">

                <div class="header-left">
                    <img src="../images/logo.png" class="logo">
                </div>

                <div class="header-right">
                    <button class="vd-theme-toggle" id="headerThemeToggle">
                        <iconify-icon icon="solar:moon-bold" width="22" height="22"></iconify-icon>
                    </button>
                    <h3>Hello, <?php echo e($user['first_name'] . ' ' . $user['last_name']); ?>!</h3>
                    <i class="fa-solid fa-user profile-icon" id="menuToggle"></i>
                </div>

            </div>

            <!-- SEARCH -->
            <div class="search-box">

                <div class="input-group">
                    <input type="text" id="donorSearch" placeholder="Search for campaign name">
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

            <!-- CAMPAIGNS / DYNAMIC CONTENT AREA -->
            <div id="dynamic-content">
                <h2 id="campaign">Campaigns</h2>

                <div class="cards-wrapper">
                    <div class="cards">

                        <?php foreach ($campaigns as $row): ?>
                            <div class="card" data-location="<?php echo e($row['location']); ?>"
                                data-blood="<?php echo e($row['blood_groups']); ?>">

                                <h3><?php echo e($row['name']); ?></h3>

                                <p>Location: <?php echo e($row['location']); ?></p>
                                <p>Time: <?php echo e($row['time_range']); ?></p>

                                <!-- JOIN BUTTON -->
                                <button type="button" class="join-btn"
                                    onclick="toggleDashboardJoinForm(<?php echo $row['id']; ?>)">
                                    Join Campaign
                                </button>

                                <!-- FORM (INSIDE CARD PROPERLY) -->
                                <form id="dashboard-join-form-<?php echo $row['id']; ?>" class="dashboard-join-form"
                                    data-id="<?php echo $row['id']; ?>" style="display:none;">

                                    <input type="hidden" name="campaign_id" value="<?php echo $row['id']; ?>">

                                    <label>First Name</label>
                                    <input type="text" name="first_name" required>

                                    <label>Last Name</label>
                                    <input type="text" name="last_name" required>

                                    <label>Phone Number</label>
                                    <input type="tel" name="phone" required pattern="98[0-9]{8}" maxlength="10"
                                        minlength="10" placeholder="98XXXXXXXX">

                                    <label>Campaign Name</label>
                                    <input type="text" name="campaign_name" value="<?php echo e($row['name']); ?>" readonly>

                                    <label>Location</label>
                                    <input type="text" name="location" value="<?php echo e($row['location']); ?>" readonly>

                                    <button type="submit" class="join-btn">
                                        Confirm Join
                                    </button>

                                    <p class="dj-error" style="display:none;"></p>

                                    <div class="dj-success" style="display:none;">
                                        <div class="success-msg"></div>
                                        <button type="button" onclick="this.closest('form').style.display='none'">
                                            OK
                                        </button>
                                    </div>

                                </form>

                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>

            <p id="noResults" style="display:none; text-align:center; color:#aaa; margin-top:20px;">
                No campaigns found.
            </p>
        </div>

    </div>

    </div>

    <!-- LOGOUT MODAL -->
    <div id="logoutModal" class="modal">
        <div class="modal-box">
            <p>Are you sure you want to logout?</p>

            <div class="modal-actions">
                <button id="cancelLogout">Cancel</button>
                <button id="confirmLogout">Logout</button>
            </div>
        </div>
    </div>
    <?php include '../includes/footor.php'; ?>

    <script src="../assets/js/donor.js"></script>
    <script src="../assets/js/script.js"></script>
    <!-- Add theme.js to handle the dashboard theme toggle -->
    <script src="../assets/js/theme.js"></script>

    <?php /* CHATBOT WIDGET: Floating assistant — only renders for logged-in users */ ?>
    <?php include __DIR__ . '/../includes/chatbot_widget.php'; ?>
</body>

</html>