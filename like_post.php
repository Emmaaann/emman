<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like a post.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['post_id']) || !is_numeric($data['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit;
}

$post_id = (int)$data['post_id'];
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE id = :post_id");
    $stmt->execute(['post_id' => $post_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Post does not exist.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = :post_id AND user_id = :user_id");
    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    $like = $stmt->fetch();

    if ($like) {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE id = :like_id");
        $stmt->execute(['like_id' => $like['id']]);
        $action = 'unliked';
    } else {
        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)");
        $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
        $action = 'liked';
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
    $stmt->execute(['post_id' => $post_id]);
    $likes_count = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'action' => $action, 'likes_count' => $likes_count]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
