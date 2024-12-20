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

// Initialize variables
$user_display_name = '';

if (isset($_SESSION['user_id'])) {
    // Fetch the username based on user_id
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_display_name = $user['username'];
    }
}

// Fetch latest 5 posts with category names
$stmt = $pdo->prepare("
    SELECT p.id, p.title, c.name AS category, p.image_path 
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 5
");
$stmt->execute();
$latest_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch 5 most liked posts with category names
$stmt = $pdo->prepare("
    SELECT p.id, p.title, c.name AS category, p.image_path, COUNT(l.id) AS likes_count 
    FROM posts p
    LEFT JOIN likes l ON p.id = l.post_id
    LEFT JOIN categories c ON p.category_id = c.id
    GROUP BY p.id
    ORDER BY likes_count DESC
    LIMIT 5
");
$stmt->execute();
$trending_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="/jegrandia/home.css?v=<?= time(); ?>">
    <script src="/jegrandia/like.js?v=<?= time(); ?>"></script>
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
        <div class="title">
            <h1>Guide and Review: Paradise and Resort</h1>
            <h2>"Find your peace in paradise."</h2>
        </div>
        <div class="header-title-bg"></div>

        <!-- Latest Posts Section -->
        <section class="latest-posts">
            <h2>Latest Posts</h2>
            <div class="post-list">
                <?php if (!empty($latest_posts)): ?>
                    <?php foreach ($latest_posts as $post): ?>
                        <div class="post-card">
                            <img src="<?= htmlspecialchars($post['image_path'] ?: 'default-image.jpg') ?>"
                                alt="<?= htmlspecialchars($post['title']) ?> Image">
                            <h3><?= htmlspecialchars($post['title']) ?></h3>
                            <p><?= htmlspecialchars($post['category'] ?: 'Uncategorized') ?></p>
                            <a href="post.php?id=<?= $post['id'] ?>">Read More</a>
                            <div class="like-container">
                                <button type="button" class="like-button" data-post-id="<?= $post['id'] ?>">
                                    ❤ Like <span class="like-count"></span>
                                </button>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No posts available.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Trending Posts Section -->
        <section class="trending-posts">
            <h2>Trending Posts</h2>
            <div class="post-list">
                <?php if (!empty($trending_posts)): ?>
                    <?php foreach ($trending_posts as $post): ?>
                        <div class="post-card">
                            <img src="<?= htmlspecialchars($post['image_path'] ?: 'default-image.jpg') ?>"
                                alt="<?= htmlspecialchars($post['title']) ?> Image">
                            <h3><?= htmlspecialchars($post['title']) ?></h3>
                            <p><?= htmlspecialchars($post['category'] ?: 'Uncategorized') ?></p>
                            <a href="post.php?id=<?= $post['id'] ?>">Read More</a>
                            <div class="like-container">
                                <button type="button" class="like-button" data-post-id="<?= $post['id'] ?>">
                                    ❤ Like <span class="like-count"><?= htmlspecialchars($post['likes_count'] ?? 0) ?></span>
                                </button>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No trending posts available.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> Blog. All rights reserved.</p>
    </footer>
</body>

</html>