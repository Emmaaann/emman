<?php
session_start();

$host = 'localhost';
$dbname = 'blogdb';
$username = 'root';
$password = '';

// Database connection setup
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to comment.");
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$comment = trim($_POST['comment']);

if (empty($comment)) {
    die("Comment cannot be empty.");
}

// Check if the user has already commented
$stmt = $pdo->prepare("SELECT id FROM Comments WHERE user_id = :user_id AND post_id = :post_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->fetch()) {
    die("You have already commented on this post.");
}

// Insert the comment
$stmt = $pdo->prepare("INSERT INTO Comments (user_id, post_id, content, created_at) VALUES (:user_id, :post_id, :content, NOW())");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->bindParam(':content', $comment, PDO::PARAM_STR);

if ($stmt->execute()) {
    header("Location: post.php?id=$post_id");
    exit;
} else {
    die("Failed to submit your comment. Please try again.");
}
?>
