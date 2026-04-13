<?php
session_start();
require '../config/db.php';

$error = "";
$success = "";

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

    // Get and trim inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $age = intval($_POST['age']);
    $blood_group = $_POST['blood_group'];
    $location = trim($_POST['location']);
    $health_notes = trim($_POST['health_notes']);

    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif ($age < 18) {
        $error = "You must be at least 18 years old.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);

            if ($stmt->rowCount() > 0) {
                $error = "Email already exists!";
            } else {
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users 
                    (name, email, password, age, blood_group, location, health_notes) 
                    VALUES (:name, :email, :password, :age, :blood_group, :location, :health_notes)");

                $successInsert = $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashed_password,
                    'age' => $age,
                    'blood_group' => $blood_group,
                    'location' => $location,
                    'health_notes' => $health_notes
                ]);

                if ($successInsert) {
                    $success = "Registration successful!";
                } else {
                    $error = "Something went wrong.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Vital Drop</title>
    <meta name="description" content="Create your Vital Drop account to become a blood donor or find donors near you.">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="register-main">

    <!-- LEFT SIDE -->
    <div class="register-left">
        <img src="../images/Photo.png" alt="Blood donation illustration">
    </div>

    <!-- RIGHT SIDE -->
    <div class="register-right">
        <h2>Register</h2>

        <?php if($error) echo "<p class='reg-error'>".e($error)."</p>"; ?>
        <?php if($success) echo "<p class='reg-success'>".e($success)."</p>"; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="text" name="name" placeholder="Enter your full name" required>
            <input type="email" name="email" placeholder="Enter your email address" required>
        
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            <input type="number" name="age" placeholder="Enter your age" required>

            <select name="blood_group" required>
                <option value="" disabled selected>Select Blood Group:</option>
                <option>A+</option>
                <option>O+</option>
                <option>AB+</option>
                <option>B+</option>
                <option>A-</option>
                <option>B-</option>
                <option>O-</option>
                <option>AB-</option>
            </select>

            <input type="text" name="location" placeholder="Enter your current location" required>
            <input type="text" name="health_notes" placeholder="Enter health issues if you have any (optional)">

            <input type="submit" value="Register" class="register-btn">
        </form>

        <p class="register-login">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>

</div>

<script src="../js/animations.js"></script>
</body>
</html>
