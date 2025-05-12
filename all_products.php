<?php
include 'config/db.php';
$products = $pdo->query("SELECT p.*, salons.name AS salon_name FROM products p JOIN salons ON p.salon_id = salons.id WHERE p.status = 'active'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Products - GlamConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f9f9f9;
      font-family: 'Segoe UI', sans-serif;
    }

    .container {
      max-width: 1200px;
    }

    .card {
      transition: transform 0.2s ease-in-out;
    }

    .card:hover {
      transform: scale(1.02);
    }

    .btn-glam {
      background-color: #ff69b4;
      color: #fff;
      border: none;
    }

    .btn-glam:hover {
      background-color: #e0569e;
    }

    .card-img-top {
      height: 200px;
      object-fit: cover;
      border-bottom: 1px solid #eee;
    }

    h2 {
      color: #1d2b64;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="container py-5">
  <h2 class="mb-4 text-center">All Available Products</h2>
  <div class="row g-4">
    <?php foreach ($products as $prod): ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= htmlspecialchars($prod['image']) ?: 'https://via.placeholder.com/300x200' ?>" class="card-img-top" alt="<?= htmlspecialchars($prod['name']) ?>">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($prod['name']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($prod['description']) ?></p>
            <p class="text-muted mb-1">Price: ₹<?= $prod['price'] ?></p>
            <p class="small text-secondary">Salon: <?= htmlspecialchars($prod['salon_name']) ?></p>
            <a href="product-details.php?id=<?= $prod['id'] ?>" class="btn btn-glam mt-auto">Buy Now</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>
