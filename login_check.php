<?php
session_start();
include('db_con.php');

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);

    // Check in Employee table
    $query = "SELECT * FROM Employee WHERE Name=? AND Password=?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'employee'; // optional, you can track role
        header("Location: dashboard.php");
        exit();
    } else {
        // Check in Customer table
        $query2 = "SELECT * FROM Customer WHERE Name=? AND password=?";
        $stmt2 = mysqli_prepare($connection, $query2);
        mysqli_stmt_bind_param($stmt2, "ss", $username, $password);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);

        if (mysqli_num_rows($result2) === 1) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'customer';
            header("Location: customer_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid Name or Password'); window.location='login.php';</script>";
        }
    }
} else {
    header("Location: login.php");
    exit();
}
?>
