<?php
session_start();
require '../config/db.php';

$error = "";
$message = "";

// Escape function
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid password reset link.");
}

try {

    // Check token in database
    $stmt = $pdo->prepare("
        SELECT id, reset_expires 
        FROM users 
        WHERE reset_token = :token
    ");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Invalid or expired reset link.");
    }

    // Check expiration
    if (strtotime($user['reset_expires']) < time()) {
        die("Reset link has expired.");
    }

} catch (PDOException $e) {
    die("Database error.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF CHECK
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $password = $_POST['password'];
    // Password rule:
    // Minimum 8 characters
    // At least 1 uppercase, 1 lowercase, 1 number, 1 special character

    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";

    if (!preg_match($pattern, $password)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    }
    
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {

        try {

            // Hash new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Update password and remove token
            $stmt = $pdo->prepare("
                UPDATE users
                SET password = :password,
                    reset_token = NULL,
                    reset_expires = NULL
                WHERE id = :id
            ");

            $stmt->execute([
                'password' => $hashedPassword,
                'id' => $user['id']
            ]);

            $message = "Password reset successful. You can now login.";

        } catch (PDOException $e) {
            $error = "Database error.";
        }
    }
}
?>

<?php include '../includes/navbar.php'; ?>

<div class="register-main login_main">

    <!-- LEFT SIDE -->
    <div class="register-left">
        <img src="../images/Photo.png">
    </div>

    <!-- RIGHT SIDE -->
    <div class="register-right">

        <h2>Reset Password</h2>

        <?php if($error) echo "<p class='reg-error'>".e($error)."</p>"; ?>
        <?php if($message) echo "<p class='reg-success'>".e($message)."</p>"; ?>

        <?php if(!$message): ?>

        <form method="POST">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="password-field">
                <input type="password" name="password" placeholder="Enter Password" 
                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                title="Password must contain at least 8 characters, including uppercase, lowercase, number, and special character" required>
                <i class="fa-solid fa-eye togglePassword"></i>
            </div>

            <!-- <input type="password" name="password" placeholder="Enter new password" required> -->

            <div class="password-field">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <i class="fa-solid fa-eye togglePassword"></i>
            </div>

            <!-- <input type="password" name="confirm_password" placeholder="Confirm new password" required> -->

            <input type="submit" value="Reset Password" class="register-btn">

        </form>

        <?php else: ?>

        <p class="register-login">
            <a href="login.php">Go to Login</a>
        </p>

        <?php endif; ?>

    </div>

</div>
