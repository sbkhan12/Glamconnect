<?php
include 'config/db.php';
$services = $pdo->query("SELECT s.*, salons.name AS salon_name FROM services s JOIN salons ON s.salon_id = salons.id WHERE s.status = 'active'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Services - GlamConnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f9f9f9;
      font-family: 'Segoe UI', sans-serif;
    }

    h2 {
      color: #1d2b64;
      font-weight: bold;
    }

    .container {
      max-width: 1200px;
    }

    .card {
      transition: transform 0.2s ease-in-out;
      border: none;
    }

    .card:hover {
      transform: scale(1.02);
    }

    .btn-glam {
      background-color: #ff69b4;
      color: white;
      border: none;
    }

    .btn-glam:hover {
      background-color: #e0569e;
    }

    .card-title {
      font-weight: 600;
      color: #1d2b64;
    }

    .card-text, .text-muted, .small {
      font-size: 0.95rem;
    }
  </style>
</head>
<body>

<div class="container py-5">
  <h2 class="mb-4 text-center">All Available Services</h2>
  <div class="row g-4">
    <?php foreach ($services as $srv): ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($srv['name']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($srv['description']) ?></p>
            <p class="text-muted mb-1">Price: ₹<?= $srv['price'] ?></p>
            <p class="small text-secondary">Salon: <?= htmlspecialchars($srv['salon_name']) ?></p>
            <a href="book-appointment.php?service_id=<?= $srv['id'] ?>" class="btn btn-glam mt-auto">Book Now</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>
