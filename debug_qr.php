<?php
require_once 'app/init.php';

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

echo "<h3>QR Debugging Tool</h3>";
echo "<b>1. Target Directory:</b> " . $uploadDir . "<br>";

if (is_dir($uploadDir)) {
    echo "<span style='color:green;'>✔ Directory exists.</span><br>";
} else {
    echo "<span style='color:red;'>✘ Directory DOES NOT exist. Creating it...</span><br>";
    mkdir($uploadDir, 0777, true);
}

if (is_writable($uploadDir)) {
    echo "<span style='color:green;'>✔ Directory is WRITABLE.</span><br>";
} else {
    echo "<span style='color:red;'>✘ Directory is NOT WRITABLE. This is the issue.</span><br>";
}

$testFile = $uploadDir . 'test_write.txt';
if (file_put_contents($testFile, 'PHP can write here')) {
    echo "<span style='color:green;'>✔ Successfully wrote test file.</span><br>";
    unlink($testFile);
} else {
    echo "<span style='color:red;'>✘ FAILED to write test file. Check Windows Security tab.</span><br>";
}
?>