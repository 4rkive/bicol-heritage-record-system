<?php
include 'db.php';
session_start();

$sale_id = intval($_POST['sale_id']);
$sale_date = $_POST['sale_date'];
$buyer = $_POST['buyer'];
$amount = $_POST['amount'];
$remarks = $_POST['remarks'];
$wingbands = $_POST['wingband'] ?? [];

// Update sale header
$update_sale = $conn->query("UPDATE sale SET sale_date='$sale_date', buyer='$buyer', amount='$amount', remarks='$remarks' WHERE sale_id=$sale_id");

// Delete old wingbands
$delete_wb = $conn->query("DELETE FROM sale_wingbands WHERE sale_id=$sale_id");

// Insert new wingbands
$insert_ok = true;
foreach ($wingbands as $wb) {
    $wb = $conn->real_escape_string($wb);
    if (!$conn->query("INSERT INTO sale_wingbands (sale_id, wingband) VALUES ($sale_id, '$wb')")) {
        $insert_ok = false;
    }
}

// Check if all queries succeeded
if ($update_sale && $delete_wb && $insert_ok) {
    header("Location: sales.php?status=success");
    exit;
} else {
    header("Location: sales.php?status=error");
    exit;
}
