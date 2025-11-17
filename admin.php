<?php
session_start();
include('db_con.php'); // Ensure this connects to your lab_project2 database

$error = '';
$success = '';

if (isset($_POST['submit'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    // Handle specialization checkboxes
    $specializations = isset($_POST['specialization']) ? $_POST['specialization'] : [];
    $specialization_string = implode(",", $specializations); // store as comma-separated string

    $hiredate = $_POST['hiredate'];
    $contact = trim($_POST['contact']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hashed password

    // Check if email already exists
    $stmt_check = mysqli_prepare($connection, "SELECT EmployeeID FROM Employee WHERE Email = ?");
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $error = "Email already exists!";
    } else {
        // Insert employee
        $stmt = mysqli_prepare($connection, "INSERT INTO Employee (Name, Email, Specialization, HireDate, ContactNo, Password) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $specialization_string, $hiredate, $contact, $password);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Employee added successfully! Employee ID: " . mysqli_insert_id($connection);
        } else {
            $error = "Error: " . mysqli_error($connection);
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_stmt_close($stmt_check);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Add Employee</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="container mt-5">
    <h2>Add New Employee</h2>

    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <!-- Specialization checkboxes -->
        <div class="mb-3">
            <label class="form-label">Specialization</label><br>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="specialization[]" value="Pants" id="specPants">
                <label class="form-check-label" for="specPants">Pants</label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="specialization[]" value="Shirts" id="specShirts">
                <label class="form-check-label" for="specShirts">Shirts</label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="specialization[]" value="Dress" id="specDress">
                <label class="form-check-label" for="specDress">Dress</label>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Hire Date</label>
            <input type="date" name="hiredate" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Contact No</label>
            <input type="text" name="contact" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Add Employee</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
