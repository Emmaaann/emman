<?php
$host = 'localhost';
$dbname = 'blogdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$data = json_decode(file_get_contents('php://input'), true);
$post_id = $data['postId'] ?? null;

if (!$post_id) {
    echo json_encode(['success' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if the user already liked the post
$stmt = $conn->prepare("SELECT id FROM Likes WHERE post_id = ? AND user_id = ?");
$stmt->bind_param('ii', $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Unlike the post
    $stmt = $conn->prepare("DELETE FROM Likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $post_id, $user_id);
    $stmt->execute();
    $liked = false;
} else {
    // Like the post
    $stmt = $conn->prepare("INSERT INTO Likes (post_id, user_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $post_id, $user_id);
    $stmt->execute();
    $liked = true;
}

// Fetch the updated like count
$stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM Likes WHERE post_id = ?");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$like_count = $stmt->get_result()->fetch_assoc()['like_count'];

echo json_encode(['success' => true, 'liked' => $liked, 'newLikeCount' => $like_count]);
