<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
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

// Error messages
const ERR_DB_CONNECTION = 'Error: Unable to connect to the database.';
const ERR_INVALID_POST_ID = 'Invalid Post ID.';
const ERR_CSRF_VALIDATION = 'CSRF validation failed.';
const ERR_EMPTY_COMMENT = 'Comment cannot be empty or exceed 500 characters.';
const ERR_LOGIN_REQUIRED = 'You must be logged in to comment.';
const ERR_SUBMIT_COMMENT = 'Unable to submit comment. Please try again later.';

$user_display_name = '';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_display_name = $user['username'];
    }
}

// Function to fetch categories based on title similarity
function getCategoryFromTitle($title, $pdo)
{
    $stmt = $pdo->query("SELECT DISTINCT category, title FROM Posts");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as $category) {
        similar_text($title, $category['title'], $similarity);
        if ($similarity > 80) { // Threshold: 80%
            return $category['category'];
        }
    }
    return 'Uncategorized';
}

// Validate and sanitize post ID
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$post_id) {
    header("Location: error.php?message=" . ERR_INVALID_POST_ID);
    exit;
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle comment submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'message' => ERR_CSRF_VALIDATION]);
        exit;
    }

    // Check user login
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => ERR_LOGIN_REQUIRED]);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $comment_content = trim($_POST['comment']);

    // Validate comment content
    if (empty($comment_content) || strlen($comment_content) > 500) {
        echo json_encode(['status' => 'error', 'message' => ERR_EMPTY_COMMENT]);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())"
        );
        $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id,
            ':content' => $comment_content,
        ]);

        // Fetch the newly added comment
        $new_comment_id = $pdo->lastInsertId();
        $stmt_new_comment = $pdo->prepare(
            "SELECT c.id, c.content, c.created_at, u.username 
            FROM comments c 
            JOIN Users u ON c.user_id = u.id 
            WHERE c.id = :id"
        );
        $stmt_new_comment->execute([':id' => $new_comment_id]);
        $new_comment = $stmt_new_comment->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'comment' => $new_comment]);
        exit;
    } catch (PDOException $e) {
        error_log("Error submitting comment: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => ERR_SUBMIT_COMMENT]);
        exit;
    }
}

// Fetch post details, related posts, latest posts, and comments
try {
    $stmt = $pdo->prepare(
        "SELECT p.id, p.title, p.content, p.category, p.image_path, COUNT(l.id) AS likes_count, p.created_at 
        FROM Posts p 
        LEFT JOIN Likes l ON p.id = l.post_id 
        WHERE p.id = :post_id 
        GROUP BY p.id"
    );
    $stmt->execute([':post_id' => $post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header("Location: error.php?message=Post not found.");
        exit;
    }

    $stmt_related = $pdo->prepare(
        "SELECT id, title, category, image_path, created_at 
        FROM Posts 
        WHERE category = :category AND id != :post_id 
        ORDER BY created_at DESC LIMIT 5"
    );
    $stmt_related->execute([
        ':category' => $post['category'],
        ':post_id' => $post_id,
    ]);
    $related_posts = $stmt_related->fetchAll(PDO::FETCH_ASSOC);

    $stmt_latest = $pdo->query(
        "SELECT id, title, category, image_path, created_at 
        FROM Posts 
        ORDER BY created_at DESC LIMIT 5"
    );
    $latest_posts = $stmt_latest->fetchAll(PDO::FETCH_ASSOC);

    $stmt_comments = $pdo->prepare(
        "SELECT c.id, c.content, c.created_at, u.username 
        FROM comments c 
        JOIN Users u ON c.user_id = u.id 
        WHERE c.post_id = :post_id 
        ORDER BY c.created_at DESC"
    );
    $stmt_comments->execute([':post_id' => $post_id]);
    $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching post data: " . $e->getMessage());
    header("Location: error.php?message=Unable to fetch post data.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link rel="stylesheet" href="/jegrandia/post.css?v=<?= time(); ?>">
</head>

<body>
    <header>
        <h1>Welcome</h1>
        <nav>
            <?php if (!empty($user_display_name)): ?>
                <div class="dropdown">
                    <button><?= htmlspecialchars($user_display_name) ?></button>
                    <div class="dropdown-content">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin_dashboard.php">Admin Dashboard</a>
                        <?php endif; ?>
                        <a href="index.php">Home</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <article>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <img src="<?= htmlspecialchars($post['image_path'] ?: 'default-image.jpg') ?>" alt="Post Image">
            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
            <p><strong>Likes:</strong> <?= htmlspecialchars($post['likes_count']) ?></p>
        </article>

        <section class="related-posts">
            <h2>Related Posts</h2>
            <ul class="post-list">
                <?php foreach ($related_posts as $related): ?>
                    <li>
                        <a href="post.php?id=<?= $related['id'] ?>">
                            <img src="<?= htmlspecialchars($related['image_path'] ?: 'default-image.jpg') ?>" alt="Post Image">
                            <span><?= htmlspecialchars($related['title']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="comments">
            <h2>Comment</h2>
            <ul id="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <li>
                        <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                        <?= htmlspecialchars($comment['content']) ?>
                        <small><?= htmlspecialchars($comment['created_at']) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
                <section class="comment-box">
                    <h3>Leave a Comment</h3>
                    <form method="POST">
                        <textarea name="comment" placeholder="Write your comment here..." required></textarea>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit">Submit Comment</button>
                    </form>
                </section>
            <?php else: ?>
                <p>You must <a href="login.php">log in</a> to comment.</p>
            <?php endif; ?>
        </section>

        <section class="latest-posts">
            <h2>Latest Posts</h2>
            <ul class="post-list">
                <?php foreach ($latest_posts as $latest): ?>
                    <li>
                        <a href="post.php?id=<?= $latest['id'] ?>">
                            <img src="<?= htmlspecialchars($latest['image_path'] ?: 'default-image.jpg') ?>" alt="Post Image">
                            <span><?= htmlspecialchars($latest['title']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector(".comment-box form");
            const commentsList = document.getElementById("comments-list");

            if (form) {
                form.addEventListener("submit", function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    formData.append("ajax", "1");

                    fetch("post.php?id=<?= $post_id ?>", {
                            method: "POST",
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === "success") {
                                const newComment = document.createElement("li");
                                newComment.innerHTML = `
                                <strong>${data.comment.username}:</strong>
                                ${data.comment.content}
                                <small>${data.comment.created_at}</small>
                            `;
                                commentsList.prepend(newComment);
                                form.reset();
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            alert("An error occurred. Please try again.");
                        });
                });
            }
        });
    </script>
</body>

</html>