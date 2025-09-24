<?php
$host = "localhost";   // ✅ the MySQL server (keep this as localhost for XAMPP)
$user = "root";        // ✅ your MySQL username (default is root for XAMPP)
$pass = "";            // ✅ your MySQL password (empty by default in XAMPP)
$dbname = "bgc";       // ✅ your database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
