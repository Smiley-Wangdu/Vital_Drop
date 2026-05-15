<?php
$files = ['index.php', 'profile.php', 'users.php', 'campaigns.php', 'requests.php'];
foreach($files as $file) {
    $path = 'c:/xampp/htdocs/Vital_Drop/admin/' . $file;
    if(file_exists($path)) {
        $content = file_get_contents($path);
        
        // Remove old inclusions of footor.php
        $content = preg_replace("/\s*<\?php include '\.\.\/includes\/footor\.php'; \?>/", '', $content);
        
        // Add footer inside admin-main right before </main>
        if (strpos($content, "</main>") !== false) {
            $content = str_replace("</main>", "\n        <?php include '../includes/footor.php'; ?>\n        </main>", $content);
            file_put_contents($path, $content);
            echo "Fixed $file\n";
        }
    }
}
?>
