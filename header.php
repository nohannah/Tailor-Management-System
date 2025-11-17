<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>TailorPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/TailoringSystem/CSS/header.css">
</head>
<body>
<nav>
  <label class="logo">TailorPro Management</label>
  <ul>
    <li><a href="/TailoringSystem/index.php">Home</a></li>
    <li><a href="/TailoringSystem/customers.php">Customers</a></li>
    <li><a href="/TailoringSystem/orders.php">Orders</a></li>
    <li><a href="/TailoringSystem/measurements.php">Measurements</a></li>
    <?php if(isset($_SESSION['username'])): ?>
      <li><a href="/TailoringSystem/logout.php" class="btn btn-sm btn-danger">Logout</a></li>
    <?php else: ?>
      <li><a href="/TailoringSystem/login.php" class="btn btn-sm btn-success">Login</a></li>
    <?php endif; ?>
  </ul>
</nav>
<div class="container mt-5 pt-4">
