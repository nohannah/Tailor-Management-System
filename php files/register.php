<?php
include('db_con.php');

if (isset($_POST['submit'])) {

    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Always hash passwords

    // Generate a CustomerID automatically, e.g., CUST001, CUST002
    $query = "SELECT CustomerID FROM Customer ORDER BY CustomerID DESC LIMIT 1";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastID = intval(substr($row['CustomerID'], 4)); // Get numeric part
        $newID = 'CUST' . str_pad($lastID + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newID = 'CUST001';
    }

    // Insert into database
    $stmt = mysqli_prepare($connection, "INSERT INTO Customer (CustomerID, Name, Age, Gender, Email, ContactNo, Address, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssisssss", $newID, $name, $age, $gender, $email, $contact, $address, $password);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Registration successful! Your Customer ID is $newID'); window.location='login.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($connection) . "');</script>";
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TailorPro - Register</title>
<link rel="stylesheet" href="CSS/login.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TailorPro - Register</title>
<link rel="stylesheet" href="CSS/register.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="form_design">
    <form method="POST" class="register_form">

        <h1>Create Account</h1>

        <label class="label_design">Full Name</label>
        <input type="text" name="name" placeholder="Enter your full name" required maxlength="100">

        <label class="label_design">Age</label>
        <input type="number" name="age" placeholder="Enter your age" required min="1" max="120">

        <label class="label_design">Gender</label>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="M">Male</option>
            <option value="F">Female</option>
            <option value="O">Other</option>
        </select>

        <label class="label_design">Email</label>
        <input type="email" name="email" placeholder="Enter your email" required maxlength="100">

        <label class="label_design">Contact No</label>
        <input type="text" name="contact" placeholder="Enter your contact number" required maxlength="15">

        <label class="label_design">Address</label>
        <input type="text" name="address" placeholder="Enter your address" required maxlength="255">

        <label class="label_design">Password</label>
        <input type="password" name="password" placeholder="Create a password" required minlength="6">

        <input type="submit" name="submit" value="Register">

        <p>Already have an account? <a href="login.php">Login Here</a></p>
    </form>
</div>

</body>
</html>