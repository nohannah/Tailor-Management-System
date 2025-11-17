<?php
session_start();
include('db_con.php');

$success = '';
$error = '';

if(isset($_POST['submit'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Basic validation
    if(empty($name) || empty($email) || empty($subject) || empty($message)){
        $error = "All fields are required.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email address.";
    } else {
        $stmt = mysqli_prepare($connection, "INSERT INTO ContactMessages (Name, Email, Subject, Message) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
        if(mysqli_stmt_execute($stmt)){
            $success = "Your message has been sent successfully!";
        } else {
            $error = "Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contact Us - TailorPro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
.navbar {
    background: rgba(0,0,0,0.85); /* semi-transparent black */
    border-radius: 0 0 15px 15px;
    backdrop-filter: blur(5px); /* nice modern blur */
}
.navbar-brand {
    letter-spacing: 1px;
}
.nav-link {
    font-weight: 500;
    transition: color 0.3s, transform 0.2s;
}
.nav-link:hover, .nav-link.active {
    color: #ffc107;
    transform: scale(1.05);
}
.btn-danger {
    font-weight: 500;
    transition: transform 0.2s;
}
.btn-danger:hover {
    transform: scale(1.05);
}
.contact-card {
    background: #fff;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: transform 0.3s;
}
.contact-card:hover {
    transform: translateY(-5px);
}
h2 {
    color: #343a40;
    margin-bottom: 30px;
    font-weight: 700;
}
.form-floating>.form-control:focus~label,
.form-floating>.form-control:not(:placeholder-shown)~label {
    color: #495057;
    font-weight: 500;
}
.btn-primary {
    background-color: #0062cc;
    border: none;
    font-weight: 600;
}
.alert {
    border-radius: 10px;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3" href="home.php">TailorPro</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item mx-2">
                    <a class="nav-link" href="home.php">Home</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="home.php">Services</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="btn btn-danger btn-sm px-3" href="login.php">Log In</a>
                </li>
            </ul>
        </div>
    </div>
</nav>


<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="contact-card">
                <h2 class="text-center"><i class="fa-solid fa-envelope"></i> Contact Us</h2>

                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php elseif($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                        <label for="name"><i class="fa-solid fa-user"></i> Name</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                        <label for="email"><i class="fa-solid fa-envelope"></i> Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required>
                        <label for="subject"><i class="fa-solid fa-tag"></i> Subject</label>
                    </div>
                    <div class="form-floating mb-4">
                        <textarea class="form-control" id="message" name="message" placeholder="Message" style="height: 150px;" required></textarea>
                        <label for="message"><i class="fa-solid fa-message"></i> Message</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="submit" class="btn btn-primary btn-lg">Send Message <i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
