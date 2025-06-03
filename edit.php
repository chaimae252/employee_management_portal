<?php
// Start session and include database connection
session_start();
include 'database.php'; // Ensure this file initializes $pdo

// Initialize variables
$status_message = '';
$employee = [];

// Fetch employee data based on the employee ID from the URL
if (isset($_GET['id'])) {
    $ID_Employe = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM employe WHERE ID_Employe = :ID_Employe"); // Change $conn to $pdo
        $stmt->bindParam(':ID_Employe', $ID_Employe);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $status_message = 'Error fetching employee data: ' . $e->getMessage();
    }
}

// Handle form submission for editing the employee details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ID_Employe = $_POST['ID_Employe'];
    $Nom = $_POST['Nom'];
    $Prenom = $_POST['Prenom'];
    $Poste = $_POST['Poste'];
    $Departement = $_POST['Departement'];
    $date_d_embauche = $_POST['date_d_embauche'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];

    // Update employee details in the database
    try {
        $query = "UPDATE employe 
                  SET Nom = :Nom, Prenom = :Prenom, Poste = :Poste, ID_Departement = :Departement, date_d_embauche = :date_d_embauche, email = :email, telephone = :telephone
                  WHERE ID_Employe = :ID_Employe";
        $stmt = $pdo->prepare($query); // Change $conn to $pdo

        // Bind parameters
        $stmt->bindParam(':Nom', $Nom);
        $stmt->bindParam(':Prenom', $Prenom);
        $stmt->bindParam(':Poste', $Poste);
        $stmt->bindParam(':Departement', $Departement);
        $stmt->bindParam(':date_d_embauche', $date_d_embauche);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':ID_Employe', $ID_Employe);

        // Execute the update query
        $stmt->execute();

        // Redirect to employees.php with a success message
        header("Location: employees.php?status=success&message=" . urlencode('Employee updated successfully!'));
        exit();
    } catch (PDOException $e) {
        // Redirect with error message
        header("Location: edit.php?id=" . $ID_Employe . "&status=error&message=" . urlencode('Error updating employee: ' . $e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/04f0db2fa7.js" crossorigin="anonymous"></script>
    <title>Edit Employee</title>
    <link rel="stylesheet" href="add.css">
</head>
<body>
    <div class="container">
        <?php include 'layout.php'; ?>
        <main>
            <h1>Edit Employee</h1>

            <!-- Display success or error message if available as notification -->
            <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
                <div class="notification error show">
                    <i class="fa fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="notification success show">
                    <i class="fa fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <section class="right"> 
                <div class="container2" id="container2"> 
                    <div class="profile-frame">
                        <div class="ad">
                            <!-- Edit form -->
                            <form action="edit.php?id=<?php echo htmlspecialchars($employee['ID_Employe']); ?>" method="post" class="edit-form">
                                <!-- Displayed Employee ID field (readonly) -->
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="ID_Employe_display" type="text" value="<?php echo htmlspecialchars($employee['ID_Employe']); ?>" readonly>
                                    <label>ID Employee</label>
                                    <i class="icon fa-solid fa-address-card"></i>
                                </div>

                                <!-- Hidden Employee ID field (for submission) -->
                                <input type="hidden" name="ID_Employe" value="<?php echo htmlspecialchars($employee['ID_Employe']); ?>">

                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="Nom" type="text" value="<?php echo htmlspecialchars($employee['Nom']); ?>">
                                    <label>First Name</label>
                                    <i class="icon fa-solid fa-address-card"></i>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="Prenom" type="text" value="<?php echo htmlspecialchars($employee['Prenom']); ?>">
                                    <label>Last Name</label>
                                    <i class="icon fa-solid fa-address-card"></i>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="Poste" type="text" value="<?php echo htmlspecialchars($employee['Poste']); ?>">
                                    <label>Poste</label>
                                    <i class="icon fa-solid fa-briefcase"></i>
                                </div>
                                <div class="input-wrap">
                                    <select class="edit-input" autocomplete="off" name="Departement">
                                        <option value="" disabled>Select Department</option>
                                        <?php
                                        // Fetch departments dynamically from the database
                                        try {
                                            $stmt = $pdo->query("SELECT ID_Departement, Nom_Departement FROM departement"); // Change $conn to $pdo
                                            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($departments as $department) {
                                                echo '<option value="' . $department['ID_Departement'] . '" ' . ($employee['ID_Departement'] == $department['ID_Departement'] ? 'selected' : '') . '>' . $department['Nom_Departement'] . '</option>';
                                            }
                                        } catch (PDOException $e) {
                                            echo '<option value="">Error fetching departments</option>';
                                        }
                                        ?>
                                    </select>
                                    <label>Department</label>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="date_d_embauche" type="date" value="<?php echo htmlspecialchars($employee['date_d_embauche']); ?>">
                                    <label>Date Of Joining</label>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="email" type="email" value="<?php echo htmlspecialchars($employee['email']); ?>">
                                    <label>Email Address</label>
                                    <i class="icon fa-solid fa-envelope"></i>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="telephone" type="text" value="<?php echo htmlspecialchars($employee['telephone']); ?>">
                                    <label>Telephone</label>
                                    <i class="icon fa-solid fa-telephone"></i>
                                </div>
                                <div class="save">
                                    <input type="submit" value="Save Changes" class="btn">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Hide notification after 3 seconds
        setTimeout(function () {
            const notification = document.querySelector('.notification.show');
            if (notification) {
                notification.classList.remove('show');
            }
        }, 3000);
    </script>
</body>
</html>
