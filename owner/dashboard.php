<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header('Location: ../login.php');
    exit;
}

$owner_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT * FROM salons WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$salon = $stmt->fetch();

// Handle salon registration
if (!$salon && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $stmt = $pdo->prepare("INSERT INTO salons (owner_id, name, address, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$owner_id, $name, $address, $phone]);

    header('Location: dashboard.php');
    exit;
}

if ($salon) {
    $salon_id = $salon['id'];

    // Products
    $products = $pdo->prepare("SELECT * FROM products WHERE salon_id = ?");
    $products->execute([$salon_id]);
    $products = $products->fetchAll();

    if (isset($_POST['add_product'])) {
        $product_name = $_POST['product_name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $image = $_FILES['image']['name'];
        $image_path = 'assets/images/' . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

        $stmt = $pdo->prepare("INSERT INTO products (salon_id, name, description, price, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$salon_id, $product_name, $description, $price, $image_path]);
        header('Location: dashboard.php');
        exit;
    }

    if (isset($_GET['delete_product'])) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_GET['delete_product']]);
        header('Location: dashboard.php');
        exit;
    }

    // Services
    $services = $pdo->prepare("SELECT * FROM services WHERE salon_id = ?");
    $services->execute([$salon_id]);
    $services = $services->fetchAll();

    if (isset($_POST['add_service'])) {
        $stmt = $pdo->prepare("INSERT INTO services (salon_id, name, description, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$salon_id, $_POST['service_name'], $_POST['description'], $_POST['price']]);
        header('Location: dashboard.php');
        exit;
    }

    // Appointments
    $appointments = $pdo->prepare("SELECT a.id, u.name AS customer_name, s.name AS service_name, a.appointment_date, a.status
                                   FROM appointments a
                                   JOIN users u ON a.customer_id = u.id
                                   JOIN services s ON a.service_id = s.id
                                   WHERE s.salon_id = ?
                                   ORDER BY a.appointment_date DESC");
    $appointments->execute([$salon_id]);
    $appointments = $appointments->fetchAll();

    if (isset($_GET['approve_appointment'])) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'approved' WHERE id = ?");
        $stmt->execute([$_GET['approve_appointment']]);
        header('Location: dashboard.php');
        exit;
    }

    if (isset($_GET['cancel_appointment'])) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$_GET['cancel_appointment']]);
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Salon Owner Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color:rgb(111, 54, 146);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            padding: 30px;
            max-width: 1000px;
            margin: auto;
        }
        h3, h4 {
            color: #333;
        }
          h2 {
            color: white;
        }
        form {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        input[type="text"], input[type="number"], input[type="file"], textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background-color: #6a1b9a;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #4a148c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        table, th, td {
            border: 1px solid #e0e0e0;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        img {
            width: 100px;
            height: auto;
        }
        a {
            color: #6a1b9a;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .section {
            margin-top: 40px;
        }
        header {
    position: relative;
}
    </style>
</head>
<body>


<header>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?> <br>Salon Owner Dashboard</h2>
  <a href="../logout.php" style="
        position: absolute;
        top: 40px;
        right: 50px;
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
    <?php if (!$salon): ?>
        <div class="section">
            <h3>Register Your Salon</h3>
            <form method="POST">
                <label>Salon Name:</label>
                <input type="text" name="name" required>

                <label>Address:</label>
                <textarea name="address" required></textarea>

                <label>Phone:</label>
                <input type="text" name="phone" required>

                <button type="submit">Register Salon</button>
            </form>
        </div>
    <?php else: ?>
        <div class="section">
            <h3>Your Salon: <?= htmlspecialchars($salon['name']) ?></h3>
        </div>

        <div class="section">
            <h3>Manage Products</h3>
            <form method="POST" enctype="multipart/form-data">
                <label>Product Name:</label>
                <input type="text" name="product_name" required>

                <label>Description:</label>
                <textarea name="description" required></textarea>

                <label>Price:</label>
                <input type="number" name="price" required>

                <label>Image:</label>
                <input type="file" name="image" required>

                <button type="submit" name="add_product">Add Product</button>
            </form>

            <h4>Your Products</h4>
            <table>
                <tr>
                    <th>Product Name</th><th>Description</th><th>Price</th><th>Image</th><th>Action</th>
                </tr>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['description']) ?></td>
                    <td>$<?= $product['price'] ?></td>
                    <td><img src="<?= htmlspecialchars($product['image']) ?>"></td>
                    <td><a href="?delete_product=<?= $product['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="section">
            <h3>Manage Services</h3>
            <form method="POST">
                <label>Service Name:</label>
                <input type="text" name="service_name" required>

                <label>Description:</label>
                <textarea name="description" required></textarea>

                <label>Price:</label>
                <input type="number" name="price" required>

                <button type="submit" name="add_service">Add Service</button>
            </form>

            <h4>Available Services</h4>
            <table>
                <tr>
                    <th>Service Name</th><th>Description</th><th>Price</th>
                </tr>
                <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= htmlspecialchars($service['name']) ?></td>
                    <td><?= htmlspecialchars($service['description']) ?></td>
                    <td>$<?= $service['price'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="section">
            <h3>Customer Appointments</h3>
            <table>
                <tr>
                    <th>Customer Name</th><th>Service</th><th>Date</th><th>Status</th><th>Action</th>
                </tr>
                <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td><?= htmlspecialchars($appointment['customer_name']) ?></td>
                    <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                    <td><?= $appointment['appointment_date'] ?></td>
                    <td><?= ucfirst(htmlspecialchars($appointment['status'])) ?></td>
                    <td>
                        <?php if ($appointment['status'] === 'pending'): ?>
                            <a href="?approve_appointment=<?= $appointment['id'] ?>">Approve</a> |
                            <a href="?cancel_appointment=<?= $appointment['id'] ?>">Cancel</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
