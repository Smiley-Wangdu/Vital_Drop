<?php
session_start();
require '../config/db.php';

$error = "";

// CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Escape function for XSS
function e($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Process Step 1 form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";

    if (!preg_match($pattern, $password)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {

        $_SESSION['reg_data'] = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];

        header("Location: register_step2.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - Vital Drop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="register-main">
        <div class="register-left">
            <img src="../images/Photo.png">
        </div>

        <div class="register-right">
            <h2>Register</h2>

            <?php if ($error)
                echo "<p class='reg-error'>" . e($error) . "</p>"; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="name-fields">
                    <input type="text" name="first_name" placeholder="Enter your first name" required>
                    <input type="text" name="last_name" placeholder="Enter your last name" required>
                </div>

                <input type="email" name="email" placeholder="Enter your email address" required>

                <div class="password-field">
                    <input type="password" name="password" placeholder="Enter Password" required>
                    <i class="fa-solid fa-eye togglePassword"></i>
                </div>

                <div class="password-field">
                    <input type="password" name="confirm_password" placeholder="Confirm Password"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                        title="Password must contain at least 8 characters, including uppercase, lowercase, number, and special character"
                        required>
                    <i class="fa-solid fa-eye togglePassword"></i>
                </div>

                <input type="submit" value="Next" class="register-btn">
            </form>

            <p class="register-login">
                Already have an account? <a href="login.php">Login</a>
            </p>
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>

</html>