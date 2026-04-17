<?php
session_start();
require '../config/db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

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

/* CHECK EMAIL SESSION */
$email = $_SESSION['reset_email'] ?? '';

if (!$email) {
    die("Invalid request");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* CSRF VALIDATION */
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    /* SANITIZE INPUT */
    $code = trim($_POST['code']);

    if (!preg_match("/^[0-9]{6}$/", $code)) {
        $error = "Invalid verification code format.";
    } else {

        /* SQL INJECTION SAFE QUERY */
        $stmt = $pdo->prepare("SELECT reset_expires FROM users WHERE email=:email AND reset_code=:code");

        $stmt->execute([
            'email' => $email,
            'code' => $code
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            /* CHECK EXPIRY */
            if (strtotime($user['reset_expires']) < time()) {

                $error = "Verification code expired.";

            } else {

                /* REGENERATE SESSION */
                session_regenerate_id(true);

                $_SESSION['verified_code'] = $code;

                header("Location: new_password.php");
                exit();
            }

        } else {

            $error = "Invalid verification code.";
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

        <h2>Verify Code</h2>

        <?php if ($error) echo "<p class='reg-error'>" . e($error) . "</p>"; ?>

        <form method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?php echo e($_SESSION['csrf_token']); ?>">

            <input type="text"
                   name="code"
                   placeholder="Enter verification code"
                   maxlength="6"
                   required>

            <input type="submit"
                   value="Verify Code"
                   class="register-btn">

        </form>

    </div>

</div>