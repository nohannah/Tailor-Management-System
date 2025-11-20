<?php
session_start();
include('db_con.php'); // Your DB connection

if (isset($_POST['submit'])) {
    $username = $_POST['username']; // email
    $password = $_POST['password'];
    $role = $_POST['role']; // NEW: role selection

    if ($role == "customer") {

        // Customer Login Check
        $stmt = mysqli_prepare($connection, 
            "SELECT CustomerID, Name, Password FROM Customer WHERE Email = ?"
        );
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['Password'])) {

                $_SESSION['username'] = $row['Name'];
                $_SESSION['customer_id'] = $row['CustomerID'];
                $_SESSION['role'] = "customer";

                header("Location: customer.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Customer not found!";
        }

        mysqli_stmt_close($stmt);

    } 
    else if ($role == "employee") {

        // Employee Login Check
        $stmt = mysqli_prepare($connection, 
            "SELECT EmployeeID, Name, Password FROM Employee WHERE Email = ?"
        );
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['Password'])) {

                $_SESSION['username'] = $row['Name'];
                $_SESSION['employee_id'] = $row['EmployeeID'];
                $_SESSION['role'] = "employee";

                header("Location: index2.php");
                
                exit();
            }
            else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Employee not found!";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TailorPro - Login</title>
<link rel="stylesheet" href="CSS/login.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="form_design">
    <form action="" method="POST" class="login_form">

        <h1>Login to TailorPro</h1>

        <?php if(isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <!-- Email -->
        <div class="mb-3">
            <label class="label_design">Email</label>
            <input type="email" name="username" class="form-control" placeholder="Enter your email" required maxlength="100">
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="label_design">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter your password" required maxlength="50">
        </div>

        <!-- Role Dropdown -->
        <div class="mb-3">
            <label class="label_design">Login As</label>
            <select name="role" class="form-control" required>
                <option value="" disabled selected>Select Role</option>
                <option value="customer">Customer</option>
                <option value="employee">Employee</option>
            </select>
        </div>

        <!-- Submit -->
        <div class="mb-3">
            <input class="btn btn-primary w-100" type="submit" name="submit" value="Login">
        </div>

        <p>Don't have an account? <a href="register.php">Register Here</a></p>

    </form>
</div>

</body>
</html>
