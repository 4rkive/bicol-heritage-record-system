<?php
include 'db.php';
session_start();

// Get filters
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year  = isset($_GET['year']) ? $_GET['year'] : '';
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

// Fetch sales data
$sql = "SELECT * FROM purchase WHERE 1=1";
if (!empty($month)) $sql .= " AND MONTH(date) = '$month'";
if (!empty($year)) $sql .= " AND YEAR(date) = '$year'";
if (!empty($branch_id)) {
    $sql .= " AND branch_id = '$branch_id'";
}
$sql .= " ORDER BY id ASC";
$result = $conn->query($sql);

// Calculate total sales
$total_sql = "SELECT SUM(amount) AS total_amount FROM purchase WHERE 1=1";
if (!empty($month)) $total_sql .= " AND MONTH(date) = '$month'";
if (!empty($year)) $total_sql .= " AND YEAR(date) = '$year'";
if (!empty($branch_id)) {
    $total_sql .= " AND branch_id = '$branch_id'";
}

$total_result = $conn->query($total_sql);
$total_amount = $total_result->fetch_assoc()['total_amount'] ?? 0;

// Build filename
$filename = "Purchase Report";
if (!empty($branch_name)) {
    $filename .= " for " . $branch_name;
}
if (!empty($month)) {
    $filename .= " in " . date("F", mktime(0,0,0,$month,10));
}
if (!empty($year)) {
    $filename .= " " . $year;
}
$filename .= ".doc";

// Word headers
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=" . str_replace(' ', '_', $filename));
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Purchase Report</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: white;
        margin: 0;
        padding: 10px;
    }
    .document {
        background: white;
        width: 150mm;   /* narrower for portrait */
        min-height: 200mm; /* short page */
        margin: auto;
        padding: 10mm;
        box-shadow: none; /* remove shadow */
    }
    h3 {
        text-align: center;
        margin: 0 0 10px 0;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 14px;
    }
    th, td {
        border: 1px solid #000;
        padding: 10px;
        vertical-align: top;
        text-align: center;
    }
    th {
        background-color: #a8d5a2;
    }
    tfoot td {
        font-weight: bold;
        background-color: #f1f8e9;
    }
</style>
</head>
<body>
<div class="document">
    <h3>
        Month of
        <?php
        if(!empty($month) && !empty($year)){
            echo date("F", mktime(0,0,0,$month,10))." $year";
        } elseif(!empty($month)){
            echo date("F", mktime(0,0,0,$month,10));
        } elseif(!empty($year)){
            echo $year;
        } else {
            echo "All Records";
        }
        ?>
    </h3>
            <h3 style="text-align:center; margin-top:-10px; margin-bottom:20px;">
            Feeds – <?php echo $branch_name; ?>
        </h3>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Supplier</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Receipts</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) 
                    echo "<tr>
                            <td>".$row['date']."</td>
                            <td>".$row['supplier']."</td>
                            <td>".$row['qty']."</td>
                            <td>".$row['unit']."</td>
                            <td>".$row['description']."</td>
                            <td>".number_format($row['amount'],2)."</td>
                            <td>".$row['receipt_number']."</td>
                          </tr>";
                }
                 else {
                echo "<tr><td colspan='7' style='text-align:center;'>No records found</td></tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan='5' style='text-align:right;'>Total Amount:</td>
                <td><?php echo number_format($total_amount, 2); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</div>
</body>
</html>
