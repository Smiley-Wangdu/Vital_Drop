<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-top">
        <div class="sidebar-profile">
            <div class="profile-avatar"></div>
            <span class="profile-name">Admin</span>
        </div>

        <nav class="sidebar-nav">
            <a href="profile.php" class="nav-item <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                <span class="nav-icon"></span> Profile
            </a>
            <a href="index.php" class="nav-item <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <span class="nav-icon"></span> Dashboard
            </a>
            <a href="users.php" class="nav-item <?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                <span class="nav-icon"></span> Users
            </a>
            <a href="campaigns.php" class="nav-item <?php echo ($current_page === 'campaigns.php' || $current_page === 'create_campaign.php' || $current_page === 'edit_campaign.php') ? 'active' : ''; ?>">
                <span class="nav-icon"></span> Campaigns
            </a>
            <a href="requests.php" class="nav-item <?php echo $current_page === 'requests.php' ? 'active' : ''; ?>">
                <span class="nav-icon"></span> Requests
            </a>
            <div class="theme-wrapper">
                <button id="admin-theme-toggle" class="nav-item theme-btn">
                    <span class="nav-icon"></span> Theme
                </button>
                <div id="theme-dropdown" class="theme-dropdown">
                    <button class="theme-option" data-theme="light">
                        <span></span> Light
                    </button>
                    <button class="theme-option" data-theme="dark">
                        <span></span> Dark
                    </button>
                </div>
            </div>
        </nav>
    </div>

    <div class="sidebar-bottom">
        <a href="../auth/logout.php" class="logout-btn-sidebar">Logout</a>
    </div>
</aside>
