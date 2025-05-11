<?php

session_start();
include '../config/db.php';

// Check if logged-in user is a customer
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header('Location: ../login.php');
    exit;
}

$customer_id = $_SESSION['user']['id'];

// Fetch all products from all salons
$products = $pdo->query("SELECT p.id, p.name, p.description, p.price, p.image, s.name AS salon_name FROM products p JOIN salons s ON p.salon_id = s.id")->fetchAll();

// Fetch all services from salons
$services = $pdo->query("SELECT s.id, s.name AS service_name, s.description, s.price, sa.name AS salon_name
                         FROM services s
                         JOIN salons sa ON s.salon_id = sa.id")->fetchAll();

// Handle order placement
if (isset($_GET['order'])) {
    $product_id = $_GET['order'];

    // Example: for simplicity, quantity will always be 1
    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, product_id, quantity) VALUES (?, ?, 1)");
    $stmt->execute([$customer_id, $product_id]);

    $_SESSION['message'] = "Order placed successfully!";
    header("Location: dashboard.php");
    exit;
}

// Handle appointment booking
if (isset($_POST['book_appointment'])) {
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];

    $stmt = $pdo->prepare("INSERT INTO appointments (customer_id, service_id, appointment_date) VALUES (?, ?, ?)");
    $stmt->execute([$customer_id, $service_id, $appointment_date]);

    $_SESSION['message'] = "Appointment booked successfully!";
    header("Location: dashboard.php");
    exit;
}

// Fetch order history for the logged-in customer
$orders = $pdo->prepare("SELECT o.id, p.name AS product_name, o.quantity, o.order_date, s.name AS salon_name, o.status
                         FROM orders o
                         JOIN products p ON o.product_id = p.id
                         JOIN salons s ON p.salon_id = s.id
                         WHERE o.customer_id = ?
                         ORDER BY o.order_date DESC");
$orders->execute([$customer_id]);
$order_history = $orders->fetchAll();

// Fetch customer's past appointments
$appointments = $pdo->prepare("SELECT a.id, s.name AS service_name, a.appointment_date, sa.name AS salon_name, a.status
                               FROM appointments a
                               JOIN services s ON a.service_id = s.id
                               JOIN salons sa ON s.salon_id = sa.id
                               WHERE a.customer_id = ?
                               ORDER BY a.appointment_date DESC");
$appointments->execute([$customer_id]);
$appointments = $appointments->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        h2 {
            margin: 0;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            max-width: 1200px;
        }

        .message {
            color: green;
            font-weight: bold;
            margin: 20px 0;
            padding: 10px;
            background-color: #e7f9e7;
            border: 1px solid #d4edda;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        td img {
            max-width: 100px;
        }

        .action-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .action-btn:hover {
            background-color: #45a049;
        }

        form {
            margin-top: 10px;
        }

        input[type="datetime-local"], button {
            padding: 10px;
            font-size: 14px;
            margin-right: 10px;
        }

        input[type="datetime-local"] {
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 5px;
        }

        button:hover {
            background-color: #45a049;
        }

        .table-container {
            margin-top: 30px;
        }

        .table-container h3 {
            margin-top: 40px;
            color: #333;
        }

        @media screen and (max-width: 768px) {
            table {
                width: 100%;
                font-size: 14px;
            }

            td, th {
                padding: 8px;
            }

            form {
                display: block;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

<header>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></h2>
    <a href="../logout.php" style="
        position: absolute;
        top: 20px;
        right: 30px;
        background:rgb(151, 49, 45);
        color: #fff;
        padding: 8px 18px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        font-size: 16px;
    ">Logout</a>
</header>

<div class="container">

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?= htmlspecialchars($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="table-container">
        <h3>Available Products</h3>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Salon</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['description']) ?></td>
                    <td>$<?= $product['price'] ?></td>
                    <td><?= htmlspecialchars($product['salon_name']) ?></td>
                    <td><a class="action-btn" href="?order=<?= $product['id'] ?>">Order</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h3>Your Order History</h3>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Order Date</th>
                    <th>Salon</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($order_history) > 0): ?>
                    <?php foreach ($order_history as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['product_name']) ?></td>
                        <td><?= $order['quantity'] ?></td>
                        <td><?= $order['order_date'] ?></td>
                        <td><?= htmlspecialchars($order['salon_name']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($order['status'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h3>Available Services</h3>
        <table>
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Salon</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= htmlspecialchars($service['service_name']) ?></td>
                    <td><?= htmlspecialchars($service['description']) ?></td>
                    <td>$<?= $service['price'] ?></td>
                    <td><?= htmlspecialchars($service['salon_name']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                            <input type="datetime-local" name="appointment_date" required>
                            <button type="submit" name="book_appointment">Book Appointment</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h3>Your Appointment History</h3>
        <table>
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Appointment Date</th>
                    <th>Salon</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                    <td><?= $appointment['appointment_date'] ?></td>
                    <td><?= htmlspecialchars($appointment['salon_name']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($appointment['status'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
