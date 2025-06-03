<?php
// Start session and include database connection
session_start();
include 'database.php';

// Initialize status message variable
$status_message = '';

// Fetch departments for the dropdown
$departments = [];
try {
    $stmt = $pdo->query("SELECT ID_Departement, Nom_Departement FROM departement"); // Change $conn to $pdo
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $status_message = 'Error fetching departments: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize form data
    $ID_Employe = htmlspecialchars($_POST['ID_Employe']);
    $Nom = htmlspecialchars($_POST['Nom']);
    $Prenom = htmlspecialchars($_POST['Prenom']);
    $Poste = htmlspecialchars($_POST['Poste']);
    $Departement = htmlspecialchars($_POST['Departement']); // This should be the ID of the department
    $date_d_embauche = htmlspecialchars($_POST['date_d_embauche']);
    $email = htmlspecialchars($_POST['email']);
    $telephone = htmlspecialchars($_POST['telephone']);

    // Insert employee data into the database
    try {
        $query = "INSERT INTO employe (ID_Employe, Nom, Prenom, Poste, ID_Departement, date_d_embauche, email, telephone) 
                  VALUES (:ID_Employe, :Nom, :Prenom, :Poste, :Departement, :date_d_embauche, :email, :telephone)";
        $stmt = $pdo->prepare($query); // Change $conn to $pdo

        // Bind the parameters
        $stmt->bindParam(':ID_Employe', $ID_Employe);
        $stmt->bindParam(':Nom', $Nom);
        $stmt->bindParam(':Prenom', $Prenom);
        $stmt->bindParam(':Poste', $Poste);
        $stmt->bindParam(':Departement', $Departement); // Now this should be a valid ID
        $stmt->bindParam(':date_d_embauche', $date_d_embauche);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);

        // Execute the statement
        $stmt->execute();
        
        // Set a session message and redirect
        $_SESSION['status_message'] = 'Employee added successfully!';
        header("Location: employees.php?status=success&message=Employee added successfully");
        exit();
    } catch (PDOException $e) {
        // Handle any errors
        $status_message = 'Error: Could not add employee. ' . $e->getMessage();
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
    <title>Add Employee</title>
    <link rel="stylesheet" href="add.css">
</head>
<body>
    <div class="container">
        <?php include 'layout.php'; ?>
        <main>
            <h1>Add Employee</h1>

            <!-- Display success or error message if available as a notification -->
            <?php if ($status_message): ?>
                <div class="notification error show">
                    <i class="fa fa-times-circle"></i>
                    <?php echo htmlspecialchars($status_message); ?>
                </div>
            <?php endif; ?>

            <section class="right"> 
                <div class="container2" id="container2"> 
                    <div class="profile-frame">
                        <div class="ad">
                            <form action="addemployee.php" method="post" class="edit-form">
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="ID_Employe" type="text" required>
                                    <label>ID Employee</label>
                                    <i class="icon fa-solid fa-address-card"></i>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="Nom" type="text" required>
                                    <label>First Name</label>
                                    <i class="icon fa-solid fa-address-card"></i>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="Prenom" type="text" required>
                                    <label>Last Name</label>
                                    <i class="icon fa-solid fa-address-card"></i>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="Poste" type="text" required>
                                    <label>Position</label>
                                    <i class="icon fa-solid fa-briefcase"></i>
                                </div>
                                <div class="input-wrap">
                                    <select class="edit-input" name="Departement" required>
                                        <option value="" disabled selected>Select Department</option>
                                        <?php foreach ($departments as $department): ?>
                                            <option value="<?php echo $department['ID_Departement']; ?>"><?php echo htmlspecialchars($department['Nom_Departement']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label>Department</label>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="date_d_embauche" type="date" required>
                                    <label>Date Of Joining</label>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="email" type="email" required>
                                    <label>Email Address</label>
                                    <i class="icon fa-solid fa-envelope"></i>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="telephone" type="text" required>
                                    <label>Phone</label>
                                    <i class="icon fa-solid fa-phone"></i>
                                </div>
                                <div class="save">
                                    <input type="submit" value="Add Employee" class="btn">
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
