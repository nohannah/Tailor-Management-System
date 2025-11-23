<?php
session_start();
session_unset();
session_destroy();

// Prevent back button access
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login page
header("Location: login.php");

exit();
?>
