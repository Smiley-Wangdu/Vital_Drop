<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<header class="admin-header">
    <div class="header-left">
        <a href="../public/index.php" class="header-back" title="Back to site"><iconify-icon icon="mdi:arrow-left"></iconify-icon></a>
        <div class="header-brand">
            <img src="../images/logo.png" alt="Vital Drop" class="header-logo">
            <span class="header-title">VITAL DROP</span>
        </div>
        <button class="settings-icon" title="Settings"><iconify-icon icon="mdi:cog"></iconify-icon></button>
    </div>
    <div class="header-right">
        <span class="header-greeting">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
        <div class="header-avatar"><iconify-icon icon="mdi:account-circle"></iconify-icon></div>
    </div>
</header>
