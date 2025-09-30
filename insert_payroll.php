<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $branch_id   = $_POST['branch_id'];
    $payroll_date= $_POST['payroll_date'];
    $no_of_days  = $_POST['no_of_days'];
    $salary      = $_POST['total_monthly_salary'];
    $ca1         = $_POST['cash_advance1'];
    $sss         = $_POST['sss'];
    $philhealth  = $_POST['philhealth'];
    $pagibig     = $_POST['pagibig'];
    $ca2         = $_POST['cash_advance2'];

    // Auto compute total amount received
    $total_received = $salary - ($ca1 + $sss + $philhealth + $pagibig + $ca2);

    // Restriction: prevent duplicate record for same employee in same month
    $month = date("m", strtotime($payroll_date));
    $year  = date("Y", strtotime($payroll_date));
    $check = $conn->prepare("SELECT * FROM payroll WHERE employee_id=? AND MONTH(payroll_date)=? AND YEAR(payroll_date)=?");
    $check->bind_param("iii", $employee_id, $month, $year);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        header("Location: payroll.php?status=duplicate");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO payroll (employee_id, branch_id, payroll_date, no_of_days, total_monthly_salary, cash_advance1, sss, philhealth, pagibig, cash_advance2, total_amount_received) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisiddddddd", $employee_id, $branch_id, $payroll_date, $no_of_days, $salary, $ca1, $sss, $philhealth, $pagibig, $ca2, $total_received);

    if ($stmt->execute()) {
        header("Location: payroll.php?status=added");
    } else {
        header("Location: payroll.php?status=add_error");
    }
    exit();
}
