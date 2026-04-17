<?php
session_start();
require '../config/db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = "";
$message = "";

/* CSRF TOKEN */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ESCAPE FUNCTION FOR XSS */
function e($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/* SESSION VALIDATION */
$email = $_SESSION['reset_email'] ?? '';
$code = $_SESSION['verified_code'] ?? '';

if (!$email || !$code) {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* CSRF VALIDATION */
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    /* SANITIZE INPUT */
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);

    /* PASSWORD POLICY */
    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";

    if (!preg_match($pattern, $password)) {
        $error = "Password must contain uppercase, lowercase, number and special character.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Fetch current password hash
        $stmt = $pdo->prepare("SELECT password FROM users WHERE email=:email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $error = "Please enter a new password (cannot reuse old password).";
        } else {
            // HASH PASSWORD 
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Update password
            $stmt = $pdo->prepare("UPDATE users SET password=:password, reset_code=NULL, reset_expires=NULL WHERE email=:email");
            $stmt->execute([
                'password' => $hash,
                'email' => $email
            ]);

            // Get user ID
            $stmt = $pdo->prepare("SELECT id, first_name FROM users WHERE email=:email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Clear reset session data
            unset($_SESSION['reset_email']);
            unset($_SESSION['verified_code']);

            // AUTO LOGIN (same idea as register)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];

            // Regenerate session
            session_regenerate_id(true);

            // Redirect to dashboard
            header("Location: ../user/dashboard.php?reset=success");
            exit();
        }
    }
}
?>

<?php include '../includes/navbar.php'; ?>

<div class="register-main login_main">

    <div class="register-left">
        <img src="../images/Photo.png">
    </div>

    <div class="register-right">

        <h2>Reset Password</h2>

        <?php if ($error)
            echo "<p class='reg-error'>" . e($error) . "</p>"; ?>
        <?php if ($message)
            echo "<p class='reg-success'>" . e($message) . "</p>"; ?>

        <?php if (!$message): ?>

            <form method="POST">

                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">

                <div class="password-field">
                    <input type="password" name="password" placeholder="Enter Password" required>
                    <i class="fa-solid fa-eye togglePassword"></i>
                </div>

                <div class="password-field">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <i class="fa-solid fa-eye togglePassword"></i>
                </div>

                <input type="submit" value="Reset Password" class="register-btn">

            </form>

        <?php else: ?>

            <p class="register-login">
                <a href="login.php">Go to Login</a>
            </p>

        <?php endif; ?>

    </div>

</div>