<?php
session_start();

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
$stmt = $pdo->prepare("SELECT id FROM comments WHERE user_id = :user_id AND post_id = :post_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->fetch()) {
    die("You have already commented on this post.");
}

// Insert the comment
$stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, content, created_at) VALUES (:user_id, :post_id, :content, NOW())");
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
