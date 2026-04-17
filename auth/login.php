<?php
session_start();
require '../config/db.php';

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

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Check admin first
        $stmt = $pdo->prepare("
            SELECT id, name, password, 'admin' as role 
            FROM admins 
            WHERE email = :email
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If not admin → check users
        if (!$user) {
            $stmt = $pdo->prepare("
                SELECT 
                    id, 
                    CONCAT(first_name, ' ', last_name) AS name, 
                    password, 
                    'user' AS role 
                FROM users 
                WHERE email = :email
            ");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($user) {
            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: ../admin/index.php");
                } else {
                    header("Location: ../user/dashboard.php");
                }
                exit;

            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "Email not found!";
        }

    } catch (PDOException $e) {
        $error = "Database error!";
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
        <h2>Login</h2>

        <?php if($error) echo "<p class='reg-error'>".e($error)."</p>"; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="email" name="email" placeholder="Enter your email address" required>

            <div class="password-field">
                <input type="password" name="password" placeholder="Enter Password" required>
                <i class="fa-solid fa-eye togglePassword"></i>
            </div>
            
            <p><a href="forgot_password.php" id="forgot_password">Forgot password?</a></p>

            <input type="submit" value="Login" class="register-btn">
        </form>

        <p class="register-login">
            Don't have an account? <a href="register_step1.php">Register</a>
        </p>
    </div>

</div>
