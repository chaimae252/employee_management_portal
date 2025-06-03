<?php
// Start session
session_start();

// Include the database connection
include 'database.php'; // Ensure this file sets up $pdo
$status_message = '';

// Check for success or error messages passed via GET parameters
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $status_message = htmlspecialchars($_GET['message']);
}

// Handle deletion if the form is submitted
if (isset($_POST['confirm_delete'])) {
    $employee_id = $_POST['employee_id'];

    // Prepare and execute the DELETE query
    $delete_query = "DELETE FROM Employe WHERE ID_Employe = :employee_id";
    $stmt = $pdo->prepare($delete_query); // Change $conn to $pdo
    $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirect to employees.php with a success message
        header("Location: employees.php?status=success&message=Employee deleted successfully");
        exit();
    } else {
        $status_message = "Error deleting employee.";
    }
}

// Fetch the search, department filter, and date filter input if provided
$search = isset($_GET['search']) ? $_GET['search'] : '';
$department = isset($_GET['filter-department']) ? $_GET['filter-department'] : 'all'; // 'all' for no filter
$date = isset($_GET['date_d_embauche']) ? $_GET['date_d_embauche'] : ''; // Date filter

// Fetch the list of employees with their department names, optionally filtered by search, department, or date
$query = "
    SELECT e.ID_Employe, e.Nom, e.Prenom, e.Poste, e.date_d_embauche, e.email, e.telephone, d.Nom_Departement AS department_name
    FROM Employe e
    LEFT JOIN Departement d ON e.ID_Departement = d.ID_Departement
    WHERE 1=1
";

// Search by employee name, ID, or position if search term is provided
if (!empty($search)) {
    $query .= " AND (e.Nom LIKE :search OR e.Prenom LIKE :search OR e.ID_Employe LIKE :search)";
}

// Filter by department if one is selected (other than 'all')
if ($department !== 'all') {
    $query .= " AND e.ID_Departement = :department";
}

// Filter by date if a date is provided
if (!empty($date)) {
    $query .= " AND e.date_d_embauche = :date_d_embauche";
}

$stmt = $pdo->prepare($query); // Change $conn to $pdo

// Bind the search term if provided
if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt->bindParam(':search', $searchTerm);
}

// Bind the department filter if provided
if ($department !== 'all') {
    $stmt->bindParam(':department', $department, PDO::PARAM_INT);
}

// Bind the date filter if provided
if (!empty($date)) {
    $stmt->bindParam(':date_d_embauche', $date);
}

$stmt->execute();
$listquery = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/04f0db2fa7.js" crossorigin="anonymous"></script>
    <title>Employees</title>
    <link rel="stylesheet" href="emp.css">
</head>
<body>
    <div class="container">
        <?php include 'layout.php'; ?>

        <main>
            <h1>Employees</h1>

            <!-- Display success message if available as notification -->
            <?php if ($status_message): ?>
                <div class="notification success show">
                    <i class="fa fa-check-circle"></i>
                    <?php echo $status_message; ?>
                </div>
            <?php endif; ?>

            <section class="list">
                <div class="container2">
                    <div class="profile-frame">
                        <div class="list-employees">
                            <div class="here">
                                <h2 class="h">List of Employees</h2>
                            </div>
                            <!-- Filter search form -->
                            <form action="employees.php" method="GET" class="filters-search">
                                <!-- Department Filter -->
                                <select name="filter-department" id="filter-department">
                                    <option value="all">All Departments</option>
                                    <?php
                                    // Fetch departments from the database
                                    $deptQuery = "SELECT ID_Departement, Nom_Departement FROM Departement";
                                    $deptStmt = $pdo->prepare($deptQuery); // Change $conn to $pdo
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

                            <div class="button">
                                <a href="addemployee.php" id="addemploye">
                                    <span class="material-icons-sharp">person_add</span>
                                    <h3>Add New Employee</h3>
                                </a>
                            </div>

                            <div class="table-container">
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
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($listquery as $list): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($list['ID_Employe']); ?></td>
                                                <td><?php echo htmlspecialchars($list['Nom']); ?></td>
                                                <td><?php echo htmlspecialchars($list['Prenom']); ?></td>
                                                <td><?php echo htmlspecialchars($list['Poste']); ?></td>
                                                <td><?php echo htmlspecialchars($list['department_name']); ?></td>
                                                <td><?php echo htmlspecialchars($list['date_d_embauche']); ?></td>
                                                <td><?php echo htmlspecialchars($list['email']); ?></td>
                                                <td><?php echo htmlspecialchars($list['telephone']); ?></td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $list['ID_Employe']; ?>">
                                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="#" onclick="showDeleteModal(<?php echo $list['ID_Employe']; ?>)">
                                                        <i class="fa fa-trash" aria-hidden="true" style="color: red;"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Confirmation Modal -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <h2>Are you sure?</h2>
                <p>Do you really want to delete this employee?</p>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="employee_id" id="employeeId" value="">
                    <div class="modal-buttons">
                        <button type="submit" name="confirm_delete" class="confirm-button">Confirm</button>
                        <button type="button" class="cancel-button" onclick="closeDeleteModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Show modal function
            function showDeleteModal(employeeId) {
                document.getElementById('employeeId').value = employeeId;
                document.getElementById('deleteModal').style.display = 'flex';
            }

            // Close modal function
            function closeDeleteModal() {
                document.getElementById('deleteModal').style.display = 'none';
            }

            // Hide notification after a few seconds
            setTimeout(function() {
                const notification = document.querySelector('.notification.show');
                if (notification) {
                    notification.classList.remove('show');
                }
            }, 3000);
        </script>
    </div>
</body>
</html>
