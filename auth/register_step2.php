<?php
session_start();
require '../config/db.php';

$error = "";
$success = "";

// CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Escape function for XSS
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Ensure Step 1 data exists
if (!isset($_SESSION['reg_data'])) {
    header("Location: register_step1.php");
    exit();
}

$step1 = $_SESSION['reg_data'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $age = intval($_POST['age']);
    $blood_group = $_POST['blood_group'];
    $location = trim($_POST['location']);
    $health_notes = trim($_POST['health_notes']);

    if ($age < 18 || $age > 60) {
        $error = "Age must be between 18 and 60.";
    } else {
        $hashed_password = password_hash($step1['password'], PASSWORD_DEFAULT);

        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $step1['email']]);

            if ($stmt->rowCount() > 0) {
                $error = "Email already exists!";
            } else {
                // Insert full registration
                $stmt = $pdo->prepare("INSERT INTO users 
                    (name, email, password, age, blood_group, location, health_notes) 
                    VALUES (:name, :email, :password, :age, :blood_group, :location, :health_notes)");

                $successInsert = $stmt->execute([
                    'name' => $step1['name'],
                    'email' => $step1['email'],
                    'password' => $hashed_password,
                    'age' => $age,
                    'blood_group' => $blood_group,
                    'location' => $location,
                    'health_notes' => $health_notes
                ]);

                if ($successInsert) {
                    $success = 'Registration successful! Please <a href="login.php" style="color:#4dff88;text-decoration:underline;">login</a> to continue.';
                    unset($_SESSION['reg_data']); // clear step1 session
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

<?php include '../includes/navbar.php'; ?>

<div class="register-main">
    <div class="register-left">
        <img src="../images/Photo.png">
    </div>

    <div class="register-right">
        <h2>Register</h2>

        <?php if($error) echo "<p class='reg-error'>".e($error)."</p>"; ?>
        <?php if($success) echo "<p class='reg-success'>".$success."</p>"; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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
            <select name="health_notes" required>
                <option value="" disabled selected>Select your health issues:</option>
                <option value="None">None</option>
                <option value="Diabetes">Diabetes</option>
                <option value="Hypertension">Hypertension</option>
                <option value="Asthma">Asthma</option>
                <option value="Heart Disease">Heart Disease</option>
                <option value="HIV/AIDS">HIV/AIDS</option>
                <option value="Hepatitis B or C">Hepatitis B or C</option>
                <option value="Tuberculosis">Tuberculosis</option>
                <option value="Malaria">Malaria</option>
                <option value="Cancer">Cancer</option>
                <option value="Epilepsy">Epilepsy</option>
                <option value="Pregnancy / Recent Childbirth">Pregnancy / Recent Childbirth</option>
                <option value="Recent Surgery / Tattoo">Recent Surgery / Tattoo</option>
                <option value="Other">Other</option>
            </select>

            <input type="submit" value="Register" class="register-btn">
        </form>

        <p class="register-login">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>
</div>
