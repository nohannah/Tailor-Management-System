<?php
// db_con.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default
$DB_NAME = 'lab_project2';

$connection = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

// set charset
mysqli_set_charset($connection, 'utf8mb4');
?>
