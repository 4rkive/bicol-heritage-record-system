<?php
include 'db.php';
session_start();

// Get filters from purchase.php
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

// Base query
$sql = "SELECT * FROM purchase WHERE 1=1";
if (!empty($month)) {
    $sql .= " AND MONTH(date) = '$month'";
}
if (!empty($year)) {
    $sql .= " AND YEAR(date) = '$year'";
}
if (!empty($branch_id)) {
    $sql .= " AND branch_id = '$branch_id'";
}
$sql .= " ORDER BY id ASC";
$result = $conn->query($sql);

// Total sales
$total_sql = "SELECT SUM(amount) AS total_amount FROM purchase WHERE 1=1";
if (!empty($month)) {
    $total_sql .= " AND MONTH(date) = '$month'";
}
if (!empty($year)) {
    $total_sql .= " AND YEAR(date) = '$year'";
}
if (!empty($branch_id)) {
    $total_sql .= " AND branch_id = '$branch_id'";
}
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_amount = $total_row['total_amount'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Preview</title>
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

        <!-- Month and Branch - Sales Title -->
        <h3>
            Month of 
            <?php 
                if (!empty($month) && !empty($year)) {
                    echo date("F", mktime(0, 0, 0, $month, 10)) . " " . $year;
                } elseif (!empty($month)) {
                    echo date("F", mktime(0, 0, 0, $month, 10));
                } elseif (!empty($year)) {
                    echo $year;
                } else {
                    echo "All Records";
                }
            ?>
        </h3>
        <h3 style="text-align:center; margin-top:-10px; margin-bottom:20px;">
            Feeds – <?php echo $branch_name; ?>
        </h3>

        <!-- Sales Table -->
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
                                <td>".number_format($row['amount'])."</td>
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
                    <td colspan='5x' style='text-align:right;'>Total Amount:</td>
                    <td><?php echo number_format($total_amount); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

    </div>

    <!-- Action buttons BELOW the document -->
    <div class="actions">
        <form method="GET" action="download_purchase.php" style="display:inline;">
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            <input type="hidden" name="branch_id" value="<?php echo $branch_id; ?>">
            <button type="submit" class="download-btn">Download</button>
        </form>
        <button type="button" class="cancel-btn" onclick="window.top.location.href='purchase.php'">Cancel</button>
    </div>

    <script>
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                window.location.href = "purchase.php";
            }
        });
    </script>

</body>
</html>
