<?php include('db_con.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tailoring Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" type="text/css" href="makeorder.css">
</head>
<body>
<!-- Navigation -->
<nav>
    <label class="logo">TailorPro Management</label>
    <ul>
    <li><a href="home.php" class="active">Home</a></li>
    <li><a href="services.php">Services</a></li>
    <li><a href="login.php">Orders</a></li>
    <li><a href="contact.php">Contact</a></li>
    <li><a href="login.php" class="btn btn-success btn-sm">Login</a></li>
</ul>
</nav>

<!-- Banner Section -->
<div class="section1">
    <img class="banner_img" src="image/tailor.jpeg" alt="Tailoring Banner">
    <div class="banner_text">
        <h1>Custom Tailoring Made Easy</h1>
        <p>Manage customers, orders, and payments all in one place.</p>
        <a href="employee.php" class="btn btn-primary mt-3">View Customers</a>
        <a href="vieworder.php" class="btn btn-outline-light mt-3">View Orders</a>
    </div>
</div>

<!-- Services Section -->
<div class="container mt-5">
    <div class="row text-center">
        <h2 class="mb-4">Our Services</h2>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <img src="image/tshirt.jpeg" class="card-img-top" alt="Shirts">
                <div class="card-body">
                    <h5 class="card-title">Custom Shirts</h5>
                    <p class="card-text">Tailor-made shirts with perfect fit and premium fabric options.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <img src="image/pant.jpeg" class="card-img-top" alt="Pants">
                <div class="card-body">
                    <h5 class="card-title">Pants & Trousers</h5>
                    <p class="card-text">Custom-fitted pants for all occasions and styles.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <img src="image/dress.jpeg" class="card-img-top" alt="Dresses">
                <div class="card-body">
                    <h5 class="card-title">Dresses & Outfits</h5>
                    <p class="card-text">Elegant and modern dresses tailored to your measurements.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- About / Why Choose Us Section -->
<div class="container mt-5 mb-5">
    <div class="row align-items-center">
        <div class="col-md-6 bt-dark">
            <h2>Why Choose TailorPro?</h2>
            <ul>
                <li>Manage customers and orders efficiently</li>
                <li>Track payments and invoices</li>
                <li>Generate reports for better business decisions</li>
                <li>Modern and user-friendly dashboard</li>
            </ul>
        </div>
        <div class="col-md-6">
            <img src="image/measurement.jpeg" class="img-fluid rounded shadow" alt="Tailoring Shop">
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container text-center">
        <p>&copy; 2025 TailorPro Management. All rights reserved.</p>
        <p>
            <a href="#">Privacy Policy</a> |
            <a href="#">Terms of Use</a>
        </p>
    </div>
</footer>
</body>
</html>