<?php
// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bgc";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Check if sale_id is provided ---
if (isset($_GET['sale_id'])) {
    $sale_id = intval($_GET['sale_id']);

    // --- First delete related wingbands ---
    $conn->query("DELETE FROM sale_wingbands WHERE sale_id = $sale_id");

    // --- Then delete the sale header ---
    $delete_sql = "DELETE FROM sale_header WHERE sale_id = $sale_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>alert('Sale record deleted successfully.'); window.location.href='sales.php';</script>";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "Invalid request. No sale_id provided.";
}

$conn->close();
?>
