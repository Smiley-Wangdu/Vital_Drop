<?php
$out = 'combined_files.txt';
@unlink($out);
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
    if (!in_array($ext, ['php', 'html', 'css', 'js'])) continue;
    $path = $file->getPathname();
    if (strpos($path, 'combined_files.txt') !== false) continue;
    
    $content = "/* ==========================================\n" .
               "   File: " . str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $path) . "\n" .
               "   ========================================== */\n\n" .
               file_get_contents($path) . "\n\n\n";
    file_put_contents($out, $content, FILE_APPEND);
}
echo "Done.";
?>
