<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$sale_date = $_POST['sale_date'];
$buyer = $_POST['buyer'];
$wingbands = $_POST['wingband']; // this is an array
$amount = $_POST['amount'];
$remarks = $_POST['remarks'];

// Insert into sale_header
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

    header("Location: sales.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
?>
