<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$msgType = "";

// DELETE user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $delId]);
        $message = "User deleted successfully.";
        $msgType = "success";
    } else {
        $message = "Cannot delete your own account.";
        $msgType = "error";
    }
}

// UPDATE user (inline edit via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'edit') {
        $id = intval($_POST['user_id']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $age = intval($_POST['age']);
        $blood_group = $_POST['blood_group'];
        $location = trim($_POST['location']);
        $role = $_POST['role'];
        $is_donor = isset($_POST['is_donor']) ? 1 : 0;

        try {
            $stmt = $pdo->prepare("UPDATE users SET name=:name, email=:email, age=:age, blood_group=:blood_group, location=:location, role=:role, is_donor=:is_donor WHERE id=:id");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'age' => $age,
                'blood_group' => $blood_group,
                'location' => $location,
                'role' => $role,
                'is_donor' => $is_donor,
                'id' => $id
            ]);
            $message = "User updated successfully.";
            $msgType = "success";
        } catch (PDOException $e) {
            $message = "Error updating user: " . $e->getMessage();
            $msgType = "error";
        }
    }

    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $age = intval($_POST['age']);
        $blood_group = $_POST['blood_group'];
        $location = trim($_POST['location']);
        $health_notes = trim($_POST['health_notes']);
        $role = $_POST['role'];
        $is_donor = isset($_POST['is_donor']) ? 1 : 0;

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, age, blood_group, location, health_notes, role, is_donor) VALUES (:name, :email, :password, :age, :blood_group, :location, :health_notes, :role, :is_donor)");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'age' => $age,
                'blood_group' => $blood_group,
                'location' => $location,
                'health_notes' => $health_notes,
                'role' => $role,
                'is_donor' => $is_donor
            ]);
            $message = "User added successfully.";
            $msgType = "success";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "Email already exists!";
            } else {
                $message = "Error: " . $e->getMessage();
            }
            $msgType = "error";
        }
    }
}

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterRole = isset($_GET['role']) ? $_GET['role'] : '';
$filterBlood = isset($_GET['blood_group']) ? $_GET['blood_group'] : '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE :search OR email LIKE :search2)";
    $params['search'] = "%$search%";
    $params['search2'] = "%$search%";
}
if ($filterRole) {
    $sql .= " AND role = :role";
    $params['role'] = $filterRole;
}
if ($filterBlood) {
    $sql .= " AND blood_group = :blood_group";
    $params['blood_group'] = $filterBlood;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For edit mode
$editUser = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editStmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $editStmt->execute(['id' => intval($_GET['edit'])]);
    $editUser = $editStmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users — Vital Drop Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
</head>
<body class="admin-body dark">

    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-top">
                <h1 class="page-title">Users Management</h1>
                <button class="btn-primary" onclick="toggleModal('addUserModal')">+ Add User</button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Search & Filter Bar -->
            <form method="GET" class="filter-bar">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn"><iconify-icon icon="mdi:magnify"></iconify-icon></button>
                </div>

                <select name="blood_group" onchange="this.form.submit()">
                    <option value="">All Blood Groups</option>
                    <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg): ?>
                        <option value="<?php echo $bg; ?>" <?php echo $filterBlood === $bg ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($search || $filterRole || $filterBlood): ?>
                    <a href="users.php" class="clear-filters"><iconify-icon icon="mdi:close"></iconify-icon> Clear</a>
                <?php endif; ?>
            </form>

            <!-- Users Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3>All Users (<?php echo count($users); ?>)</h3>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Age</th>
                                <th>Blood Group</th>
                                <th>Location</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['age']; ?></td>
                                <td><span class="badge bg-badge"><?php echo htmlspecialchars($user['blood_group']); ?></span></td>
                                <td><?php echo htmlspecialchars($user['location']); ?></td>
                                <td><?php echo date('M d', strtotime($user['created_at'])); ?></td>
                                <td class="actions-cell">
                                    <a href="?edit=<?php echo $user['id']; ?>" class="btn-edit" title="Edit"><iconify-icon icon="mdi:pencil"></iconify-icon></a>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <a href="?delete=<?php echo $user['id']; ?>" class="btn-delete" title="Delete" onclick="return confirm('Delete this user?')"><iconify-icon icon="mdi:delete"></iconify-icon></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($users)): ?>
                            <tr><td colspan="8" class="no-data">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- ADD USER MODAL -->
    <div id="addUserModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <button class="modal-close" onclick="toggleModal('addUserModal')"><iconify-icon icon="mdi:close"></iconify-icon></button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="number" name="age" placeholder="Age" min="1" required>
                <select name="blood_group" required>
                    <option value="" disabled selected>Blood Group</option>
                    <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                    <option>O+</option><option>O-</option><option>AB+</option><option>AB-</option>
                </select>
                <input type="text" name="location" placeholder="Location" required>
                <input type="text" name="health_notes" placeholder="Health Notes (optional)">
                <select name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <label class="checkbox-label">
                    <input type="checkbox" name="is_donor" value="1"> Is Donor
                </label>
                <button type="submit" class="btn-primary full-width">Add User</button>
            </form>
        </div>
    </div>

    <!-- EDIT USER MODAL -->
    <?php if ($editUser): ?>
    <div id="editUserModal" class="modal-overlay" style="display:flex;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User #<?php echo $editUser['id']; ?></h2>
                <a href="users.php" class="modal-close"><iconify-icon icon="mdi:close"></iconify-icon></a>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($editUser['name']); ?>" required>
                <input type="email" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
                <input type="number" name="age" value="<?php echo $editUser['age']; ?>" min="1" required>
                <select name="blood_group" required>
                    <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg): ?>
                        <option value="<?php echo $bg; ?>" <?php echo $editUser['blood_group'] === $bg ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="location" value="<?php echo htmlspecialchars($editUser['location']); ?>" required>
                <select name="role">
                    <option value="user" <?php echo $editUser['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo $editUser['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
                <label class="checkbox-label">
                    <input type="checkbox" name="is_donor" value="1" <?php echo $editUser['is_donor'] ? 'checked' : ''; ?>> Is Donor
                </label>
                <button type="submit" class="btn-primary full-width">Update User</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="../js/admin.js"></script>
</body>
</html>
