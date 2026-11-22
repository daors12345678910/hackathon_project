<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] === 'donor') {
    header("Location: donor.php");
    exit();
}

if ($_SESSION['role'] === 'charity') {
    header("Location: charity.php");
    exit();
}

if ($_SESSION['role'] === 'admin') {
    header("Location: admin.php");
    exit();
}

session_destroy();
header("Location: login.php");
exit();
?>