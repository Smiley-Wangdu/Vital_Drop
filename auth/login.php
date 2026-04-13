<?php
session_start();
require '../config/db.php';

$error = "";

// CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Escape function (XSS protection)
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Check if user exists in admins table first
        $stmtAdmin = $pdo->prepare("SELECT id, name, password, 'admin' as role FROM admins WHERE email = :email");
        $stmtAdmin->execute(['email' => $email]);
        $user = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Check if user exists in users table
            $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/index.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "Email not found!";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Vital Drop</title>
    <meta name="description" content="Login to your Vital Drop account to manage blood donations and requests.">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="register-main login_main">

    <!-- LEFT SIDE -->
    <div class="register-left">
        <img src="../images/Photo.png" alt="Blood donation illustration">
    </div>

    <!-- RIGHT SIDE -->
    <div class="register-right">
        <h2>Login</h2>

        <?php if($error) echo "<p class='reg-error'>".e($error)."</p>"; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="email" name="email" placeholder="Enter your email address" required>
            
            <input type="password" name="password" placeholder="Enter your password" required>

            <p><a href="forgot_password.php" id="forgot_password">Forgot password?</a></p>

            <input type="submit" value="Login" class="register-btn">
        </form>

        <p class="register-login">
            Don't have an account? <a href="register.php">Sign up</a>
        </p>
    </div>

</div>

<script src="../js/animations.js"></script>
</body>
</html>
