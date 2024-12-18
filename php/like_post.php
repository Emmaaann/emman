<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'blogdb';
$username = 'root';
$password = '';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like a post.']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get data from the request
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['post_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
        exit;
    }

    $post_id = $data['post_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the user already liked this post
    $stmt = $pdo->prepare("SELECT id FROM Likes WHERE post_id = :post_id AND user_id = :user_id");
    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    $like = $stmt->fetch();

    if ($like) {
        echo json_encode(['success' => false, 'message' => 'You have already liked this post.']);
        exit;
    }

    // Insert a new like
    $stmt = $pdo->prepare("INSERT INTO Likes (post_id, user_id) VALUES (:post_id, :user_id)");
    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);

    // Get the updated like count
    $stmt = $pdo->prepare("SELECT COUNT(*) AS likes_count FROM Likes WHERE post_id = :post_id");
    $stmt->execute(['post_id' => $post_id]);
    $like_count = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'new_likes' => $like_count]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
