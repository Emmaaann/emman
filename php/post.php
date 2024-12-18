<?php
session_start(); // Start the session

$host = 'localhost';
$dbname = 'blogdb';
$username = 'root';
$password = '';

// Establish database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: Unable to connect to the database.");
}

// Validate and get the post ID
$post_id = $_GET['id'] ?? null;
if (!$post_id || !is_numeric($post_id)) {
    die("Invalid Post ID.");
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        die("You must be logged in to comment.");
    }

    $user_id = $_SESSION['user_id'];
    $comment_content = trim($_POST['comment']);

    if (!empty($comment_content)) {
        try {
            $stmt_add_comment = $pdo->prepare("
                INSERT INTO Comments (post_id, user_id, content, created_at) 
                VALUES (:post_id, :user_id, :content, NOW())
            ");
            $stmt_add_comment->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt_add_comment->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_add_comment->bindParam(':content', $comment_content, PDO::PARAM_STR);
            $stmt_add_comment->execute();
            header("Location: post.php?id=$post_id"); // Refresh the page
            exit;
        } catch (PDOException $e) {
            die("Error: Unable to submit comment.");
        }
    } else {
        $error_message = "Comment cannot be empty.";
    }
}

try {
    // Fetch the post details
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.content, p.category, p.image_path, 
               COUNT(l.id) AS likes_count, p.created_at
        FROM Posts p
        LEFT JOIN Likes l ON p.id = l.post_id
        WHERE p.id = :post_id
        GROUP BY p.id
    ");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        die("Error: Post not found.");
    }

    // Fetch related posts
    $stmt_related = $pdo->prepare("
        SELECT id, title, category, image_path, created_at 
        FROM Posts 
        WHERE category = :category AND id != :post_id
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt_related->bindParam(':category', $post['category'], PDO::PARAM_STR);
    $stmt_related->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt_related->execute();
    $related_posts = $stmt_related->fetchAll(PDO::FETCH_ASSOC);

    // Fetch latest posts
    $stmt_latest = $pdo->prepare("
        SELECT id, title, category, image_path, created_at 
        FROM Posts 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt_latest->execute();
    $latest_posts = $stmt_latest->fetchAll(PDO::FETCH_ASSOC);

    // Fetch comments
    $stmt_comments = $pdo->prepare("
        SELECT c.id, c.content, c.created_at, u.username 
        FROM Comments c
        JOIN Users u ON c.user_id = u.id
        WHERE c.post_id = :post_id
        ORDER BY c.created_at DESC
    ");
    $stmt_comments->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt_comments->execute();
    $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: Unable to fetch data.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link rel="stylesheet" href="/bscs4a/css/post.css?v=<?= time(); ?>">
</head>

<body>
    <header>
        <nav>
            <a href="home.php">Home</a>
        </nav>
    </header>

    <main>
        <!-- Post Details -->
        <article>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image">
            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($post['category']) ?></p>
            <p><strong>Likes:</strong> <span id="like-count"><?= htmlspecialchars($post['likes_count']) ?></span></p>
        </article>

        <!-- Related Posts -->
        <section class="related-posts">
            <h2>Related Posts</h2>
            <ul class="post-list">
                <?php foreach ($related_posts as $related): ?>
                    <li>
                        <a href="post.php?id=<?= $related['id'] ?>">
                            <img src="<?= htmlspecialchars($related['image_path']) ?>" alt="Post Image">
                            <span><?= htmlspecialchars($related['title']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- Comments Section -->
        <section class="comments">
            <h2>Comments</h2>
            <ul>
                <?php foreach ($comments as $comment): ?>
                    <li>
                        <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                        <?= htmlspecialchars($comment['content']) ?>
                        <small><?= htmlspecialchars($comment['created_at']) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Comment Form -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                // Check if the user has already commented on this post
                $stmt = $pdo->prepare("SELECT content FROM Comments WHERE user_id = :user_id AND post_id = :post_id");
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
                $stmt->execute();
                $user_comment = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <?php if ($user_comment): ?>
                    <p><strong>Your Comment:</strong> <?= htmlspecialchars($user_comment['content']) ?></p>
                <?php else: ?>
                    <section class="comment-box">
                        <h3>Leave a Comment</h3>
                        <form method="POST" action="submit_comment.php">
                            <textarea name="comment" placeholder="Write your comment here..." required></textarea>
                            <input type="hidden" name="post_id" value="<?= htmlspecialchars($post_id) ?>">
                            <div class="btn-container">
                                <button type="submit">Submit Comment</button>
                            </div>
                        </form>
                    </section>
                <?php endif; ?>
            <?php else: ?>
                <p>You must <a href="login.php">log in</a> to comment.</p>
            <?php endif; ?>
        </section>


        <!-- Comment Input Section -->
        <section class="comment-box">
            <h3>Leave a Comment</h3>
            <form method="POST" action="submit_comment.php">
                <textarea name="comment" placeholder="Write your comment here..." required></textarea>
                <div class="btn-container">
                    <button type="submit">Submit Comment</button>
                </div>
            </form>
        </section>

        <!-- Latest Posts -->
        <section class="latest-posts">
            <h2>Latest Posts</h2>
            <ul class="post-list">
                <?php foreach ($latest_posts as $latest): ?>
                    <li>
                        <a href="post.php?id=<?= $latest['id'] ?>">
                            <img src="<?= htmlspecialchars($latest['image_path']) ?>" alt="Post Image">
                            <span><?= htmlspecialchars($latest['title']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>

</body>

</html>