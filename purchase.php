<?php
include 'db.php';
session_start();


// Detect current page for sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feeds Purchase Records</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    :root {
        --green1: #a8d5a2;
        --green2: #81c784;
        --green3: #4caf50;
        --green4: #388e3c;
        --green5: #2e7d32;
        --green6: #1b5e20;
    }
    body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: var(--green1);
    color: #333;
    height: 100vh;          /* full height */
    overflow: hidden;       /* prevent body scroll */
}
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 20px;
        background: var(--green2);
        color: #fff;
    }
    .farm-logo { display: flex; align-items: center; gap: 12px; }
    .farm-logo img {
        width: 50px; height: 50px; object-fit: cover;
        border-radius: 50%; border: 2px solid rgba(255,255,255,0.6);
        background: #fff;
    }
    .farm-name { font-size: 20px; font-weight: 700; }
    .layout {
    display: flex;
    height: calc(100vh - 64px); /* subtract header height */
    }
/* Sidebar stays fixed */
.sidebar {
    width: 260px;
    background: #388e3c;
    color: #a8d5a2;
    display: flex;
    flex-direction: column;
    padding: 16px 12px;
    height: 100%;
    position: sticky;
    top: 0;
    flex-shrink: 0; /* prevents shrinking */
    overflow-y: auto; /* scroll inside sidebar if content overflows */
}
    .sidebar.collapsed { width: 80px; }
    .sidebar.collapsed .nav a span,
    .sidebar.collapsed .nav-section-title { display: none; }
    .toggle-btn {
        cursor: pointer; font-size: 20px; padding: 10px;
        color: white; background: none; border: none;
        margin-bottom: 20px; text-align: left;
    }
    .nav { flex: 1; padding: 6px 6px 12px; }
    .nav a {
        display: flex; align-items: center; gap: 10px;
        text-decoration: none; color: #fff;
        padding: 10px 12px; margin: 4px 6px;
        border-radius: 8px; transition: background 0.2s ease;
    }
    .nav a.active, .nav a:hover { background: rgba(255, 255, 255, 0.25); }
    .nav-section-title {
        font-size: 11px; letter-spacing: 1.5px;
        opacity: 0.9; margin: 14px 6px 6px;
    }

    /* Make main scroll properly */
.main {
    flex: 1;
    padding: 20px;
    background: linear-gradient(rgba(177, 221, 158, 0.40), rgba(177, 221, 158, 0.40)), 
                url("image/cover.jpg") no-repeat center center;
    background-size: cover;
    height: 100%;
    overflow-y: auto;   /* allow scrolling */
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
}

/* Optional: stick total sales row at the bottom */
.total-row {
    position: sticky;
    bottom: 0;
    background: #fff;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
}
    .page-title {
        text-align: center; margin: 0; font-size: 24px;
        font-weight: 700; margin-bottom: 10px; color: #18392b;
    }
    .header-controls {
        display: flex; justify-content: space-between;
        align-items: center; margin-bottom: 15px;
    }
    .filter-controls form { display: flex; gap: 10px; }
    .filter-controls select, .filter-controls button { padding: 6px; }
    .add-btn button {
        background: var(--green3); color: white; border: none;
        padding: 6px 12px; border-radius: 6px; cursor: pointer;
    }
    .add-btn button:hover { background: var(--green5); }
/* Ensure table fills space and doesn't cut off bottom row */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    flex-shrink: 0;   /* ✅ keep table fully visible */
}
    
    .btn-edit { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; margin: 0 3px; display: inline-block; }
    .btn-edit { background: #388e3c; color: white; }
    .btn-edit:hover { background: #2e7d32; }
    table th, table td {
        padding: 10px; border-bottom: 1px solid #ddd;
        text-align: center; font-size: 14px;
    }
    table th { background: var(--green3); color: #fff; }
    .modal {
        display: none; position: fixed; top:0; left:0;
        width:100%; height:100%;
        background: rgba(0,0,0,0.5);
        justify-content: center; align-items: center;
    }
    .modal form {
        background: #fff; padding: 20px;
        border-radius: 12px; display: flex;
        flex-direction: column; gap: 10px; width: 320px;
    }
    .modal input, .modal button, .modal select { padding: 8px; }
    .modal .form-actions {
        display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;
    }
    .modal button[type="submit"] {
        background: var(--green3); color: #fff; border: none;
        padding: 8px 14px; border-radius: 6px; cursor: pointer;
        transition: background 0.2s ease;
    }
    .modal button[type="submit"]:hover { background: var(--green5); }
    .modal button[type="button"] {
        background: #e0e0e0; color: #333; border: none;
        padding: 8px 14px; border-radius: 6px; cursor: pointer;
        transition: background 0.2s ease;
    }
    .modal button[type="button"]:hover { background: #bdbdbd; }
    .toast {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        background-color: #28a745;
        color: #fff;
        text-align: center;
        border-radius: 8px;
        padding: 16px;
        position: fixed;
        z-index: 1000;
        left: 50%;
        top: 30px;
        font-size: 17px;
        opacity: 0;
        transition: opacity 0.5s, bottom 0.5s;
    }
    .toast.show {
        visibility: visible;
        opacity: 1;
        top: 50px;
    }
    .toast.error { background-color: #dc3545; }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="farm-logo">
            <img src="image/logo.png" alt="Farm Logo" />
            <span class="farm-name">Bicol Heritage Gamefarm</span>
        </div>
    </header>

    <div class="layout">
        <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <nav class="nav">
            <a href="dashboard.php" class="<?php echo ($current_page=='dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            <div class="nav-section-title">BREEDING</div>
            <a href="lineage.php" class="<?php echo ($current_page=='lineage.php') ? 'active' : ''; ?>"><i class="fas fa-dna"></i> <span>Breeding</span></a>
            <div class="nav-section-title">FEEDS</div>
            <a href="purchase.php" class="<?php echo ($current_page=='purchase.php') ? 'active' : ''; ?>"><i class="fas fa-cart-plus"></i> <span>Purchase</span></a>
            <div class="nav-section-title">HEALTH</div>
            <a href="mortality.php" class="<?php echo ($current_page=='mortality.php') ? 'active' : ''; ?>"><i class="fas fa-skull-crossbones"></i> <span>Mortality</span></a>
            <a href="disease.php" class="<?php echo ($current_page=='disease.php') ? 'active' : ''; ?>"><i class="fas fa-virus"></i> <span>Disease & Cured</span></a>
            <div class="nav-section-title">REPORT</div>
            <a href="sales.php" class="<?php echo ($current_page=='sales.php') ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> <span>Sales</span></a>
            <a href="payroll.php" class="<?php echo ($current_page=='payroll.php') ? 'active' : ''; ?>"><i class="fas fa-money-check"></i> <span>Payroll</span></a>
            <a href="employee.php" class="<?php echo ($current_page=='employee.php') ? 'active' : ''; ?>"><i class="fas fa-users"></i> <span>Employee</span></a>
        </nav>
    </aside>

        <!-- Main -->
        <main class="main">
            <h1 class="page-title">Feeds Purchase Records</h1>
            <div class="header-controls">
                <div class="filter-controls">
                    <form method="GET">
                        <select name="month" onchange="this.form.submit()">
                            <option value="">All Months</option>
                            <?php 
                                for ($m=1; $m<=12; $m++) {
                                    $selected = (isset($_GET['month']) && $_GET['month']==$m) ? "selected" : "";
                                    echo "<option value='$m' $selected>".date("F", mktime(0,0,0,$m,1))."</option>";
                                }
                            ?>
                        </select>
                        <select name="year" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            <?php 
                                $years = $conn->query("SELECT DISTINCT YEAR(date) as yr FROM mortality ORDER BY yr DESC");
                                while ($y = $years->fetch_assoc()) {
                                    $selected = (isset($_GET['year']) && $_GET['year']==$y['yr']) ? "selected" : "";
                                    echo "<option value='".$y['yr']."' $selected>".$y['yr']."</option>";
                                }
                            ?>
                        </select>
                        <select name="branch_id" onchange="this.form.submit()">
                            <option value="">All Branches</option>
                            <?php 
                                $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
                                while ($b = $branches->fetch_assoc()) {
                                    $selected = (isset($_GET['branch_id']) && $_GET['branch_id']==$b['branch_id']) ? "selected" : "";
                                    echo "<option value='".$b['branch_id']."' $selected>".$b['branch_name']."</option>";
                                }
                            ?>
                        </select>
                    </form>
                </div>
                <div class="add-btn">
                    <button onclick="document.getElementById('addForm').style.display='flex'">➕ Add Record</button>
                    <button  onclick="openPreview()">Download</button>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Date</</th>
                        <th>Branch</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Receipt Number</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $where = [];
                if (!empty($_GET['month'])) $where[] = "MONTH(p.date) = ".intval($_GET['month']);
                if (!empty($_GET['year'])) $where[] = "YEAR(p.date) = ".intval($_GET['year']);
                if (!empty($_GET['branch_id'])) $where[] = "p.branch_id = ".intval($_GET['branch_id']);

                $sql = "SELECT p.*, b.branch_name 
                        FROM purchase p 
                        JOIN branches b ON p.branch_id = b.branch_id";

                if ($where) {
                    $sql .= " WHERE " . implode(" AND ", $where);
                }
                $sql .= " ORDER BY p.date DESC, p.id DESC";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>".$row['date']."</td>
                            <td>".$row['branch_name']."</td>
                            <td>".$row['supplier']."</td>
                            <td>".$row['qty']."</td>
                            <td>".$row['unit']."</td>
                            <td>".$row['description']."</td>
                            <td>₱ ".number_format($row['amount'])."</td>
                            <td>".$row['receipt_number']."</td>
                            <td>
                                <button type='button' class='btn-edit'onclick='openEditForm(".json_encode($row).")'>✏️ Edit</button>
                            </td>
                        </tr>";
                    }
                } else {
                    $message = "No records found";
                    if (!empty($_GET['month'])) $message = "No record for this month";
                    if (!empty($_GET['year'])) $message = "No record for this year";
                    if (!empty($_GET['branch_id'])) $message = "No record for this branch";

                    echo "<tr><td colspan='9' style='text-align:center; color:red;'>$message</td></tr>";
                }
                ?>
                </tbody>
                <tfoot>
<?php
$totalSql = "SELECT SUM(p.amount) as total_amount
             FROM purchase p 
             JOIN branches b ON p.branch_id = b.branch_id";
if ($where) {
    $totalSql .= " WHERE " . implode(" AND ", $where);
}
$totalResult = $conn->query($totalSql);
$totalRow = $totalResult->fetch_assoc();
$grandTotal = $totalRow['total_amount'] ?? 0;

echo "<tr>
        <td colspan='6' style='text-align:right; font-weight:bold;'>Total Amount:</td>
        <td style='text-align:center; font-weight:bold; color:green;'>
            ₱ " . number_format($grandTotal, 2) . "
        </td>
        <td colspan='2'></td>
      </tr>";
?>
</tfoot>
            </table>
        </main>
    </div>

    <!-- Add Modal -->
    <div id="addForm" class="modal">
        <form action="insert_purchase.php" method="post">
            <h2>Add Purchase Record</h2>
            <label>Date:</label>
            <input type="date" name="date" required>
            <label>Branch:</label>
            <select name="branch_id" id="branch_id" required>
                <option value="">Select Branch</option>
                <?php 
                    $branches2 = $conn->query("SELECT * FROM branches");
                    while ($b = $branches2->fetch_assoc()) {
                        echo "<option value='".$b['branch_id']."'>".$b['branch_name']."</option>";
                    }
                ?>
            </select>
            <label>Supplier:</label>
            <input type="text" name="supplier" required>
            <label>Quantity:</label>
            <input type="text" name="qty" required>
            <label>Unit:</label>
            <input type="text" name="unit" required>
            <label>Description:</label>
            <input type="text" name="description" required>
            <label>Amount:</label>
            <input type="number" step="0.01" name="amount" required>
            <label>Receipt Number:</label>
            <input type="text" name="receipt_number" required>
            <div class="form-actions">
                <button type="submit">Save</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Edit Modal -->
    <div id="editForm" class="modal">
        <form action="update_purchase.php" method="post">
            <h2>Edit Purchase Record</h2>
            <input type="hidden" name="update_id" id="edit_id">
            <label>Date:</label>
            <input type="date" name="date" id="edit_date" required>
            <label>Branch:</label>
            <select name="branch_id" id="edit_branch_id" required>
                <?php 
                    $branches3 = $conn->query("SELECT * FROM branches");
                    while ($b = $branches3->fetch_assoc()) {
                        echo "<option value='".$b['branch_id']."'>".$b['branch_name']."</option>";
                    }
                ?>
            </select>
            <label>Supplier:</label>
            <input type="text" name="supplier" id="edit_supplier" required>
            <label>Quantity:</label>
            <input type="text" name="qty" id="edit_qty" required>
            <label>Unit:</label>
            <input type="text" name="unit" id="edit_unit" required>
            <label>Description:</label>
            <input type="text" name="description" id="edit_description" required>
            <label>Amount:</label>
            <input type="number" step="0.01" name="amount" id="edit_amount" required>
            <label>Receipt Number:</label>
            <input type="text" name="receipt_number" id="edit_receipt" required>
            <div class="form-actions">
                <button type="submit">Update</button>
                <button type="button" onclick="document.getElementById('editForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast"></div>

    <div id="previewModal" class="modal">
    <div style="background:#fff; width:65%; height:75%; border-radius:10px; position:relative; overflow:hidden;">
        <iframe id="previewFrame" src="" style="width:100%; height:100%; border:none;"></iframe>
    </div>
</div>

    <script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("collapsed");
    }
    function openEditForm(row) {
        document.getElementById('edit_id').value = row.id;
        document.getElementById('edit_date').value = row.date;
        document.getElementById('edit_branch_id').value = row.branch_id;
        document.getElementById('edit_supplier').value = row.supplier;
        document.getElementById('edit_qty').value = row.qty;
        document.getElementById('edit_unit').value = row.unit;
        document.getElementById('edit_description').value = row.description;
        document.getElementById('edit_amount').value = row.amount;
        document.getElementById('edit_receipt').value = row.receipt_number;
        document.getElementById('editForm').style.display = 'flex';
    }
    function showToast(message, success = true) {
        var toast = document.getElementById("toast");
        toast.innerText = message;
        toast.className = "toast show " + (success ? "" : "error");
        setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
    }
    window.onload = function() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('status')) {
            if (params.get('status') === 'success') {
                showToast("Record updated successfully!", true);
            } else if (params.get('status') === 'error') {
                showToast("Error updating record.", false);
            }
        }
    }
    window.onload = function() {
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) {
        if (params.get('status') === 'success') {
            showToast("Record updated successfully!", true);
        } else if (params.get('status') === 'error') {
            showToast("Error updating record.", false);
        } else if (params.get('status') === 'added') {
            showToast("Record added successfully!", true);
        } else if (params.get('status') === 'add_error') {
            showToast("Error adding record.", false);
        }
    }
}
    // ✅ Open Preview modal with filters (month, year, branch)
    function openPreview() {
        const params = new URLSearchParams(window.location.search);
        const month = params.get('month') || '';
        const year = params.get('year') || '';
        const branch = params.get('branch_id') || '';

        document.getElementById("previewFrame").src = 
            "preview_purchase.php?month=" + month + "&year=" + year + "&branch_id=" + branch;
        document.getElementById("previewModal").style.display = "flex";
    }

// ✅ Close Preview modal
function closePreview() {
    document.getElementById("previewModal").style.display = "none";
    document.getElementById("previewFrame").src = "";
};
    </script>
</body>
</html>
