<?php
// Start session and include the database connection
session_start();
include 'database.php'; // Ensure this file contains the connection to your database

// Fetch filter and search parameters from the GET request
$department = isset($_GET['filter-department']) ? $_GET['filter-department'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date = isset($_GET['date_d_embauche']) ? $_GET['date_d_embauche'] : '';

// Build the query to fetch attendance records with optional filters
$query = "
    SELECT e.ID_Employe, e.Nom, e.Prenom, d.Nom_Departement, p.Date AS Date_Presence, p.Heure_arrivee, p.Heure_depart, p.Status
    FROM employe e
    INNER JOIN presence p ON e.ID_Employe = p.ID_Employe
    LEFT JOIN departement d ON e.ID_Departement = d.ID_Departement
    WHERE 1=1
";

// Success message (if exists)
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);  // Clear the message after showing it

// Filter by department if selected
if ($department != 'all') {
    $query .= " AND e.ID_Departement = :department";
}

// Search by employee name or ID
if (!empty($search)) {
    $query .= " AND (e.Nom LIKE :search OR e.Prenom LIKE :search OR e.ID_Employe LIKE :search)";
}

// Filter by date if selected
if (!empty($date)) {
    $query .= " AND p.Date = :date"; // Changed to filter by presence date
}

// Prepare and execute the query
$stmt = $pdo->prepare($query);

// Bind parameters
if ($department != 'all') {
    $stmt->bindParam(':department', $department);
}
if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt->bindParam(':search', $searchTerm);
}
if (!empty($date)) {
    $stmt->bindParam(':date', $date);
}

$stmt->execute();
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/04f0db2fa7.js" crossorigin="anonymous"></script>
    <title>Attendance</title>
    <link rel="stylesheet" href="att.css">
    <style>
        .success-msg {
    background-color: #d4edda;
    color: #155724;
    padding: 15px 20px;
    margin-bottom: 20px;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    position: fixed; /* Fix the position at the top */
    top: 20px; /* Adjust top spacing as needed */
    left: 57%;
    transform: translateX(-50%);
    width: auto;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
    transition: opacity 0.4s ease, transform 0.4s ease;
    z-index: 100; /* Make sure it stays on top */
}

.success-msg i {
    margin-right: 10px;
    font-size: 22px;
    color: #28a745;
}

.success-msg.fade-out {
    opacity: 0;
    transform: translateY(-10px);
}

    </style>
</head>
<body>
    <div class="container">
        <?php include 'layout.php'; ?>
        <main>
            <h1>Attendance</h1>

            <!-- Success Message -->
            <?php if (!empty($successMessage)): ?>
                <div class="success-msg">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <section class="right">
                <div class="container2">
                    <div class="profile-frame">
                        
                        <!-- Filters and Search -->
                        <form action="attendance.php" method="GET" class="filters-search">
                            <!-- Department Filter -->
                            <select name="filter-department" id="filter-department">
                                <option value="all">All Departments</option>
                                <?php
                                // Fetch departments from the database
                                $deptQuery = "SELECT ID_Departement, Nom_Departement FROM departement";
                                $deptStmt = $pdo->prepare($deptQuery);
                                $deptStmt->execute();
                                $departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($departments as $dept) {
                                    echo "<option value=\"{$dept['ID_Departement']}\" " . ($department == $dept['ID_Departement'] ? 'selected' : '') . ">{$dept['Nom_Departement']}</option>";
                                }
                                ?>
                            </select>

                            <!-- Search Input -->
                            <input type="text" name="search" placeholder="Search employee..." id="search-employee" value="<?php echo htmlspecialchars($search); ?>">

                            <!-- Date Filter -->
                            <input autocomplete="off" name="date_d_embauche" type="date" value="<?php echo htmlspecialchars($date); ?>">

                            <!-- Search Button -->
                            <button type="submit">Search</button>
                        </form>

                        <!-- Add Attendance for New Day -->
                        <a href="today.php" class="btn">Add Attendance</a>

                        <!-- Attendance Table -->
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID Employee</th>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>Department</th>
                                        <th>Date of Presence</th> <!-- Updated to show presence date -->
                                        <th>Arrival Time</th>
                                        <th>Departure Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($list)): ?>
                                        <?php foreach ($list as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['ID_Employe']); ?></td>
                                                <td><?php echo htmlspecialchars($row['Nom']); ?></td>
                                                <td><?php echo htmlspecialchars($row['Prenom']); ?></td>
                                                <td><?php echo htmlspecialchars($row['Nom_Departement']); ?></td>
                                                <td><?php echo htmlspecialchars($row['Date_Presence']) ?: 'Not Recorded'; ?></td> <!-- Updated to display Date of Presence -->
                                                <td><?php echo htmlspecialchars($row['Heure_arrivee']) ?: 'Not Recorded'; ?></td>
                                                <td><?php echo htmlspecialchars($row['Heure_depart']) ?: 'Not Recorded'; ?></td>
                                                <td><?php echo htmlspecialchars($row['Status']) ?: 'Not Recorded'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8">No attendance records found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const successMessage = document.querySelector('.success-msg');
    if (successMessage) {
        setTimeout(function() {
            // Add fade-out class to initiate the transition
            successMessage.classList.add('fade-out');
            // Remove the element from the DOM after the fade-out completes
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 400); // Match the duration of the CSS transition (0.4s)
        }, 3000); // Hide after 3 seconds
    }
});

    </script>
</body>
</html>
