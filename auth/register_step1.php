<?php
session_start();
require '../config/db.php';

$error = "";

// CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Escape function for XSS
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Process Step 1 form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $password = $_POST['password'];
    // Password rule:
    // Minimum 8 characters
    // At least 1 uppercase, 1 lowercase, 1 number, 1 special character

    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";
    if (!preg_match($pattern, $password)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    }

    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Store Step 1 data in session
        $_SESSION['reg_data'] = [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ];
        // Redirect to step 2
        header("Location: register_step2.php");
        exit();
    }
}
?>

<?php include '../includes/navbar.php'; ?>

<div class="register-main">
    <div class="register-left">
        <img src="../images/Photo.png">
    </div>

    <div class="register-right">
        <h2>Register</h2>

        <?php if($error) echo "<p class='reg-error'>".e($error)."</p>"; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="text" name="name" placeholder="Enter your full name" required>
            <input type="email" name="email" placeholder="Enter your email address" required>

            <div class="password-field">
                <input type="password" name="password" placeholder="Enter Password" required>
                <i class="fa-solid fa-eye togglePassword"></i>
            </div>


            <!-- <input type="password" name="password" placeholder="Enter Password" required> -->

            <div class="password-field">
                <input type="password" name="confirm_password" placeholder="Confirm Password" 
                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                title="Password must contain at least 8 characters, including uppercase, lowercase, number, and special character" required>
                <i class="fa-solid fa-eye togglePassword"></i>
            </div>


            <!-- <input type="password" name="confirm_password" placeholder="Confirm Password" required> -->

            <input type="submit" value="Next" class="register-btn">
        </form>

        <p class="register-login">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>
</div>
