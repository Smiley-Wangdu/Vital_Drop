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
    <link rel="stylesheet" href="../css/contact.css">
    <style>
        /* Dark-mode overrides for AJAX-loaded Request Blood form */
        #dynamic-content .tabs-content-wrapper {
            background: #111;
            border-color: #222;
            max-width: 650px;
            margin: 1.5rem auto;
        }
        #dynamic-content .tab-header h2 { color: #fff; }
        #dynamic-content .tab-header p  { color: #888; }
        #dynamic-content .contact-form label { color: #ccc; }
        #dynamic-content .blood-radio span { border-color: #333; color: #fff; }
        #dynamic-content .blood-radio input:checked + span { background: #333; border-color: #a90000; }
        #dynamic-content .contact-form input[type="text"],
        #dynamic-content .contact-form input[type="tel"],
        #dynamic-content .contact-form input[type="number"] { border-color: #333; color: #fff; }
        #dynamic-content .urgency-btn { border-color: #333; color: #fff; }
        #dynamic-content .submit-btn  { color: #fff; }
        /* Urgency button selected states — dark theme overrides */
        #dynamic-content .urgency-radio input[value="Normal"]:checked + .urgency-btn {
            background: #610000;
            border-color: #610000;
            color: #fff;
        }
        #dynamic-content .urgency-radio input[value="Emergency"]:checked + .urgency-btn {
            background: transparent;
            border-color: #a90000;
            color: #ff4d4d;
        }
        #rb-loading { color: #aaa; padding: 3rem; text-align: center; font-size: 1rem; }
    </style>
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
            <p><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></p>
        </div>

        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="donar-dashboard.php">Profile</a></li>
            <li><a href="#" id="sidebar-request-blood">Request Blood</a></li>
            <li><a href="donation-form.php">Donate Blood</a></li>
            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="#">Theme</a></li>
        </ul>

        <a href="../auth/logout.php">
            <button id="logout">Logout</button>
        </a>
    </div>

    <!-- MAIN -->
    <div class="mainn">

        <!-- HEADER -->
        <div class="header">

            <div class="header-left">
                <img src="../images/logo.png" class="logo">
            </div>

            <div class="header-right">
                <h3>Hello, <?php echo e($user['first_name'] . ' ' . $user['last_name']); ?>!</h3>
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

        <!-- CAMPAIGNS / DYNAMIC CONTENT AREA -->
        <div id="dynamic-content">
            <h2 id="campaign">Upcoming Campaigns!</h2>

            <div class="cards-wrapper">
                <div class="cards">

                    <?php foreach ($campaigns as $row): ?>
                        <div class="card"
                            data-location="<?php echo e($row['location']); ?>"
                            data-blood="<?php echo e($row['blood_groups']); ?>">

                            <h3><?php echo e($row['name']); ?></h3>

                            <p>Location: <?php echo e($row['location']); ?></p>
                            <p>Time: <?php echo e($row['time_range']); ?></p>

                            <a href="campaigns.php?id=<?php echo e($row['id']); ?>&csrf=<?php echo $_SESSION['csrf_token']; ?>"
                               class="join-btn">
                                Join
                            </a>

                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
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

<script src="../assets/js/script.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var requestBloodLink = document.getElementById('sidebar-request-blood');
        var dynamicContent   = document.getElementById('dynamic-content');

        if (!requestBloodLink || !dynamicContent) return;

        requestBloodLink.addEventListener('click', function (e) {
            e.preventDefault();

            // Show a loading state
            dynamicContent.innerHTML = '<p id="rb-loading">Loading form…</p>';

            fetch('../public/request_blood_action.php')
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    dynamicContent.innerHTML = html;

                    // Attach submit handler to the injected form
                    var form = document.getElementById('ajaxRequestBloodForm');
                    if (!form) return;

                    form.addEventListener('submit', function (e) {
                        e.preventDefault();

                        var btn = form.querySelector('button[type="submit"]');
                        btn.disabled    = true;
                        btn.textContent = 'Submitting…';

                        fetch('../public/request_blood_action.php', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: new FormData(form)
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.success) {
                                window.location.href = '../public/compatible_donors.php?request_id=' + data.request_id;
                            } else {
                                alert(data.error || 'An error occurred. Please try again.');
                                btn.disabled    = false;
                                btn.textContent = 'Submit Blood Request';
                            }
                        })
                        .catch(function () {
                            alert('Network error. Please try again.');
                            btn.disabled    = false;
                            btn.textContent = 'Submit Blood Request';
                        });
                    });
                })
                .catch(function () {
                    dynamicContent.innerHTML = '<p style="color:#ff4d4d;padding:2rem;text-align:center;">Failed to load form. Please refresh and try again.</p>';
                });
        });
    });
</script>

</body>
</html>