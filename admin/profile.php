<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$msgType = "";

// Fetch admin profile
$stmt = $pdo->prepare("SELECT *, 'admin' as role FROM admins WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $location = trim($_POST['location']);
    $age = intval($_POST['age']);

    $updateSql = "UPDATE admins SET name=:name, email=:email, location=:location, age=:age";
    $params = ['name' => $name, 'email' => $email, 'location' => $location, 'age' => $age, 'id' => $_SESSION['user_id']];

    // Password change (optional)
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $updateSql .= ", password=:password";
            $params['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        } else {
            $message = "Passwords do not match!";
            $msgType = "error";
        }
    }

    if ($msgType !== 'error') {
        $updateSql .= " WHERE id=:id";
        try {
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute($params);
            $_SESSION['user_name'] = $name;
            $message = "Profile updated successfully.";
            $msgType = "success";

            // Re-fetch
            $stmt = $pdo->prepare("SELECT *, 'admin' as role FROM admins WHERE id = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $msgType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile — Vital Drop</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="admin-body dark">

    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <h1 class="page-title">Admin Profile</h1>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="profile-grid">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar-lg"></div>
                        <div>
                            <h2><?php echo htmlspecialchars($admin['name']); ?></h2>
                            <p class="profile-role"><?php echo ucfirst($admin['role']); ?> · <?php echo htmlspecialchars($admin['blood_group']); ?></p>
                            <p class="profile-meta">Location: <?php echo htmlspecialchars($admin['location']); ?> · Joined <?php echo date('M Y', strtotime($admin['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="profile-edit-card">
                    <h3>Edit Profile</h3>
                    <form method="POST" class="profile-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Age</label>
                                <input type="number" name="age" value="<?php echo $admin['age']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" value="<?php echo htmlspecialchars($admin['location']); ?>" required>
                            </div>
                        </div>

                        <h4 class="section-divider">Change Password (optional)</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" placeholder="Leave blank to keep current">
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" placeholder="Confirm new password">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
