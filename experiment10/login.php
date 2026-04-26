<?php
session_start();
require_once 'db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Exp 10</title>
    <style>
        body { background: #0f172a; color: white; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #1e293b; padding: 40px; border-radius: 12px; width: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { margin-bottom: 20px; text-align: center; color: #8b5cf6; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #8b5cf6; border: none; color: white; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .error { color: #f87171; margin-bottom: 15px; font-size: 0.9rem; }
        .link { text-align: center; margin-top: 15px; font-size: 0.85rem; color: #94a3b8; }
        a { color: #8b5cf6; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>TaskHub Login</h1>
        <?php if($error): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="link">New here? <a href="register.php">Create an account</a></div>
    </div>
</body>
</html>
