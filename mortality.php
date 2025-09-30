<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- FETCH FILTERS ---
$month = isset($_GET['month']) ? intval($_GET['month']) : '';
$year = isset($_GET['year']) ? intval($_GET['year']) : '';
$branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : '';

// --- FETCH BRANCHES AND BLOODLINES ---
$branches_res = $conn->query("SELECT * FROM branches");
$branches = [];
while($row = $branches_res->fetch_assoc()) $branches[] = $row;

$bloodline_res = $conn->query("SELECT * FROM bloodline");
$bloodlines = [];
while($row = $bloodline_res->fetch_assoc()) $bloodlines[] = $row;

// --- HANDLE ADD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_record'])) {
    $date = $conn->real_escape_string($_POST['date']);
    $bloodline_id = intval($_POST['bloodline_id']);
    $branch_id_post = intval($_POST['branch_id']);
    $wing_band = $conn->real_escape_string($_POST['wing_band']);
    $leg_band = $conn->real_escape_string($_POST['leg_band']);
    $cause_of_death = $conn->real_escape_string($_POST['cause_of_death']);

    if($conn->query("INSERT INTO mortality (date, bloodline, branch_id, wing_band, leg_band, cause_of_death) 
        VALUES ('$date', $bloodline_id, $branch_id_post, '$wing_band', '$leg_band', '$cause_of_death')")) {
        $_SESSION['toast'] = ["message"=>"Record added successfully!", "type"=>"success"];
    } else $_SESSION['toast'] = ["message"=>"Error adding record: ".$conn->error, "type"=>"error"];
    header("Location: mortality.php");
    exit;
}

// --- HANDLE EDIT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_record'])) {
    $id = intval($_POST['id']);
    $date = $conn->real_escape_string($_POST['date']);
    $bloodline_id = intval($_POST['bloodline_id']);
    $branch_id_post = intval($_POST['branch_id']);
    $wing_band = $conn->real_escape_string($_POST['wing_band']);
    $leg_band = $conn->real_escape_string($_POST['leg_band']);
    $cause_of_death = $conn->real_escape_string($_POST['cause_of_death']);

    if($conn->query("UPDATE mortality SET date='$date', bloodline=$bloodline_id, branch_id=$branch_id_post, wing_band='$wing_band', leg_band='$leg_band', cause_of_death='$cause_of_death' WHERE id=$id")) {
        $_SESSION['toast'] = ["message"=>"Record updated successfully!", "type"=>"success"];
    } else $_SESSION['toast'] = ["message"=>"Error updating record: ".$conn->error, "type"=>"error"];
    header("Location: mortality.php");
    exit;
}

// --- HANDLE DELETE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_record'])) {
    $id = intval($_POST['id']);
    if($conn->query("DELETE FROM mortality WHERE id=$id")) {
        $_SESSION['toast'] = ["message"=>"Record deleted successfully!", "type"=>"success"];
    } else $_SESSION['toast'] = ["message"=>"Error deleting record: ".$conn->error, "type"=>"error"];
    header("Location: mortality.php");
    exit;
}

// --- FETCH RECORDS WITH FILTERS ---
$where = [];
if($month) $where[] = "MONTH(m.date) = $month";
if($year) $where[] = "YEAR(m.date) = $year";
if($branch_id) $where[] = "m.branch_id = $branch_id";

$sql = "SELECT m.*, b.branch_name, bl.bloodline_name 
        FROM mortality m
        LEFT JOIN branches b ON m.branch_id = b.branch_id
        LEFT JOIN bloodline bl ON m.bloodline = bl.bloodline_id";
if(!empty($where)) $sql .= " WHERE ".implode(" AND ", $where);
$sql .= " ORDER BY m.date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Mortality Records</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root {
    --green1: #a8d5a2; --green2:#81c784; --green3:#4caf50; --green4:#388e3c; --green5:#2e7d32; --green6:#1b5e20;
}
body { margin:0; font-family:Arial,sans-serif; background:var(--green1); color:#333; }
.header { display:flex; justify-content:space-between; align-items:center; padding:6px 20px; background:var(--green2); color:#fff; }
.farm-logo { display:flex; align-items:center; gap:12px; }
.farm-logo img { width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid rgba(255,255,255,0.6); background:#fff; }
.farm-name { font-size:20px; font-weight:700; }
.layout { display:flex; }
.sidebar { width:260px; background:#388e3c; color:#a8d5a2; display:flex; flex-direction:column; padding:16px 12px; min-height:100vh; transition: width 0.3s; }
.sidebar.collapsed { width:80px; }
.sidebar.collapsed .nav a span, .sidebar.collapsed .nav-section-title { display:none; }
.toggle-btn { cursor:pointer; font-size:20px; padding:10px; color:white; background:none; border:none; margin-bottom:20px; text-align:left; }
.nav { flex:1; padding:6px 6px 12px; }
.nav a { display:flex; align-items:center; gap:10px; text-decoration:none; color:#fff; padding:10px 12px; margin:4px 6px; border-radius:8px; transition:background 0.2s ease; }
.nav a.active, .nav a:hover { background: rgba(255,255,255,0.25); }
.nav-section-title { font-size:11px; letter-spacing:1.5px; opacity:0.9; margin:14px 6px 6px; }
.main { flex:1; padding:20px; background: linear-gradient(rgba(177,221,158,0.4), rgba(177,221,158,0.4)), url("image/cover.jpg") no-repeat center center; background-size:cover; }
.page-title { text-align:center; font-size:28px; font-weight:700; margin-bottom:10px; color:#18392b; }
.header-controls { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
.filter-controls select { padding:6px; margin-right:6px; }
.add-btn, .edit-btn, .delete-btn { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; margin:2px; }
.add-btn { background:var(--green3); color:#fff; }
.edit-btn { background:var(--green4); color:#fff; }
.delete-btn { background:#d9534f; color:#fff; }
table { width:100%; border-collapse:collapse; margin-top:15px; background:#fff; border-radius:10px; overflow:hidden; }
table th, table td { padding:12px; border-bottom:1px solid #eee; text-align:center; font-size:14px; }
table th { background:var(--green3); color:#fff; text-transform:uppercase; font-size:13px; letter-spacing:1px; }
table tr:nth-child(even) { background:#f9f9f9; }
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content:center; align-items:center; }
.modal form { background:#fff; padding:25px; border-radius:12px; display:flex; flex-direction:column; gap:12px; width:320px; box-shadow:0 6px 16px rgba(0,0,0,0.2); }
.modal h3 { text-align:center; color:#1b5e20; margin-top:0; }
.modal input, .modal select, .modal button { padding:8px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
.modal button[type=submit] { cursor:pointer; color:#fff; }
.toast { position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#333; color:#fff; padding:12px 20px; border-radius:6px; display:none; z-index:9999; }
.toast.success { background:#27ae60; }
.toast.error { background:#c0392b; }
</style>
</head>
<body>
<header class="header">
    <div class="farm-logo">
        <img src="image/logo.png" alt="Farm Logo">
        <span class="farm-name">Bicol Heritage Gamefarm</span>
    </div>
</header>

<div class="layout">
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

<main class="main">
    <h1 class="page-title">Mortality Records</h1>
    <div class="header-controls">
        <div class="filter-controls">
            <select id="filter_month">
                <option value="">All Months</option>
                <?php for($m=1;$m<=12;$m++): 
                    $selected = ($month==$m)?'selected':'';
                    echo "<option value='$m' $selected>".date("F", mktime(0,0,0,$m,1))."</option>";
                endfor;?>
            </select>
            <select id="filter_year">
                <option value="">All Years</option>
                <?php 
                    $minYearRes = $conn->query("SELECT MIN(YEAR(date)) as min_year FROM mortality");
                    $row = $minYearRes->fetch_assoc();
                    $minYear = $row['min_year'] ?? date("Y");
                    for($y=date("Y");$y>=$minYear;$y--){
                        $selected = ($year==$y)?'selected':'';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                ?>
            </select>
            <select id="filter_branch">
                <option value="">All Branches</option>
                <?php foreach($branches as $b){
                    $selected = ($branch_id==$b['branch_id'])?'selected':'';
                    echo "<option value='{$b['branch_id']}' $selected>{$b['branch_name']}</option>";
                } ?>
            </select>
        </div>
        <div>
            <button class="add-btn" onclick="openAddModal()">+ Add Record</button>
        </div>
    </div>

    <table>
    <tr>
        <th>Date</th>
        <th>Bloodline</th>
        <th>Branch</th>
        <th>Wing Band</th>
        <th>Leg Band</th>
        <th>Cause of Death</th>
        <th>Action</th>
    </tr>
    <?php if($result->num_rows > 0): ?>
        <?php while($row=$result->fetch_assoc()): ?>
        <tr>
            <td><?=htmlspecialchars($row['date'])?></td>
            <td><?=htmlspecialchars($row['bloodline_name'])?></td>
            <td><?=htmlspecialchars($row['branch_name'])?></td>
            <td><?=htmlspecialchars($row['wing_band'])?></td>
            <td><?=htmlspecialchars($row['leg_band'])?></td>
            <td><?=htmlspecialchars($row['cause_of_death'])?></td>
            <td>
                <button class="edit-btn" onclick='openEditModal(<?=json_encode($row)?>)'>Edit</button>
                <button class="delete-btn" onclick="openDeleteModal(<?=$row['id']?>)">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" style="text-align:center; color:#d9534f; font-weight:700;">No records found for the selected filters.</td>
        </tr>
    <?php endif; ?>
</table>
</main>
</div>

<!-- Add Modal -->
<div class="modal" id="addModal">
<form method="post">
    <h3>Add Mortality Record</h3>
    <input type="date" name="date" required>
    <label>Bloodline:</label>
    <select name="bloodline_id" required>
        <option value="">Select Bloodline</option>
        <?php foreach($bloodlines as $bl) echo "<option value='{$bl['bloodline_id']}'>{$bl['bloodline_name']}</option>"; ?>
    </select>
    <label>Branch:</label>
    <select name="branch_id" required>
        <option value="">Select Branch</option>
        <?php foreach($branches as $b) echo "<option value='{$b['branch_id']}'>{$b['branch_name']}</option>"; ?>
    </select>
    <input type="text" name="wing_band" placeholder="Wing Band" required>
    <input type="text" name="leg_band" placeholder="Leg Band" required>
    <input type="text" name="cause_of_death" placeholder="Cause of Death" required>
    <button type="submit" name="add_record" class="add-btn">Add</button>
    <button type="button" onclick="closeAddModal()">Cancel</button>
</form>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
<form method="post">
    <h3>Edit Mortality Record</h3>
    <input type="hidden" name="id" id="edit_id">
    <input type="date" name="date" id="edit_date" required>
    <label>Bloodline:</label>
    <select name="bloodline_id" id="edit_bloodline" required>
        <?php foreach($bloodlines as $bl) echo "<option value='{$bl['bloodline_id']}'>{$bl['bloodline_name']}</option>"; ?>
    </select>
    <label>Branch:</label>
    <select name="branch_id" id="edit_branch" required>
        <?php foreach($branches as $b) echo "<option value='{$b['branch_id']}'>{$b['branch_name']}</option>"; ?>
    </select>
    <input type="text" name="wing_band" id="edit_wing" placeholder="Wing Band" required>
    <input type="text" name="leg_band" id="edit_leg" placeholder="Leg Band" required>
    <input type="text" name="cause_of_death" id="edit_cause" placeholder="Cause of Death" required>
    <button type="submit" name="update_record" class="edit-btn">Update</button>
    <button type="button" onclick="closeEditModal()">Cancel</button>
</form>
</div>

<!-- Delete Modal -->
<div class="modal" id="deleteModal">
<form method="post">
    <h3>Confirm Delete</h3>
    <p>Are you sure you want to delete this record?</p>
    <input type="hidden" name="id" id="delete_id">
    <button type="submit" name="delete_record" class="delete-btn">Yes, Delete</button>
    <button type="button" onclick="closeDeleteModal()">Cancel</button>
</form>
</div>

<div class="toast" id="toast"></div>

<script>
// --- Filter Auto Reload ---
document.getElementById('filter_month').addEventListener('change', reload);
document.getElementById('filter_year').addEventListener('change', reload);
document.getElementById('filter_branch').addEventListener('change', reload);
function reload(){
    const m = document.getElementById('filter_month').value;
    const y = document.getElementById('filter_year').value;
    const b = document.getElementById('filter_branch').value;
    window.location.href = `mortality.php?month=${m}&year=${y}&branch_id=${b}`;
}

// --- Modals ---
function openAddModal(){ document.getElementById('addModal').style.display='flex'; }
function closeAddModal(){ document.getElementById('addModal').style.display='none'; }

function openEditModal(data){
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_date').value = data.date;
    document.getElementById('edit_wing').value = data.wing_band;
    document.getElementById('edit_leg').value = data.leg_band;
    document.getElementById('edit_cause').value = data.cause_of_death;
    document.getElementById('edit_bloodline').value = data.bloodline;
    document.getElementById('edit_branch').value = data.branch_id;
    document.getElementById('editModal').style.display='flex';
}
function closeEditModal(){ document.getElementById('editModal').style.display='none'; }

function openDeleteModal(id){
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteModal').style.display='flex';
}
function closeDeleteModal(){ document.getElementById('deleteModal').style.display='none'; }

// --- Toast ---
<?php if(isset($_SESSION['toast'])): ?>
let toast = document.getElementById('toast');
toast.innerText = "<?= $_SESSION['toast']['message'] ?>";
toast.className = "toast <?= $_SESSION['toast']['type'] ?>";
toast.style.display = 'block';
setTimeout(()=>{ toast.style.display='none'; }, 3000);
<?php unset($_SESSION['toast']); endif; ?>
</script>
</body>
</html>
