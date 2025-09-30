<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sale_id'])) {
    $sale_id = intval($_POST['sale_id']);

    // Delete related wingbands first
    $conn->query("DELETE FROM sale_wingbands WHERE sale_id = $sale_id");

    // Then delete the sale record
    $delete_sql = "DELETE FROM sale WHERE sale_id = $sale_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "success"; // Return success for AJAX
    } else {
        echo "error";   // Return error for AJAX
    }
} else {
    echo "error";       // Invalid request
}

$conn->close();
?>
