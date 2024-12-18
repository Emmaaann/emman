<?php
$host = 'localhost';
$dbname = 'blogdb';
$username = 'root';
$password = '';

// Establish database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
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

// Function to generate a unique filename
function generateUniqueFilename($originalName)
{
    return uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
}

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = isset($_POST['category']) ? trim($_POST['category']) : null; // Category is optional
    $image_paths = [];

    // Validate required inputs
    if (empty($title) || empty($content)) {
        $error = "Title and Content are required.";
    } else {
// Handle multiple image uploads
if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
    $target_dir = "uploads/";

    // Check if the directory exists, if not, create it
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create the directory with proper permissions
    }

    foreach ($_FILES['images']['name'] as $key => $image_name) {
        // Sanitize the file name to avoid any issues
        $safe_image_name = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $image_name);
        $target_file = $target_dir . basename($safe_image_name);
        $file_type = pathinfo($target_file, PATHINFO_EXTENSION);

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_type), $allowed_types)) {
            $error = "Invalid file type for $safe_image_name.";
            continue;
        }

        // Move file to target directory
        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_file)) {
            $image_paths[] = $target_file;
        } else {
            $error = "Failed to upload $safe_image_name.";
        }
    }
}


        // Insert post into the database
        if (empty($error)) {
            $stmt = $pdo->prepare(
                "INSERT INTO Posts (title, content, category_id, image_path, created_at) VALUES (?, ?, ?, ?, NOW())"
            );

            // Handle optional category
            $category_value = $category ? $category : null;

            if ($stmt->execute([$title, $content, $category_value, implode(',', $image_paths)])) {
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
    }
}



// Fetch categories
try {
    $categories = $pdo->query("SELECT id, name FROM Categories")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Failed to fetch categories: " . $e->getMessage());
}

// Fetch grouped posts
$stmt = $pdo->query("
    SELECT c.name AS category_name, p.id, p.title, p.created_at
    FROM Posts p
    JOIN Categories c ON p.category_id = c.id
    ORDER BY c.name, p.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments and likes
$comments = $pdo->query("SELECT id, post_id, content, created_at FROM Comments ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$likes = $pdo->query("SELECT id, post_id, user_id, created_at FROM Likes ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Handle deletions
if (isset($_GET['delete_post_id'])) {
    $delete_id = intval($_GET['delete_post_id']);
    $pdo->prepare("DELETE FROM Posts WHERE id = ?")->execute([$delete_id]);
    $pdo->prepare("DELETE FROM PostImages WHERE post_id = ?")->execute([$delete_id]);
    $pdo->prepare("DELETE FROM Comments WHERE post_id = ?")->execute([$delete_id]);
    $pdo->prepare("DELETE FROM Likes WHERE post_id = ?")->execute([$delete_id]);

    header("Location: admin_dashboard.php");
    exit;
}

if (isset($_GET['delete_comment_id'])) {
    $comment_id = intval($_GET['delete_comment_id']);
    $pdo->prepare("DELETE FROM Comments WHERE id = ?")->execute([$comment_id]);
    header("Location: admin_dashboard.php");
    exit;
}

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
        <section class="post-creation">
            <h2>Create a New Post</h2>
            <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>
            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>

                <label for="content">Content:</label>
                <textarea id="content" name="content" rows="5" required></textarea>

                <label for="category">Category (optional):</label>
                <select id="category" name="category">
                    <option value="">None</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>



                <label for="images">Images:</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple>

                <button type="submit" name="create_post">Create Post</button>
            </form>
        </section>

        <section class="post-list">
            <h2>Manage Posts</h2>
            <?php
            $current_category = '';
            foreach ($posts as $post):
                if ($current_category !== $post['category_name']):
                    if ($current_category !== '') echo '</tbody></table>';
                    $current_category = $post['category_name'];
            ?>
                    <h3><?= htmlspecialchars($current_category) ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php endif; ?>
                        <tr>
                            <td><?= htmlspecialchars($post['id']) ?></td>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= htmlspecialchars($post['created_at']) ?></td>
                            <td>
                                <a href="admin_dashboard.php?delete_post_id=<?= $post['id'] ?>" class="delete-btn">Delete Post</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                        </tbody>
                    </table>
        </section>

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