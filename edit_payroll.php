<?php
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id          = $_POST['employee_id'];
    $pay_period_start     = $_POST['pay_period_start'];
    $pay_period_end       = $_POST['pay_period_end'];
    $no_of_days           = $_POST['no_of_days'];
    $total_monthly_salary = $_POST['total_monthly_salary'];
    $cash_advance         = $_POST['cash_advance'];
    $sss                  = $_POST['sss'];
    $philhealth           = $_POST['philhealth'];
    $pagibig              = $_POST['pagibig'];
    $total_amount_received= $_POST['total_amount_received'];

    // ✅ Fetch employee area again (in case employee changed)
    $area_sql = "SELECT area FROM employees WHERE employee_id = ?";
    $stmt_area = $conn->prepare($area_sql);
    $stmt_area->bind_param("i", $employee_id);
    $stmt_area->execute();
    $area_result = $stmt_area->get_result();
    $row_area = $area_result->fetch_assoc();
    $employee_area = $row_area['area'];
    $stmt_area->close();

    $sql = "UPDATE payroll SET 
                employee_id=?, 
                area=?, 
                pay_period_start=?, 
                pay_period_end=?, 
                no_of_days=?, 
                total_monthly_salary=?, 
                cash_advance=?, 
                sss=?, 
                philhealth=?, 
                pagibig=?, 
                total_amount_received=?
            WHERE payroll_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssiddddddi",
        $employee_id, $employee_area, $pay_period_start, $pay_period_end, $no_of_days,
        $total_monthly_salary, $cash_advance, $sss, $philhealth, $pagibig, $total_amount_received, $id
    );

    if ($stmt->execute()) {
        header("Location: payroll.php?msg=updated");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch payroll record for editing form
if ($id) {
    $sql = "SELECT * FROM payroll WHERE payroll_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payroll = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>
