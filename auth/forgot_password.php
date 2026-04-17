<?php
session_start();
require '../config/db.php';
require '../config/send_mail.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = "";
$error = "";

/* CSRF TOKEN */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ESCAPE FUNCTION FOR XSS */
function e($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $email = trim($_POST['email']);

    try {

        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE email=:email");
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            $firstName = $user['first_name'];
            $lastName = $user['last_name'];

            $code = rand(100000, 999999);
            $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            $stmt = $pdo->prepare("UPDATE users SET reset_code=:code, reset_expires=:expires WHERE email=:email");
            $stmt->execute([
                'code' => $code,
                'expires' => $expires,
                'email' => $email
            ]);

            $_SESSION['reset_email'] = $email;

            if (sendResetCode($email, $code, $firstName, $lastName)) {
                header("Location: verify_code.php");
                exit;
            } else {
                $error = "Failed to send email.";
            }

        } else {
            $error = "Email not found!";
        }


    } catch (PDOException $e) {

        $error = "Database error: " . $e->getMessage();

    }

}
?>

<?php include '../includes/navbar.php'; ?>

<div class="register-main login_main">

    <div class="register-left">
        <img src="../images/Photo.png">
    </div>

    <div class="register-right">

        <h2>Forgot Password</h2>

        <?php if ($error)
            echo "<p class='reg-error'>" . e($error) . "</p>"; ?>

        <form method="POST">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="email" name="email" placeholder="Enter your registered email" required>

            <input type="submit" value="Send Verification Code" class="register-btn">

        </form>

        <p class="register-login">
            Remember your password? <a href="login.php">Login</a>
        </p>

    </div>
</div>