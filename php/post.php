<?php

$host = 'localhost';
$dbname = 'blogdb';
$username = 'root';
$password = '';
if(isset($_SESSION[''])) {
    session_start(); // Start the session
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$post_id = $_GET['id'] ?? null;
if (!$post_id || !is_numeric($post_id)) {
    die("Invalid Post ID!");
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
        die("Post not found!");
    }

    // Fetch related posts
    $stmt_related = $pdo->prepare("
        SELECT id, title, content, category, image_path, created_at 
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
        SELECT id, title, content, category, image_path, created_at 
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
    die("Error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link rel="stylesheet" href="/bscs4a/css/post.css">
</head>
<body>
<header>
    <nav>
        <a href="home.php">Home</a>
    </nav>
</header>

<main>
    <article>
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image">
        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($post['category']) ?></p>
        <p><strong>Likes:</strong> <span id="like-count"><?= htmlspecialchars($post['likes_count']) ?></span></p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button id="like-button" data-post-id="<?= $post['id'] ?>">
                <?= $user_has_liked ? 'Unlike' : 'Like' ?>
            </button>
        <?php endif; ?>
    </article>

    <section class="related-posts">
        <h2>Related Posts</h2>
        <ul>
            <?php foreach ($related_posts as $related): ?>
                <li>
                    <a href="post.php?id=<?= $related['id'] ?>">
                        <?= htmlspecialchars($related['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="comments">
        <h2>Comments</h2>
       
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                    <?= htmlspecialchars($comment['content']) ?>
                    <small><?= htmlspecialchars($comment['created_at']) ?></small>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="post.php?id=<?= $post_id ?>&delete=<?= $comment['id'] ?>">Delete</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="latest-posts">
    <h2>Latest Posts</h2>
    <ul>
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
<script src="post.js"></script>
</body>
</html>
