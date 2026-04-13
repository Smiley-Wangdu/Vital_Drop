<?php
session_start();
require '../config/db.php';

$message = "";
$error = "";

// CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Escape function
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF CHECK
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $email = trim($_POST['email']);

    try {

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            // Generate reset token
            $token = bin2hex(random_bytes(32));

            // Expiration time (1 hour)
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Save token to database
            $stmt = $pdo->prepare("
                UPDATE users 
                SET reset_token = :token, reset_expires = :expires 
                WHERE email = :email
            ");

            $stmt->execute([
                'token' => $token,
                'expires' => $expires,
                'email' => $email
            ]);

            // Reset link
            $reset_link = "http://localhost/VitalDrop/auth/reset_password.php?token=" . $token;

            $message = "Password reset link generated. Click the Link:<br><a href='$reset_link'>$reset_link</a>";

        } else {
            $error = "Email not found!";
        }

    } catch (PDOException $e) {
        $error = "Database error.";
    }
}
?>

<?php include '../includes/navbar.php'; ?>

<div class="register-main login_main">

    <!-- LEFT -->
    <div class="register-left">
        <img src="../images/Photo.png">
    </div>

    <!-- RIGHT -->
    <div class="register-right">

        <h2>Forgot Password</h2>

        <?php if($error) echo "<p class='reg-error'>".e($error)."</p>"; ?>
        <?php if($message) echo "<p class='reg-success'>$message</p>"; ?>

        <form method="POST">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="email" name="email" placeholder="Enter your registered email" required>

            <input type="submit" value="Send Reset Link" class="register-btn">

        </form>

        <p class="register-login">
            Remember your password? <a href="login.php">Login</a>
        </p>

    </div>

</div>
