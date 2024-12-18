<?php
if (!isset($_SESSION)) {
    session_start(); // Start the session if not already started
}

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

$user_display_name = '';

if (isset($_SESSION['user_id'])) {
    // Fetch the username from the database based on user_id
    $stmt = $pdo->prepare("SELECT username FROM Users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_display_name = $user['username']; // Set the username to display
    }
}

// Fetch latest 5 posts
$stmt = $pdo->prepare("SELECT id, title, category, image_path FROM Posts ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$latest_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch 5 most liked posts
$stmt = $pdo->prepare("
    SELECT p.id, p.title, p.category, p.image_path, COUNT(l.id) AS likes_count 
    FROM Posts p
    LEFT JOIN Likes l ON p.id = l.post_id
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
    <link rel="stylesheet" href="/bscs4a/css/home.css?v=<?php echo time(); ?>">
</head>
<body>
<header>
    <h1></h1>
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
        <section class="latest-posts">
            <h2>Latest Posts</h2>
            <div class="post-list">
                <?php if (count($latest_posts) > 0): ?>
                    <?php foreach ($latest_posts as $post): ?>
                        <div class="post-card">
                            <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image">
                            <h3><?= htmlspecialchars($post['title']) ?></h3>
                            <p><?= htmlspecialchars($post['category']) ?></p>
                            <a href="post.php?id=<?= $post['id'] ?>">Read More</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No posts available.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="trending-posts">
            <h2>Trending Posts</h2>
            <div class="post-list">
                <?php if (count($trending_posts) > 0): ?>
                    <?php foreach ($trending_posts as $post): ?>
                        <div class="post-card">
                            <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image">
                            <h3><?= htmlspecialchars($post['title']) ?></h3>
                            <p><?= htmlspecialchars($post['category']) ?></p>
                            <a href="post.php?id=<?= $post['id'] ?>">Read More</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No posts available.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2023 Blog. All rights reserved.</p>
    </footer>
</body>
</html>
