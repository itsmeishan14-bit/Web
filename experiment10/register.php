<?php
require_once 'db.php';
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Username already exists.";
            } else {
                $error = "Something went wrong.";
            }
        }
    } else {
        $error = "Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Exp 10</title>
    <style>
        body { background: #0f172a; color: white; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #1e293b; padding: 40px; border-radius: 12px; width: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { margin-bottom: 20px; text-align: center; color: #8b5cf6; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #8b5cf6; border: none; color: white; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .error { color: #f87171; margin-bottom: 15px; font-size: 0.9rem; }
        .success { color: #4ade80; margin-bottom: 15px; font-size: 0.9rem; }
        .link { text-align: center; margin-top: 15px; font-size: 0.85rem; color: #94a3b8; }
        a { color: #8b5cf6; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Join TaskHub</h1>
        <?php if($error): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>
        <?php if($success): ?> <div class="success"><?php echo $success; ?></div> <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create Account</button>
        </form>
        <div class="link">Already have an account? <a href="login.php">Login</a></div>
    </div>
</body>
</html>
