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

session_start();

// Check if user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);
    $image_paths = [];

    // Handle multiple image uploads
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        $target_dir = "uploads/";
        foreach ($_FILES['images']['name'] as $key => $image_name) {
            $target_file = $target_dir . basename($image_name);
            if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_file)) {
                $image_paths[] = $target_file;
            }
        }
    }

    // Insert post into the database
    $stmt = $pdo->prepare("INSERT INTO Posts (title, content, category, image_path, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt->execute([$title, $content, $category, implode(',', $image_paths)])) {
        $post_id = $pdo->lastInsertId();

        // Insert images into the database
        foreach ($image_paths as $image_path) {
            $pdo->prepare("INSERT INTO PostImages (post_id, image_path) VALUES (?, ?)")
                ->execute([$post_id, $image_path]);
        }
        $success = "Post created successfully!";
    } else {
        $error = "Failed to create post.";
    }
}

// Fetch all posts
try {
    $stmt = $pdo->query("SELECT id, title, category, created_at FROM Posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Failed to fetch posts: " . $e->getMessage());
}

// Fetch comments and likes
$comments = $pdo->query("SELECT id, post_id, content, created_at FROM Comments ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$likes = $pdo->query("SELECT id, post_id, user_id, created_at FROM Likes ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Handle post deletion
if (isset($_GET['delete_post_id'])) {
    $delete_id = intval($_GET['delete_post_id']);
    $pdo->prepare("DELETE FROM Posts WHERE id = ?")->execute([$delete_id]);
    $pdo->prepare("DELETE FROM PostImages WHERE post_id = ?")->execute([$delete_id]);
    $pdo->prepare("DELETE FROM Comments WHERE post_id = ?")->execute([$delete_id]);
    $pdo->prepare("DELETE FROM Likes WHERE post_id = ?")->execute([$delete_id]);

    header("Location: admin_dashboard.php");
    exit;
}

// Handle comment deletion
if (isset($_GET['delete_comment_id'])) {
    $comment_id = intval($_GET['delete_comment_id']);
    $pdo->prepare("DELETE FROM Comments WHERE id = ?")->execute([$comment_id]);
    header("Location: admin_dashboard.php");
    exit;
}

// Handle like deletion
if (isset($_GET['delete_like_id'])) {
    $like_id = intval($_GET['delete_like_id']);
    $pdo->prepare("DELETE FROM Likes WHERE id = ?")->execute([$like_id]);
    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/bscs4a/css/admin_dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="home.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <!-- Post Creation Section -->
        <section class="post-creation">
            <h2>Create a New Post</h2>
            <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>
            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>

                <label for="content">Content:</label>
                <textarea id="content" name="content" rows="5" required></textarea>

                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="optimization">Optimization</option>
                    <option value="troubleshooting">Troubleshooting</option>
                </select>

                <label for="images">Images:</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple>

                <button type="submit" name="create_post">Create Post</button>
            </form>
        </section>

        <!-- Manage Posts Section -->
        <section class="post-list">
            <h2>Manage Posts</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?= htmlspecialchars($post['id']) ?></td>
                                <td><?= htmlspecialchars($post['title']) ?></td>
                                <td><?= htmlspecialchars($post['category']) ?></td>
                                <td><?= htmlspecialchars($post['created_at']) ?></td>
                                <td>
                                    <a href="admin_dashboard.php?delete_post_id=<?= $post['id'] ?>" class="delete-btn">Delete Post</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No posts available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Manage Comments Section -->
        <section class="comments-list">
            <h2>Manage Comments</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Post ID</th>
                        <th>Content</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?= htmlspecialchars($comment['id']) ?></td>
                            <td><?= htmlspecialchars($comment['post_id']) ?></td>
                            <td><?= htmlspecialchars($comment['content']) ?></td>
                            <td><?= htmlspecialchars($comment['created_at']) ?></td>
                            <td>
                                <a href="admin_dashboard.php?delete_comment_id=<?= $comment['id'] ?>" class="delete-btn">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Manage Likes Section -->
        <section class="likes-list">
            <h2>Manage Likes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Post ID</th>
                        <th>User ID</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($likes as $like): ?>
                        <tr>
                            <td><?= htmlspecialchars($like['id']) ?></td>
                            <td><?= htmlspecialchars($like['post_id']) ?></td>
                            <td><?= htmlspecialchars($like['user_id']) ?></td>
                            <td><?= htmlspecialchars($like['created_at']) ?></td>
                            <td>
                                <a href="admin_dashboard.php?delete_like_id=<?= $like['id'] ?>" class="delete-btn">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>