<?php
$host = 'localhost';
$dbname = 'blogdb';
$username = 'root';
$password = '';
session_start();
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
session_unset();
session_destroy();

// Clear cookies if set
if (isset($_SESSION['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}
if (isset($_SESSION['username'])) {
    setcookie('username', '', time() - 3600, '/');
}
if (isset($_SESSION['role'])) {
    setcookie('role', '', time() - 3600, '/');
}

header("Location: login.php");
exit;
?>
