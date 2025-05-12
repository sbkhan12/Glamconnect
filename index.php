<?php
include 'config/db.php';

// Fetch featured services (limit 3)
$services = $pdo->query("SELECT name, description, price FROM services WHERE status = 'active' LIMIT 3")->fetchAll();

// Fetch featured products (limit 3)
$products = $pdo->query("SELECT name, description, price, image FROM products WHERE status = 'active' LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GlamConnect - Salon Management Platform</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #fdfdfd;
    }
    .hero {
      background: linear-gradient(to right, #f8cdda, #1d2b64);
      color: white;
      padding: 100px 0;
      text-align: center;
    }
    .features, .services, .products {
      padding: 60px 0;
    }
    .footer {
      background-color: #343a40;
      color: white;
      padding: 20px 0;
    }
    .btn-glam {
      background-color: #ff69b4;
      color: white;
      border: none;
    }
    .btn-glam:hover {
      background-color: #e0569e;
    }
  </style>
</head>
<body>

<!-- Hero Section -->
<section class="hero">
  <div class="container">
    <h1 class="display-4 fw-bold">GlamConnect</h1>
    <p class="lead">Effortlessly connect salons, customers, and owners in one smart platform.</p>
    <a href="login.php" class="btn btn-glam btn-lg mt-3">Get Started</a>
  </div>
</section>

<!-- Features Section -->
<section class="features text-center">
  <div class="container">
    <h2 class="mb-5">Key Features</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Salon Owner Dashboard</h5>
            <p class="card-text">Manage services, products, and appointments in real-time.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Customer Portal</h5>
            <p class="card-text">Browse salons, book appointments, and buy products easily.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Admin Control</h5>
            <p class="card-text">Approve users, monitor orders, and manage the entire system.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Services Section -->
<section class="services text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Popular Services</h2>
    <div class="row g-4">
      <?php foreach ($services as $srv): ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($srv['name']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($srv['description']) ?></p>
            <p class="text-muted">From ₹<?= $srv['price'] ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-4">
      <a href="/glam/all_services.php" class="btn btn-glam">See All Services</a>
    </div>
  </div>
</section>

<!-- Products Section -->
<section class="products text-center">
  <div class="container">
    <h2 class="mb-5">Top Products</h2>
    <div class="row g-4">
      <?php foreach ($products as $prod): ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= htmlspecialchars($prod['image']) ?: 'https://via.placeholder.com/300x200' ?>" class="card-img-top" alt="<?= htmlspecialchars($prod['name']) ?>">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($prod['name']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($prod['description']) ?></p>
            <p class="text-muted">₹<?= $prod['price'] ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-4">
      <a href="/glam/all_products.php" class="btn btn-glam">See All Products</a>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="text-center py-5 bg-light">
  <div class="container">
    <h2>Ready to modernize your salon experience?</h2>
    <p class="mb-4">Join GlamConnect today and streamline your salon operations.</p>
    <a href="register.php" class="btn btn-glam btn-lg">Create an Account</a>
  </div>
</section>

<!-- Footer -->
<footer class="footer text-center">
  <div class="container">
    <p class="mb-0">&copy; <?= date('Y') ?> GlamConnect. All rights reserved.</p>
  </div>
</footer>

</body>
</html>
