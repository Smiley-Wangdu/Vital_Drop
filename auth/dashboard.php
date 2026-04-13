<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Escape function (XSS protection)
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Vital Drop</title>
    <meta name="description" content="Your Vital Drop dashboard. Manage your blood donation profile and activity.">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .dashboard-container {
            min-height: 100vh;
            padding: 160px 80px 80px;
        }

        .dashboard-welcome {
            opacity: 0;
            transform: translateY(40px);
            animation: dashFadeUp 0.8s 0.3s forwards;
        }

        .dashboard-welcome h1 {
            font-size: 42px;
            color: #5a0000;
            margin-bottom: 10px;
            font-family: 'Inter', Arial, sans-serif;
        }

        .dashboard-welcome p {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .dash-card {
            background: #f5f5f5;
            border-radius: 16px;
            padding: 30px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 0;
            transform: translateY(30px);
        }

        .dash-card:nth-child(1) { animation: dashFadeUp 0.8s 0.5s forwards; }
        .dash-card:nth-child(2) { animation: dashFadeUp 0.8s 0.7s forwards; }
        .dash-card:nth-child(3) { animation: dashFadeUp 0.8s 0.9s forwards; }

        .dash-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(128, 0, 0, 0.1);
        }

        .dash-card h3 {
            font-size: 20px;
            color: #5a0000;
            margin-bottom: 10px;
        }

        .dash-card p {
            color: #666;
            line-height: 1.6;
        }

        .dash-card .card-icon {
            font-size: 36px;
            margin-bottom: 15px;
            display: block;
        }

        .logout-btn {
            display: inline-block;
            padding: 12px 32px;
            background: linear-gradient(135deg, #800000, #4d0000);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-family: 'Inter', Arial, sans-serif;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            margin-top: 10px;
        }

        .logout-btn:hover {
            transform: scale(1.05) translateY(-2px);
            background: linear-gradient(135deg, #a00000, #660000);
            box-shadow: 0 10px 30px rgba(128, 0, 0, 0.2);
        }

        @keyframes dashFadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dashboard Dark Mode */
        body.dark-mode .dashboard-welcome h1 { color: #ff4d4d; }
        body.dark-mode .dashboard-welcome p { color: #aaa; }
        body.dark-mode .dash-card { background: #1e1e1e; }
        body.dark-mode .dash-card h3 { color: #ff4d4d; }
        body.dark-mode .dash-card p { color: #bbb; }
        body.dark-mode .dash-card:hover {
            box-shadow: 0 15px 40px rgba(255, 77, 77, 0.08);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 100px 20px 40px;
            }
            .dashboard-welcome h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="dashboard-welcome">
        <h1>Welcome, <?php echo e($_SESSION['user_name']); ?>!</h1>
        <p>Your Vital Drop dashboard — where every drop counts.</p>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="dashboard-cards">
        <div class="dash-card">
            <span class="card-icon"></span>
            <h3>My Donations</h3>
            <p>Track your donation history and see the impact you've made in saving lives.</p>
        </div>
        <div class="dash-card">
            <span class="card-icon"></span>
            <h3>Find Donors</h3>
            <p>Search for compatible blood donors near your location quickly and securely.</p>
        </div>
        <div class="dash-card">
            <span class="card-icon"></span>
            <h3>Requests</h3>
            <p>View and respond to urgent blood requests from people in need.</p>
        </div>
    </div>

    <!-- My Blood Requests Section -->
    <div style="margin-top: 40px;">
        <h2 style="color: #5a0000; font-family: 'Inter', sans-serif; margin-bottom: 20px;">My Blood Requests</h2>
        <?php
        require '../config/db.php';
        
        // Auto-expire requests older than 7 days
        $pdo->query("UPDATE blood_requests SET status = 'Expired' WHERE expires_at < NOW() AND status = 'Active'");
        
        $stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($requests) > 0) {
            foreach ($requests as $req) {
                // Ensure text colors apply well
                echo "<div class='dash-card' style='margin-bottom: 15px; opacity: 1; transform: none; display: flex; justify-content: space-between; align-items: center;'>";
                echo "<div>";
                echo "<h3 style='margin-bottom:5px;'>" . e($req['blood_group']) . " needed at " . e($req['hospital_name']) . "</h3>";
                echo "<p style='margin-bottom:5px; font-size: 0.9em;'>" . e($req['units_required']) . " Units | Urgency: <b>" . e($req['urgency']) . "</b> | Location: " . e($req['location']) . "</p>";
                echo "<p style='margin-bottom:0; font-size: 0.85em;'>Status: <strong>" . e($req['status']) . "</strong> | Expires: " . e(date('M d, Y', strtotime($req['expires_at']))) . "</p>";
                echo "</div>";
                
                if ($req['status'] === 'Active') {
                    echo "<div>";
                    echo "<form action='update_request.php' method='POST' style='display:inline;'>";
                    echo "<input type='hidden' name='request_id' value='" . $req['id'] . "'>";
                    echo "<button type='submit' name='action' value='fulfill' style='padding:8px 12px; background:green; color:white; border:none; border-radius:5px; cursor:pointer; margin-right:5px;'>Fulfill</button>";
                    echo "<button type='submit' name='action' value='cancel' style='padding:8px 12px; background:#d32f2f; color:white; border:none; border-radius:5px; cursor:pointer; margin-right:5px;'>Cancel</button>";
                    echo "<a href='edit_request.php?id=" . $req['id'] . "' style='padding:8px 12px; background:#333; color:white; text-decoration:none; border-radius:5px;'>Edit</a>";
                    echo "</form>";
                    echo "</div>";
                }
                echo "</div>";
            }
        } else {
            echo "<p style='color: #666;'>You have no active blood requests. <a href='../public/contact.php' style='color:#a90000;'>Create one</a>.</p>";
        }
        ?>
    </div>
</div>


<script src="../js/animations.js"></script>
</body>
</html>
