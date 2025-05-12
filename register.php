<?php include 'config/db.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // 'owner' or 'customer'

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role]);
    echo "<div class='success'>Registration successful. Please wait for approval if you're a salon owner.</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
                 background:rgb(88, 80, 87);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 50px;
        }
        .welcome {
            font-size: 30px;
            color:white;
            margin-bottom: 50px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            /* background: linear-gradient(to right, #ff7e5f, #feb47b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;   
            animation: fadeIn 2s; */

        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.1);
            width: 300px;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color:rgb(216, 31, 31);
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color:rgb(211, 15, 15);
        }
        .success {
            margin-top: 10px;
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="welcome">Welcome to GlamConnect – Please Register Below</div>

<div class="form-container">
    <h2>Register</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="role">
            <option value="customer">Customer</option>
            <option value="owner">Salon Owner</option>
        </select><br>
        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>
