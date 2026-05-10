<?php
$files = glob('c:/xampp/htdocs/Vital_Drop/assets/css/*.css');
foreach($files as $file) {
    $content = file_get_contents($file);
    // Replace in regular state
    $content = str_replace('.contact-btn, .urgency-btn, .mr-ef-submit', '.contact-btn, .mr-ef-submit', $content);
    $content = str_replace('.hero-btn, .vd-stat-btn, .vd-blood-btn, .vd-btn-donate-now', '.hero-btn, .vd-stat-btn, .vd-btn-donate-now', $content);
    
    // Replace in hover state
    $content = str_replace('.contact-btn:hover, .urgency-btn:hover, .mr-ef-submit:hover', '.contact-btn:hover, .mr-ef-submit:hover', $content);
    $content = str_replace('.hero-btn:hover, .vd-stat-btn:hover, .vd-blood-btn:hover, .vd-btn-donate-now:hover', '.hero-btn:hover, .vd-stat-btn:hover, .vd-btn-donate-now:hover', $content);
    
    // For admin.css which was reformatted line by line in earlier step
    $content = str_replace(".contact-btn,\n.urgency-btn,\n.mr-ef-submit", ".contact-btn,\n.mr-ef-submit", $content);
    $content = str_replace(".hero-btn,\n.vd-stat-btn,\n.vd-blood-btn,\n.vd-btn-donate-now", ".hero-btn,\n.vd-stat-btn,\n.vd-btn-donate-now", $content);

    $content = str_replace(".contact-btn:hover,\n.urgency-btn:hover,\n.mr-ef-submit:hover", ".contact-btn:hover,\n.mr-ef-submit:hover", $content);
    $content = str_replace(".hero-btn:hover,\n.vd-stat-btn:hover,\n.vd-blood-btn:hover,\n.vd-btn-donate-now:hover", ".hero-btn:hover,\n.vd-stat-btn:hover,\n.vd-btn-donate-now:hover", $content);

    file_put_contents($file, $content);
    echo 'Updated ' . basename($file) . "\n";
}
?>
