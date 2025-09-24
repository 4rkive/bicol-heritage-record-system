<?php
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: mortality.php");
    exit;
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM mortality WHERE id = $id");
$record = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $bloodline = $_POST['bloodline'];
    $wing = $_POST['wing_band'];
    $leg = $_POST['leg_band'];
    $cause = $_POST['cause_of_death'];

    $stmt = $conn->prepare("UPDATE mortality SET date=?, bloodline=?, wing_band=?, leg_band=?, cause_of_death=? WHERE id=?");
    $stmt->bind_param("sssssi", $date, $bloodline, $wing, $leg, $cause, $id);

    if ($stmt->execute()) {
        header("Location: mortality.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Record</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="modal" style="display:flex;">
        <form method="post">
            <h2>Edit Record</h2>
            <label>Date:</label>
            <input type="date" name="date" value="<?php echo $record['date']; ?>" required>
            <label>Bloodline:</label>
            <input type="text" name="bloodline" value="<?php echo $record['bloodline']; ?>" required>
            <label>Wing Band:</label>
            <input type="text" name="wing_band" value="<?php echo $record['wing_band']; ?>">
            <label>Leg Band:</label>
            <input type="text" name="leg_band" value="<?php echo $record['leg_band']; ?>">
            <label>Cause of Death:</label>
            <input type="text" name="cause_of_death" value="<?php echo $record['cause_of_death']; ?>" required>
            <button type="submit">Update</button>
            <a href="mortality.php"><button type="button">Cancel</button></a>
        </form>
    </div>
</body>
</html>
