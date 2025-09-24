<?php
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filters
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

// Base query
$sql = "SELECT * FROM sale WHERE 1=1";
if (!empty($month)) {
    $sql .= " AND MONTH(sale_date) = '$month'";
}
if (!empty($year)) {
    $sql .= " AND YEAR(sale_date) = '$year'";
}
$sql .= " ORDER BY sale_id ASC";
$result = $conn->query($sql);

// Total sales
$total_sql = "SELECT SUM(amount) AS total_sales FROM sale WHERE 1=1";
if (!empty($month)) {
    $total_sql .= " AND MONTH(sale_date) = '$month'";
}
if (!empty($year)) {
    $total_sql .= " AND YEAR(sale_date) = '$year'";
}
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_sales = $total_row['total_sales'] ?? 0;
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
        /* Button container OUTSIDE the document */
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

        <!-- Month and Farm - Sales Title -->
        <h3>
            Month: 
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
        <h3 style="text-align:center; margin-top:-10px; margin-bottom:20px;">Farm - Sales</h3>

        <!-- Sales Table -->
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
                        $wingbands = "";
                        $wb_res = $conn->query("SELECT wingband FROM sale_wingbands WHERE sale_id = ".$row['sale_id']);
                        while ($wb = $wb_res->fetch_assoc()) {
                            $wingbands .= $wb['wingband'] . "<br>";
                        }

                        echo "<tr>
                                <td>".$row['sale_date']."</td>
                                <td>".$row['buyer']."</td>
                                <td>".$wingbands."</td>
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

    <!-- Action buttons BELOW the document -->
    <div class="actions">
        <form method="GET" action="download_sales.php" style="display:inline;">
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            <button type="submit" class="download-btn">Download</button>
        </form>
        <button type="button" class="cancel-btn" onclick="window.top.location.href='sales.php'">Cancel</button>

    </div>

    <script>
        // Optional: also allow ESC key to return to sales.php
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                window.location.href = "sales.php";
            }
        });
    </script>

</body>
</html>
