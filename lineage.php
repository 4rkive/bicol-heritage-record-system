<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Handle Add Bloodline ---
if (isset($_POST['save_bloodline'])) {
    $new_bloodline = trim($_POST['new_bloodline']);
    if (!empty($new_bloodline)) {
        $stmt = $conn->prepare("INSERT INTO bloodline (bloodline_name) VALUES (?)");
        $stmt->bind_param("s", $new_bloodline);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// --- Handle Edit Bloodline ---
if (isset($_POST['edit_bloodline'])) {
    $id = intval($_POST['bloodline_id']);
    $name = trim($_POST['bloodline_name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE bloodline SET bloodline_name=? WHERE bloodline_id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// --- Handle Delete Bloodline ---
if (isset($_POST['delete_bloodline'])) {
    $id = intval($_POST['bloodline_id']);
    $stmt = $conn->prepare("DELETE FROM bloodline WHERE bloodline_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// --- Handle filter (dropdown selection) ---
$selected_bloodline = isset($_GET['bloodline']) ? $_GET['bloodline'] : 'all';

// Fetch broodcocks with lineage + bloodline name
$sql = "SELECT b.cock_id, 
               b.name AS broodcock_name, 
               bl.bloodline_name, 
               b.color, 
               b.marking, 
               b.wing_band, 
               b.leg_band, 
               b.remarks,
               p.pen_number
        FROM broodcocks b
        LEFT JOIN pens p ON b.pen_number = p.pen_number
        LEFT JOIN bloodline bl ON b.bloodline_id = bl.bloodline_id";

if ($selected_bloodline != "all") {
    $sql .= " WHERE b.bloodline_id = '" . $conn->real_escape_string($selected_bloodline) . "'";
}
$result = $conn->query($sql);

// Fetch all bloodlines for dropdown
$bloodline_sql = "SELECT bloodline_id, bloodline_name FROM bloodline ORDER BY bloodline_name ASC";
$bloodline_result = $conn->query($bloodline_sql);

// Detect current page for sidebar highlight
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lineage - Bicol Golden Crown</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
:root {
    --blue1: #0aa5ff;
    --blue2: #6ab5ceff;
    --blue3: #003366;
    --bg: #f6f8fb;
}
body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: var(--bg);
    color: #1a1a1a;
}

/* Header */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 20px;
    background: var(--blue2);
    color: #fff;
}
.farm-logo { display: flex; align-items: center; gap: 12px; }
.farm-logo img {
    width: 50px; height: 50px; object-fit: cover;
    border-radius: 50%; border: 2px solid rgba(255,255,255,0.6);
    background: #fff;
}
.farm-name { font-size: 20px; font-weight: 700; }

/* Layout */
.layout { display: flex; }

/* Sidebar */
.sidebar {
    width: 260px;
    background: linear-gradient(180deg, var(--blue1), var(--blue3));
    color: #fff;
    display: flex;
    flex-direction: column;
    padding: 16px 12px;
    min-height: calc(100vh - 64px);
}
.nav { flex: 1; padding: 6px 6px 12px; }
.nav a {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; color: #fff;
    padding: 10px 12px; margin: 4px 6px;
    border-radius: 8px;
}
.nav a.active, .nav a:hover { background: rgba(255,255,255,0.25); }
.nav-section-title { font-size: 11px; letter-spacing: 1.5px; opacity: 0.9; margin: 14px 6px 6px; }

/* Main */
.main { flex: 1; padding: 20px; }
.top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.top-bar button { 
    padding:8px 12px; 
    border-radius:6px; 
    border:none; 
    cursor:pointer; 
    background:linear-gradient(to right,#0aa5ff,#003366); 
    color:#fff; 
    margin-left:8px; 
}
.right-buttons {
    margin-left: auto; /* ✅ Pushes buttons to the right */
    display: flex;
    gap: 8px;
}
.cards {
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); 
    gap: 16px;
}
.card {
    background: linear-gradient(to bottom, #4facfe, #0066cc);
    color: #fff;
    padding: 15px;
    border-radius: 12px;
}
.card h3 { margin:0 0 10px; font-size:18px; text-align:center; }
.card p { margin: 4px 0; font-size:14px; }

/* Dropdown */
.dropdown { position: relative; display: inline-block; }
.dropdown-btn {
    padding: 8px 14px; background: #0aa5ff; color: #fff;
    border: none; border-radius: 6px; cursor: pointer;
}
.dropdown-content {
    display: none;
    position: absolute;
    background: #fff;
    min-width: 220px;
    border: 1px solid #ccc;
    border-radius: 8px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 99;
}
.dropdown-content .item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 6px 10px; border-bottom: 1px solid #eee;
}
.dropdown-content .item span { font-size: 14px; }
.dropdown-content .item button {
    margin-left: 4px; padding: 3px 6px; font-size: 12px;
    border: none; border-radius: 4px; cursor: pointer;
}
.dropdown-content .item .edit { background: #ffc107; color: #000; }
.dropdown-content .item .delete { background: #dc3545; color: #fff; }
.dropdown-content .item:last-child { border-bottom: none; }
.dropdown:hover .dropdown-content { display: block; }

@media (max-width:980px){
    .layout{flex-direction:column;}
    .sidebar{width:100%; min-height:auto;}
    .main{padding:10px;}
    .cards{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<header class="header">
    <div class="farm-logo">
        <img src="image/logo.png" alt="Farm Logo" />
        <span class="farm-name">Bicol Golden Crown</span>
    </div>
</header>

<div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="nav">
            <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <div class="nav-section-title">PROFILE</div>
            <a href="lineage.php" class="<?= ($current_page == 'lineage.php') ? 'active' : ''; ?>">
                <i class="fas fa-dna"></i> Lineage
            </a>
            
            <div class="nav-section-title">FEEDS</div>
            <a href="purchase.php" class="<?= ($current_page == 'purchase.php') ? 'active' : ''; ?>">
                <i class="fas fa-cart-plus"></i> Purchase
            </a>
            <a href="consumption.php" class="<?= ($current_page == 'consumption.php') ? 'active' : ''; ?>">
                <i class="fas fa-bowl-food"></i> Consumption
            </a>

            <div class="nav-section-title">HEALTH</div>
            <a href="mortality.php" class="<?= ($current_page == 'mortality.php') ? 'active' : ''; ?>">
                <i class="fas fa-skull-crossbones"></i> Mortality
            </a>
            <a href="disease.php" class="<?= ($current_page == 'disease.php') ? 'active' : ''; ?>">
                <i class="fas fa-virus"></i> Disease
            </a>
            <a href="cured.php" class="<?= ($current_page == 'cured.php') ? 'active' : ''; ?>">
                <i class="fas fa-notes-medical"></i> Cured
            </a>

            <div class="nav-section-title">REPORT</div>
            <a href="sales.php" class="<?= ($current_page == 'sales.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Sales
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main">
        <div class="top-bar">
            <!-- ✅ Bloodline Manager Dropdown -->
            <div class="dropdown">
                <button class="dropdown-btn">Bloodlines ▼</button>
                <div class="dropdown-content">
                    <!-- Add new -->
                    <div class="item">
                        <form method="POST" style="display:flex; width:100%;">
                            <input type="text" name="new_bloodline" placeholder="New bloodline" style="flex:1; padding:4px;" required>
                            <button type="submit" name="save_bloodline" style="background:#0aa5ff;color:#fff;">Add</button>
                        </form>
                    </div>
                    <?php while($b = $bloodline_result->fetch_assoc()): ?>
                        <div class="item">
                            <span><?= htmlspecialchars($b['bloodline_name']) ?></span>
                            <div>
                                <button class="edit" onclick="editBloodline(<?= $b['bloodline_id'] ?>, '<?= htmlspecialchars($b['bloodline_name']) ?>')">Edit</button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="bloodline_id" value="<?= $b['bloodline_id'] ?>">
                                    <button type="submit" name="delete_bloodline" class="delete" onclick="return confirm('Delete this bloodline?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- ✅ Right-side buttons -->
            <div class="right-buttons">
                <button onclick="window.print()">Download</button>
                <button>Add</button>
            </div>
        </div>

        <!-- Hidden Edit Form -->
        <div id="editForm" style="display:none; margin-bottom:20px; padding:10px; border:1px solid #ccc; border-radius:6px; width:250px;">
            <h3>Edit Bloodline</h3>
            <form method="POST">
                <input type="hidden" name="bloodline_id" id="edit_id">
                <input type="text" name="bloodline_name" id="edit_name" required>
                <br><br>
                <button type="submit" name="edit_bloodline">Save Changes</button>
                <button type="button" onclick="document.getElementById('editForm').style.display='none'">Cancel</button>
            </form>
        </div>

        <!-- Broodcock Cards -->
        <div class="cards">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="card">';
                    echo '<h3>Pen ' . ($row["pen_number"] ?? "N/A") . '</h3>';
                    echo '<p><b>Color:</b> ' . $row["color"] . '</p>';
                    echo '<p><b>Bloodline:</b> ' . $row["bloodline_name"] . '</p>';
                    echo '<p><b>Name:</b> ' . $row["broodcock_name"] . '</p>';
                    echo '<p><b>Marking:</b> ' . $row["marking"] . '</p>';
                    echo '<p><b>Wing Band:</b> ' . $row["wing_band"] . '</p>';
                    echo '<p><b>Leg Band:</b> ' . $row["leg_band"] . '</p>';
                    echo '<p><b>Remarks:</b> ' . $row["remarks"] . '</p>';
                    echo '</div>';
                }
            } else {
                echo "<p>No broodcocks available.</p>";
            }
            ?>
        </div>
    </div>
</div>

<script>
function editBloodline(id, name) {
    document.getElementById('editForm').style.display = 'block';
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
}
</script>
</body>
</html>
