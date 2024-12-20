<?php
$host = 'sql203.infinityfree.com';
$dbname = 'if0_37727017_blogdb';
$username = 'if0_37727017';
$password = '8KmXpV9wEy6';

// Establish database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
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
