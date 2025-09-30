<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $branch_id = $_POST['branch_id'];
    $supplier = $_POST['supplier'];
    $qty = $_POST['qty'];
    $unit = $_POST['unit'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $receipt_number = $_POST['receipt_number'];

    $stmt = $conn->prepare("INSERT INTO purchase (date, branch_id, supplier, qty, unit, description, amount, receipt_number) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissssis", $date, $branch_id, $supplier, $qty, $unit, $description, $amount, $receipt_number);

    if ($stmt->execute()) {
        header("Location: purchase.php?status=added");
        exit;
    } else {
        header("Location: purchase.php?status=add_error");
        exit;
    }
    $stmt->close();
}
?>
