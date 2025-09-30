<?php
include 'db.php';
session_start();

// Get filters from payroll.php
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$branch_id = isset($_GET['branch_id']) ? $_GET['branch_id'] : '';

// Fetch branch name
$branch_name = "All Branches";
if (!empty($branch_id)) {
    $branch_sql = "SELECT branch_name FROM branches WHERE branch_id = '$branch_id'";
    $branch_result = $conn->query($branch_sql);
    if ($branch_result && $branch_result->num_rows > 0) {
        $branch_row = $branch_result->fetch_assoc();
        $branch_name = $branch_row['branch_name'];
    }
}

// Fetch payroll records
$sql = "SELECT p.*, e.employee_name, b.branch_name 
        FROM payroll p
        JOIN employees e ON p.employee_id = e.employee_id
        JOIN branches b ON p.branch_id = b.branch_id
        WHERE 1=1";

if (!empty($month)) $sql .= " AND MONTH(p.payroll_date) = '$month'";
if (!empty($year)) $sql .= " AND YEAR(p.payroll_date) = '$year'";
if (!empty($branch_id)) $sql .= " AND p.branch_id = '$branch_id'";

$sql .= " ORDER BY p.payroll_id ASC";
$result = $conn->query($sql);

// Calculate totals
$total_salary = $total_ca1 = $total_sss = $total_ph = $total_pagibig = $total_ca2 = $total_received = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payroll Preview</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ccc;
            margin: 0;
            padding: 20px;
        }
        .document {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            vertical-align: top;
        }
        th {
            background: #a8d5a2;
        }
        tfoot td {
            font-weight: bold;
            background: #f1f8e9;
        }
        .actions {
            text-align: right;
            margin-top: 20px;
        }
        .actions button {
            padding: 8px 16px;
            font-size: 14px;
            margin: 0 5px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .download-btn {
            background-color: #4caf50;
            color: white;
        }
        .download-btn:hover {
            background-color: #45a049;
        }
        .cancel-btn {
            background-color: #f44336;
            color: white;
        }
        .cancel-btn:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>
<div class="document">

    <!-- Month, Year & Branch -->
    <h3>
        MASTERLISTS PERSONAL EMPLOYEE – <?php echo $branch_name; ?>
    </h3>
    <h3>           
        <?php
         if (!empty($month) && !empty($year)) {
                echo strtoupper(date("F", mktime(0,0,0,$month,10))).", ".$year;
            } elseif (!empty($month)) {
                echo strtoupper(date("F", mktime(0,0,0,$month,10)));
            } elseif (!empty($year)) {
                echo $year;
            } else {
                echo "ALL RECORDS";
            }
            ?>
            </h3>

    <!-- Payroll Table -->
    <table>
        <thead>
    <tr>
        <th rowspan="2">Employee</th>
        <th rowspan="2">No. of Days</th>
        <th rowspan="2">Total Monthly Salary</th>
        <th colspan="4">Deduction</th>
        <th rowspan="2">Cash Advance </th>
        <th rowspan="2">Total Amount Received</th>
    </tr>
    <tr>
        <th>Cash Advance</th>
        <th>SSS</th>
        <th>PhilHealth</th>
        <th>Pag-IBIG</th>
    </tr>
</thead>


        <tbody>
        <?php
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $received = $row['total_monthly_salary'] - ($row['cash_advance1']+$row['sss']+$row['philhealth']+$row['pagibig']+$row['cash_advance2']);
                $total_salary +=$row['total_monthly_salary'];
                $total_ca1 += $row['cash_advance1'];
                $total_sss += $row['sss'];
                $total_ph += $row['philhealth'];
                $total_pagibig += $row['pagibig'];
                $total_ca2 += $row['cash_advance2'];
                $total_received += $received;
                echo "<tr>
                        <td>".$row['employee_name']."</td>
                        <td>".$row['no_of_days']."</td>
                        <td>".number_format($row['total_monthly_salary'])."</td>
                        <td>".number_format($row['cash_advance1'])."</td>
                        <td>".number_format($row['sss'])."</td>
                        <td>".number_format($row['philhealth'])."</td>
                        <td>".number_format($row['pagibig'])."</td>
                        <td>".number_format($row['cash_advance2'])."</td>
                        <td>".number_format($received)."</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='10' style='text-align:center;'>No payroll records found</td></tr>";
        }
        ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right;">Grand Totals:</td>
                <td><?php echo number_format($total_salary,0);?></td>
                <td><?php echo number_format($total_ca1); ?></td>
                <td><?php echo number_format($total_sss); ?></td>
                <td><?php echo number_format($total_ph); ?></td>
                <td><?php echo number_format($total_pagibig); ?></td>
                <td><?php echo number_format($total_ca2); ?></td>
                <td><?php echo number_format($total_received); ?></td>
             
            </tr>
        </tfoot>
    </table>

</div>

<!-- Action buttons -->
<div class="actions">
    <form method="GET" action="download_payroll.php" style="display:inline;">
        <input type="hidden" name="month" value="<?php echo $month; ?>">
        <input type="hidden" name="year" value="<?php echo $year; ?>">
        <input type="hidden" name="branch_id" value="<?php echo $branch_id; ?>">
        <button type="submit" class="download-btn">Download</button>
    </form>
    <button type="button" class="cancel-btn" onclick="window.top.location.href='payroll.php'">Cancel</button>
</div>

<script>
    document.addEventListener("keydown", function(e){
        if(e.key === "Escape") window.location.href = "payroll.php";
    });
</script>
</body>
</html>
