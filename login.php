<?php
session_start();
include('db_con.php');

// PREVENT BACK BUTTON (cache disable)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_POST['submit'])) {

    $email = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validate role
    if ($role == "customer") {

        // CUSTOMER LOGIN
        $stmt = mysqli_prepare($connection,
            "SELECT CustomerID, Name, Password FROM Customer WHERE Email = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {

            if (password_verify($password, $row['Password'])) {

                // Set session
                $_SESSION['role'] = "customer";
                $_SESSION['customer_id'] = $row['CustomerID'];
                $_SESSION['username'] = $row['Name'];

                header("Location: customer.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Customer not found.";
        }

        mysqli_stmt_close($stmt);
    }

    else if ($role == "employee") {

        // EMPLOYEE LOGIN
        $stmt = mysqli_prepare($connection,
            "SELECT EmployeeID, Name, Password FROM Employee WHERE Email = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {

            if (password_verify($password, $row['Password'])) {

                // Set session
                $_SESSION['role'] = "employee";
                $_SESSION['employee_id'] = $row['EmployeeID'];
                $_SESSION['username'] = $row['Name'];

                header("Location: index2.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Employee not found.";
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

<style>
body {
    background: #f8f9fa;
}
.form_design {
    max-width: 430px;
    margin: 60px auto;
}
.login_form {
    background: white;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}
h1 {
    text-align: center;
    font-weight: 700;
    margin-bottom: 20px;
}
.label_design {
    font-weight: 600;
}
</style>

</head>
<body>

<div class="form_design">
    <form action="" method="POST" class="login_form">

        <h1>Login to TailorPro</h1>

        <?php if(isset($error)) { ?>
            <div class="alert alert-danger text-center">
                <?= $error ?>
            </div>
        <?php } ?>

        <!-- EMAIL -->
        <div class="mb-3">
            <label class="label_design">Email</label>
            <input type="email" name="username" class="form-control"
                   placeholder="Enter your email" required>
        </div>

        <!-- PASSWORD -->
        <div class="mb-3">
            <label class="label_design">Password</label>
            <input type="password" name="password" class="form-control"
                   placeholder="Enter your password" required>
        </div>

        <!-- ROLE -->
        <div class="mb-3">
            <label class="label_design">Login As</label>
            <select name="role" class="form-control" required>
                <option value="" disabled selected>Select Role</option>
                <option value="customer">Customer</option>
                <option value="employee">Employee</option>
            </select>
        </div>

        <!-- SUBMIT -->
        <div class="mb-3">
            <button class="btn btn-primary w-100" type="submit" name="submit">
                Login
            </button>
        </div>

        <p class="text-center">
            Don't have an account? <a href="register.php">Register Here</a>
        </p>

    </form>
</div>

</body>
</html>
