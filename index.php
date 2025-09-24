<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gamefowl Mortality Records</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar" id="sidebar">
      <!-- Hamburger inside sidebar -->
      <button class="hamburger" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
      </button>

      <a href="dashboard.php" class="logo">
        <img src="logo.png" alt="Company Logo" />
      </a>
      <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span class="link-text">Dashboard</span></a></li>
      </ul>
      <hr>
      <ul>
        <li><a href="bloodline.php"><i class="fas fa-dna"></i> <span class="link-text">Bloodline</span></a></li>
        <li><a href="lineage.php"><i class="fas fa-sitemap"></i> <span class="link-text">Lineage</span></a></li>
        <li><a href="gallery.php"><i class="fas fa-images"></i> <span class="link-text">Gallery</span></a></li>
      </ul>
      <hr>
      <ul>
         <li><a href="mortality.php"><i class="fas fa-skull-crossbones"></i> <span class="link-text">Mortality</span></a></li>
        <li><a href="disease.php"><i class="fas fa-virus"></i> <span class="link-text">Disease</span></a></li>
        <li><a href="cure.php"><i class="fas fa-pills"></i> <span class="link-text">Cure</span></a></li>
      </ul>
      <hr>
      <ul>
        <li><a href="sales.php"><i class="fas fa-chart-line"></i> <span class="link-text">Sales Report</span></a></li>
        <li><a href="settings.php"><i class="fas fa-cog"></i> <span class="link-text">Settings</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span class="link-text">Logout</span></a></li>
      </ul>
    </div>

    <main class="content">
        <header class="header">
            <h1>Mortality Records</h1>
            <div class="controls">
                <input type="text" id="search" placeholder="Search...">
                <button onclick="document.getElementById('addForm').style.display='block'">➕ Add Record</button>
            </div>
        </header>

        <section class="stats">
            <div class="card">🐓 
                <?php 
                    $res = $conn->query("SELECT COUNT(*) as total FROM mortality");
                    $row = $res->fetch_assoc();
                    echo "<h2>".$row['total']."</h2><p>Total Deaths</p>";
                ?>
            </div>
            <div class="card">💉 
                <?php 
                    $res = $conn->query("SELECT cause_of_death, COUNT(*) as cnt FROM mortality GROUP BY cause_of_death ORDER BY cnt DESC LIMIT 1");
                    if ($res->num_rows > 0) {
                        $row = $res->fetch_assoc();
                        echo "<h2>".$row['cause_of_death']."</h2><p>Most Common Cause</p>";
                    } else {
                        echo "<h2>—</h2><p>No Data</p>";
                    }
                ?>
            </div>
            <div class="card">🧬 
                <?php 
                    $res = $conn->query("SELECT bloodline, COUNT(*) as cnt FROM mortality GROUP BY bloodline ORDER BY cnt DESC LIMIT 1");
                    if ($res->num_rows > 0) {
                        $row = $res->fetch_assoc();
                        echo "<h2>".$row['bloodline']."</h2><p>Top Bloodline</p>";
                    } else {
                        echo "<h2>—</h2><p>No Data</p>";
                    }
                ?>
            </div>
        </section>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bloodline</th>
                    <th>Wing Band</th>
                    <th>Leg Band</th>
                    <th>Cause of Death</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $result = $conn->query("SELECT * FROM mortality ORDER BY date DESC");
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>".$row['date']."</td>
                            <td>".$row['bloodline']."</td>
                            <td>".$row['wing_band']."</td>
                            <td>".$row['leg_band']."</td>
                            <td><span class='tag'>".$row['cause_of_death']."</span></td>
                            <td>
                                <a href='edit.php?id=".$row['id']."'>✏️</a>
                                <a href='delete.php?id=".$row['id']."' onclick=\"return confirm('Are you sure you want to delete this record?');\">🗑️</a>
                            </td>
                        </tr>";
                    }
                ?>
            </tbody>
        </table>
    </main>

    <!-- Modal for Adding -->
    <div id="addForm" class="modal">
        <form action="insert.php" method="post">
            <h2>Add Record</h2>
            <label>Date:</label>
            <input type="date" name="date" required>
            <label>Bloodline:</label>
            <input type="text" name="bloodline" required>
            <label>Wing Band:</label>
            <input type="text" name="wing_band">
            <label>Leg Band:</label>
            <input type="text" name="leg_band">
            <label>Cause of Death:</label>
            <input type="text" name="cause_of_death" required>
            <button type="submit">Save</button>
            <button type="button" onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
        </form>
    </div>
</body>
</html>
