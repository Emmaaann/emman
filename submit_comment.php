<?php
session_start();

header('Content-Type: application/json');

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

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate the CSRF token
if (!isset($data['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $data['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to comment.']);
    exit;
}

// Validate and sanitize inputs
$post_id = filter_var($data['post_id'], FILTER_VALIDATE_INT);
$comment_content = trim($data['comment']);

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit;
}

if (empty($comment_content) || strlen($comment_content) > 500) {
    echo json_encode(['success' => false, 'message' => 'Comment must be between 1 and 500 characters.']);
    exit;
}

try {
    // Insert the comment into the database
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())");
    $stmt->execute([
        ':post_id' => $post_id,
        ':user_id' => $_SESSION['user_id'],
        ':content' => htmlspecialchars($comment_content)
    ]);

    // Retrieve the new comment details
    $comment_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare(
        "SELECT c.id, c.content, c.created_at, u.username 
         FROM comments c 
         JOIN users u ON c.user_id = u.id 
         WHERE c.id = :comment_id"
    );
    $stmt->execute([':comment_id' => $comment_id]);
    $new_comment = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'comment' => $new_comment]);
} catch (PDOException $e) {
    error_log("Error saving comment: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save comment.']);
}
?>
