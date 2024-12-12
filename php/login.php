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



$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember_me = isset($_POST['remember_me']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = "Please fill in both fields.";
    } else {
        try {
            // Check username and hashed password
            $stmt = $pdo->prepare("SELECT id, password, role FROM Users WHERE username = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verify password using password_verify
                if ($password === $user['password']) {
                    // Set session variables
                    if(!isset($_SESSION[''])){
                        session_start();
                    }
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $user['role'];

                    // Remember me functionality
                    if ($remember_me) {
                        setcookie('user_id', $user['id'], time() + (86400 * 30), "/");
                        setcookie('username', $username, time() + (86400 * 30), "/");
                        setcookie('role', $user['role'], time() + (86400 * 30), "/");
                    }

                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: home.php");
                    }
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No account found with that username.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/bscs4a/css/login.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <label>
                <input type="checkbox" name="remember_me"> Remember me
            </label>
            
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
