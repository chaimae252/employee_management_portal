<?php
// Start the session to access admin details


// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get the current page to highlight the active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <title>Dashboard</title>
    <link rel="stylesheet" href="lay.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar Section -->
        <aside>
            <div class="toggle">
                <div class="logo">
                    <img src="img/Aptiv_logo.png" alt="Logo">
                </div>
                <div class="close" id="close-btn">
                    <span class="material-icons-sharp">close</span>
                </div>
            </div>
            <div class="sidebar">
                <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <span class="material-icons-sharp">dashboard</span>
                    <h3>Dashboard</h3>
                </a>
                <a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">
                    <span class="material-icons-sharp">account_circle</span>
                    <h3>Profile</h3>
                </a>
                <a href="employees.php" class="<?= $current_page == 'employees.php' ? 'active' : '' ?>">
                    <span class="material-icons-sharp">people</span>
                    <h3>Employees</h3>
                </a>
                <a href="attendance.php" class="<?= $current_page == 'attendance.php' ? 'active' : '' ?>">
                    <span class="material-icons-sharp">inventory</span>
                    <h3>Attendance</h3>
                </a>
                <a href="projects.php" class="<?= $current_page == 'projects.php' ? 'active' : '' ?>">
                    <span class="material-icons-sharp">work_outline</span>
                    <h3>Projects</h3>
                </a>
                <a href="logout.php">
                    <span class="material-icons-sharp">logout</span>
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>
        <!-- End of Sidebar Section -->
    </div>
    <script src="index.js"></script>
</body>
</html>
