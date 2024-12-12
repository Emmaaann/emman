<?php
$host = 'localhost';
$dbname = 'BlogDB';
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

$category = $_GET['category'] ?? 'optimization'; // Default category: optimization
$sort = $_GET['sort'] ?? 'date'; // Default sort: date
$page = $_GET['page'] ?? 1; // Default page: 1
$posts_per_page = 10;
$offset = ($page - 1) * $posts_per_page;

// Validate category
$valid_categories = ['optimization', 'troubleshooting'];
if (!in_array($category, $valid_categories)) {
    die("Invalid category!");
}

// Sorting options
$order_by = $sort === 'likes' ? 'likes_count DESC' : 'created_at DESC';

try {
    // Fetch total number of posts for pagination
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM Posts WHERE category = :category");
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt->execute();
    $total_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_posts = $total_result['total'] ?? 0;
    $total_pages = ceil($total_posts / $posts_per_page);

    // Fetch posts
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.image_path, COUNT(l.id) AS likes_count 
        FROM Posts p
        LEFT JOIN Likes l ON p.id = l.post_id
        WHERE p.category = :category
        GROUP BY p.id
        ORDER BY $order_by
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $posts_per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching posts: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst(htmlspecialchars($category)) ?> Posts</title>
    <link rel="stylesheet" href="/bscs4a/css/category.css">
</head>
<body>
    <header>
        <h1><?= ucfirst(htmlspecialchars($category)) ?> Posts</h1>
        <nav>
            <a href="home.php">Home</a>
            <a href="category.php?category=optimization">Optimization</a>
        </nav>
    </header>

    <main>
        <div class="sort-options">
            <a href="?category=<?= htmlspecialchars($category) ?>&sort=date" class="<?= $sort === 'date' ? 'active' : '' ?>">Sort by Date</a>
            <a href="?category=<?= htmlspecialchars($category) ?>&sort=likes" class="<?= $sort === 'likes' ? 'active' : '' ?>">Sort by Likes</a>
        </div>

        <section class="post-grid">
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image">
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <p>Likes: <?= htmlspecialchars($post['likes_count']) ?></p>
                        <a href="php/post.php?id=<?= $post['id'] ?>">Read More</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No posts available in this category.</p>
            <?php endif; ?>
        </section>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?category=<?= htmlspecialchars($category) ?>&sort=<?= htmlspecialchars($sort) ?>&page=<?= $page - 1 ?>">&laquo; Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?category=<?= htmlspecialchars($category) ?>&sort=<?= htmlspecialchars($sort) ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?category=<?= htmlspecialchars($category) ?>&sort=<?= htmlspecialchars($sort) ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
