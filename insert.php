<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $bloodline = $_POST['bloodline'];
    $wing = $_POST['wing_band'];
    $leg = $_POST['leg_band'];
    $cause = $_POST['cause_of_death'];

    // Get branch_id from hidden field (set by filter)
    $branch_id = $_POST['branch_id'] ?? null;

    if (!$branch_id) {
        die("⚠️ Error: No branch selected. Please filter a branch first.");
    }

    // Insert record with branch_id
    $stmt = $conn->prepare("INSERT INTO mortality (branch_id, date, bloodline, wing_band, leg_band, cause_of_death) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $branch_id, $date, $bloodline, $wing, $leg, $cause);

    if ($stmt->execute()) {
        header("Location: mortality.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
