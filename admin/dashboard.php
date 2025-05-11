<?php
session_start();
include '../config/db.php';

// Check if logged-in user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Approve/disapprove logic
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action']; // approve or disapprove
    $status = $action === 'approve' ? 'approved' : 'disapproved';

    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    $user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user->execute([$id]);
    $u = $user->fetch();

    if ($u['role'] === 'owner') {
        $pdo->prepare("UPDATE salons SET status = ? WHERE owner_id = ?")->execute([$status, $id]);
    }

    header('Location: dashboard.php');
    exit;
}

// Summary counts
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_owners = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'")->fetchColumn();
$total_saloons = $pdo->query("SELECT COUNT(*) FROM salons")->fetchColumn();
$total_services = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_appointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Filters
$filterRole = $_GET['role'] ?? '';
$filterStatus = $_GET['status'] ?? '';

$where = [];
$params = [];
if ($filterRole !== '') {
    $where[] = "u.role = ?";
    $params[] = $filterRole;
}
if ($filterStatus !== '') {
    $where[] = "u.status = ?";
    $params[] = $filterStatus;
}

$whereSQL = count($where) ? ' AND ' . implode(' AND ', $where) : '';

$users = $pdo->prepare("SELECT u.*, s.name AS salon_name FROM users u LEFT JOIN salons s ON u.id = s.owner_id WHERE u.role != 'admin' $whereSQL");
$users->execute($params);
$users = $users->fetchAll();

$saloons = $pdo->query("SELECT s.*, u.name AS owner_name FROM salons s JOIN users u ON s.owner_id = u.id")->fetchAll();
$services = $pdo->query("SELECT s.*, sa.name AS salon_name FROM services s JOIN salons sa ON s.salon_id = sa.id")->fetchAll();
$products = $pdo->query("SELECT p.*, sa.name AS salon_name FROM products p JOIN salons sa ON p.salon_id = sa.id")->fetchAll();
$appointments = $pdo->query("SELECT a.*, u.name AS customer_name, s.name AS service_name FROM appointments a JOIN users u ON a.customer_id = u.id JOIN services s ON a.service_id = s.id")->fetchAll();
$orders = $pdo->query("SELECT o.*, u.name AS customer_name, p.name AS product_name FROM orders o JOIN users u ON o.customer_id = u.id JOIN products p ON o.product_id = p.id")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
         header {
            background-color:rgb(111, 54, 146);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        h2, h3 {
            color: #343a40;
        }
          h2 {
            color: white;
        }
        .summary-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .table thead {
            background-color: #343a40;
            color: white;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }
        .btn-sm {
            font-size: 0.75rem;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        
    </style>
</head>
<header>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?> <br>Salon Owner Dashboard</h2>
  <a href="../logout.php" style="
        position: absolute;
        top: 80px;
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

<body class="p-4">
<div class="container">

    <div class="row mb-4">
        <?php
        $stats = [
            'Total Customers' => $total_users,
            'Salon Owners' => $total_owners,
            'Saloons' => $total_saloons,
            'Services' => $total_services,
            'Products' => $total_products,
            'Appointments' => $total_appointments,
            'Orders' => $total_orders
        ];
        foreach ($stats as $label => $value): ?>
            <div class="col-md-3">
                <div class="summary-box text-center">
                    <h5 class="text-primary"><?= $label ?></h5>
                    <h4><?= $value ?></h4>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h3>Registered Users</h3>
    <form method="GET" class="row g-3 mb-3">
        <div class="col-auto">
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="customer" <?= $filterRole === 'customer' ? 'selected' : '' ?>>Customer</option>
                <option value="owner" <?= $filterRole === 'owner' ? 'selected' : '' ?>>Owner</option>
            </select>
        </div>
        <div class="col-auto">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="approved" <?= $filterStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="disapproved" <?= $filterStatus === 'disapproved' ? 'selected' : '' ?>>Disapproved</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="dashboard.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Salon Name</th><th>Status</th><th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role'] ?></td>
                <td><?= htmlspecialchars($user['salon_name'] ?? '-') ?></td>
                <td><?= $user['status'] ?></td>
                <td>
                    <?php if ($user['status'] !== 'approved'): ?>
                        <a href="?action=approve&id=<?= $user['id'] ?>" class="btn btn-success btn-sm">✅ Approve</a>
                    <?php endif; ?>
                    <?php if ($user['status'] === 'approved'): ?>
                        <a href="?action=disapprove&id=<?= $user['id'] ?>" class="btn btn-danger btn-sm">🚫 Disapprove</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Registered Saloons</h3>
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr><th>ID</th><th>Name</th><th>Owner</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($saloons as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['owner_name']) ?></td>
                <td><?= $s['status'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Services</h3>
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr><th>ID</th><th>Name</th><th>Salon</th><th>Price</th></tr>
        </thead>
        <tbody>
        <?php foreach ($services as $srv): ?>
            <tr>
                <td><?= $srv['id'] ?></td>
                <td><?= htmlspecialchars($srv['name']) ?></td>
                <td><?= htmlspecialchars($srv['salon_name']) ?></td>
                <td><?= $srv['price'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Products</h3>
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr><th>ID</th><th>Name</th><th>Salon</th><th>Price</th></tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['salon_name']) ?></td>
                <td><?= $p['price'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Appointments</h3>
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr><th>ID</th><th>Customer</th><th>Service</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($a['customer_name']) ?></td>
                <td><?= htmlspecialchars($a['service_name']) ?></td>
                <td><?= $a['appointment_date'] ?></td>
                <td><?= $a['status'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Orders</h3>
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr><th>ID</th><th>Customer</th><th>Product</th><th>Qty</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                <td><?= htmlspecialchars($o['product_name']) ?></td>
                <td><?= $o['quantity'] ?></td>
                <td><?= $o['order_date'] ?></td>
                <td><?= $o['status'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
