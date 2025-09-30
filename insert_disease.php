<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_detected = $_POST['date_detected'];
    $bloodline     = $_POST['bloodline'];
    $wingband      = $_POST['wingband'];
    $legband       = $_POST['legband'];
    $disease_name  = $_POST['disease_name'];

    // Get branch_id from hidden field (set by filter)
    $branch_id = $_POST['branch_id'] ?? null;

    if (!$branch_id) {
        die("⚠️ Error: No branch selected. Please filter a branch first.");
    }

    // Insert record with branch_id and default status "Infected"
    $stmt = $conn->prepare("INSERT INTO disease_records 
        (branch_id, date_detected, bloodline, wingband, legband, disease_name, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Infected')");
    $stmt->bind_param("isssss", $branch_id, $date_detected, $bloodline, $wingband, $legband, $disease_name);

    if ($stmt->execute()) {
        header("Location: disease.php?branch_id=" . $branch_id);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
