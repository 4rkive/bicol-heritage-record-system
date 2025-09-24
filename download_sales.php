<?php
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filters
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year  = isset($_GET['year']) ? $_GET['year'] : '';

// Fetch sales data
$sql = "SELECT * FROM sale WHERE 1=1";
if (!empty($month)) $sql .= " AND MONTH(sale_date) = '$month'";
if (!empty($year)) $sql .= " AND YEAR(sale_date) = '$year'";
$sql .= " ORDER BY sale_id ASC";
$result = $conn->query($sql);

// Calculate total sales
$total_sql = "SELECT SUM(amount) AS total_sales FROM sale WHERE 1=1";
if (!empty($month)) $total_sql .= " AND MONTH(sale_date) = '$month'";
if (!empty($year)) $total_sql .= " AND YEAR(sale_date) = '$year'";
$total_result = $conn->query($total_sql);
$total_sales = $total_result->fetch_assoc()['total_sales'] ?? 0;

// Word headers
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=sales_report.doc");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Sales Report</title>
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
        <h3>Farm - Sales</h3>

    <table>
        <thead>
            <tr>
                <th>Sale Date</th>
                <th>Buyer</th>
                <th>Wingbands</th>
                <th>Amount</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $wingbands = [];
                    $wb_res = $conn->query("SELECT wingband FROM sale_wingbands WHERE sale_id=".$row['sale_id']);
                    while ($wb = $wb_res->fetch_assoc()) {
                        $wingbands[] = $wb['wingband'];
                    }
                    echo "<tr>
                            <td>".$row['sale_date']."</td>
                            <td>".$row['buyer']."</td>
                            <td>".implode("<br>", $wingbands)."</td>
                            <td>".number_format($row['amount'],2)."</td>
                            <td>".$row['remarks']."</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>No records found</td></tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan='3' style='text-align:right;'>Total Sales:</td>
                <td><?php echo number_format($total_sales, 2); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</div>
</body>
</html>
