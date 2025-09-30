<?php
include 'db.php';
session_start();

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_id'])) {
    $id = intval($_POST['update_id']);
    $date = $_POST['date'];
    $branch_id = $_POST['branch_id'];
    $supplier = $_POST['supplier'];
    $qty = $_POST['qty'];
    $unit = $_POST['unit'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $receipt_number = $_POST['receipt_number'];

    $stmt = $conn->prepare("UPDATE purchase SET date=?, branch_id=?, supplier=?, qty=?, unit=?, description=?, amount=?, receipt_number=? WHERE id=?");
    $stmt->bind_param("sisssssii", $date, $branch_id, $supplier, $qty, $unit, $description, $amount, $receipt_number, $id);

    if ($stmt->execute()) {
        header("Location: purchase.php?status=success");
        exit;
    } else {
        header("Location: purchase.php?status=error");
        exit;
    }
    $stmt->close();
}
?>