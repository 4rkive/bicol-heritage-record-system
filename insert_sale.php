<?php
include 'db.php';
session_start();

// Get form data
$sale_date = $_POST['sale_date'];
$buyer = $_POST['buyer'];
$wingbands = $_POST['wingband']; // this is an array
$amount = $_POST['amount'];
$remarks = $_POST['remarks'];

// Insert into sale table
$stmt = $conn->prepare("INSERT INTO sale (sale_date, buyer, amount, remarks) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssds", $sale_date, $buyer, $amount, $remarks);

if ($stmt->execute()) {
    $sale_id = $stmt->insert_id; // get the inserted sale_id

    // Insert each wingband into sale_wingbands
    $stmt_w = $conn->prepare("INSERT INTO sale_wingbands (sale_id, wingband) VALUES (?, ?)");
    foreach ($wingbands as $wb) {
        $stmt_w->bind_param("is", $sale_id, $wb);
        $stmt_w->execute();
    }

    // ✅ Only redirect once, no second $stmt->execute()
    header("Location: sales.php?status=added");
    exit;
} else {
    header("Location: sales.php?status=add_error");
    exit;
}

$stmt->close();
$conn->close();
?>
