<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$worksFile = __DIR__ . '/../works.json';
$works = [];
if (file_exists($worksFile)) {
    $works = json_decode(file_get_contents($worksFile), true);
}
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$deleteId = $_GET['id'];
$found = false;
foreach ($works as $index => $work) {
    if ($work['id'] == $deleteId) {
        // Hapus file PDF
        $filePath = __DIR__ . '/../' . $work['file'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        array_splice($works, $index, 1);
        $found = true;
        break;
    }
}
if ($found) {
    file_put_contents($worksFile, json_encode($works, JSON_PRETTY_PRINT));
}
header("Location: dashboard.php");
exit;
