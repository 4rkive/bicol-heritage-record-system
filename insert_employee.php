<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_name   = $_POST['employee_name'];
    $area            = $_POST['area'];
    $position        = $_POST['position'];
    $address         = $_POST['address'];
    $date_of_birth   = $_POST['date_of_birth'];
    $contact_number  = $_POST['contact_number'];
    $date_hired      = $_POST['date_hired'];
    $separation_date = $_POST['separation_date'];
    $monthly_rate    = $_POST['monthly_rate'];

    // Insert into database
    $sql = "INSERT INTO employees 
        (employee_name, area, position, address, date_of_birth, contact_number, date_hired, separation_date, monthly_rate) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssd", 
        $employee_name, 
        $area, 
        $position, 
        $address, 
        $date_of_birth, 
        $contact_number, 
        $date_hired, 
        $separation_date, 
        $monthly_rate
    );

    if ($stmt->execute()) {
        // Redirect back to employee page
        header("Location: employee.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
