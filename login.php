<?php include 'config/db.php'; session_start(); ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'approved') {
            $error = "Your profile is not approved yet.";
        } else {
            $_SESSION['user'] = $user;
            header("Location: {$user['role']}/dashboard.php");
            exit;
        }
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background:rgb(80, 88, 84);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 50px;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            width: 190px;
            height: auto;
        }
        .form-container {
            background: white;
            padding: 30px 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 300px;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            margin-top: 15px;
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="logo">
    <img src="assets/logo.png" alt="GlamConnect Logo"> <!-- Change path if needed -->
</div>

<div class="form-container">
    <h2>Welcome Back</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>

</body>
</html>
