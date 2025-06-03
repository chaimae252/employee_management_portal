<?php
// Start session to access admin data
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Include database connection
include 'database.php'; // This should define $pdo as the connection

// Fetch admin details including the profile picture from the database
$admin_id = $_SESSION['admin_id'];
$query = "SELECT Nom, Prenom, profile_picture FROM administrateur WHERE ID_Administrateur = :admin_id";
$stmt = $pdo->prepare($query); // Use $pdo instead of $conn
$stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Extract the necessary admin details
$admin_prenom = $admin['Prenom'];
$admin_nom = $admin['Nom'];
$photoPath = !empty($admin['profile_picture']) ? $admin['profile_picture'] : 'path/to/default_profile_picture.jpg'; // Default picture if not set

// Fetch total number of employees
$query = "SELECT COUNT(*) AS totalEmployees FROM Employe";
$stmt = $pdo->query($query); // Use $pdo instead of $conn
$totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['totalEmployees'];

// Fetch total number of departments
$query = "SELECT COUNT(*) AS totalDepartments FROM Departement";
$stmt = $pdo->query($query); // Use $pdo instead of $conn
$totalDepartments = $stmt->fetch(PDO::FETCH_ASSOC)['totalDepartments'];

// Fetch total number of projects
$query = "SELECT COUNT(*) AS totalProjects FROM Projects";
$stmt = $pdo->query($query); // Use $pdo instead of $conn
$totalProjects = $stmt->fetch(PDO::FETCH_ASSOC)['totalProjects'];

// Fetch recent activity (latest employees added)
$query = "
    SELECT e.ID_Employe, e.Nom, e.Prenom, e.Poste, d.Nom_Departement AS Departement, e.date_d_embauche, e.email, e.telephone 
    FROM Employe e
    LEFT JOIN Departement d ON e.ID_Departement = d.ID_Departement
    ORDER BY e.date_d_embauche DESC
    LIMIT 5"; // Adjust the limit based on how many recent activities you want to show

$stmt = $pdo->query($query); // Use $pdo instead of $conn
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <title>Dashboard</title>
    <link rel="stylesheet" href="stylee.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar Section -->
        <?php include 'layout.php'; ?>

        <!-- Main Content -->
        <main>
            <h1>Welcome <?php echo htmlspecialchars($admin_prenom . ' ' . $admin_nom); ?>,</h1>
            
            <!-- General Information -->
            <div class="statistics">
                <div class="employees">
                    <div class="status">
                        <div class="infos">
                            <h3>Total Employees</h3>
                            <h1><?php echo htmlspecialchars($totalEmployees); ?></h1>
                        </div>
                        <div class="progress">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <p>60%</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="departements">
                    <div class="status">
                        <div class="infos">
                            <h3>Total Departments</h3>
                            <h1><?php echo htmlspecialchars($totalDepartments); ?></h1>
                        </div>
                        <div class="progress">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <p>100%</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="projects">
                    <div class="status">
                        <div class="infos">
                            <h3>Total Projects</h3>
                            <h1><?php echo htmlspecialchars($totalProjects); ?></h1>
                        </div>
                        <div class="progress">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <p>100%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of General Information -->
            
            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID Employee</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Date of Joining</th>
                            <th>Email</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivities as $activity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($activity['ID_Employe']); ?></td>
                                <td><?php echo htmlspecialchars($activity['Nom']); ?></td>
                                <td><?php echo htmlspecialchars($activity['Prenom']); ?></td>
                                <td><?php echo htmlspecialchars($activity['Poste']); ?></td>
                                <td><?php echo htmlspecialchars($activity['Departement']); ?></td>
                                <td><?php echo htmlspecialchars($activity['date_d_embauche']); ?></td>
                                <td><?php echo htmlspecialchars($activity['email']); ?></td>
                                <td><?php echo htmlspecialchars($activity['telephone']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="employees.php">Show All</a>
            </div>
            <!-- End of Recent Activity -->
        
            <!-- Right Section -->
            <div class="profile-info">
                <div class="profile">
                    <a href="profile.php" class="profile-link">
                        <div class="profile-photo">
                            <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Profile Photo">
                        </div>
                    </a>
                </div>
                <div class="buttons">
                    <a href="addemployee.php" id="addemployee">
                        <span class="material-icons-sharp">person_add</span>
                        <h3>Add New Employee</h3>
                    </a>
                    <a href="projects.php" id="view">
                        <span class="material-icons-sharp">view_list</span>
                        <h3>View Projects</h3>
                    </a>
                </div>
            </div>
        </main>
    </div>
    <script src="index.js"></script>
</body>
</html>