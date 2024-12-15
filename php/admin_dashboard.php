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
    $image_path = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        $image_path = $target_file;
    }

    // Insert post into the database
    $stmt = $conn->prepare("INSERT INTO Posts (title, content, category, image_path, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param('ssss', $title, $content, $category, $image_path);

    if ($stmt->execute()) {
        $success = "Post created successfully!";
    } else {
        $error = "Failed to create post.";
    }
}

// Fetch all posts
$result = $conn->query("SELECT id, title, category, created_at FROM Posts ORDER BY created_at DESC");
$posts = $result->fetch_all(MYSQLI_ASSOC);

// Handle post deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM Posts WHERE id = ?");
    $stmt->bind_param('i', $delete_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/bscs4a/css/admin_dashboard.css">
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

                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*">

                <button type="submit" name="create_post">Create Post</button>
            </form>
        </section>

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
                                    <a href="admin_dashboard.php?delete_id=<?= $post['id'] ?>" class="delete-btn">Delete</a>
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
    </main>
</body>
</html>

