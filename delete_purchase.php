<?php
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;
if ($id) {
    $conn->query("DELETE FROM purchase WHERE id=$id");
    echo "<script>alert('Record deleted successfully!'); window.location.href='purchase.php';</script>";
} else {
    echo "<script>alert('Invalid request.'); window.location.href='purchase.php';</script>";
}
$conn->close();
?>
