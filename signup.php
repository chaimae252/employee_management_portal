<?php
// Start the session
session_start();

// Include the database connection file
require 'database.php'; // Make sure this file initializes $pdo

// Initialize variables to store form data and error messages
$firstname = $lastname = $id_employee = $email = $password = '';
$errors = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $id_employee = trim($_POST['ID']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validate form inputs
    if (empty($firstname)) {
        $errors[] = "First name is required.";
    }
    if (empty($lastname)) {
        $errors[] = "Last name is required.";
    }
    if (empty($id_employee)) {
        $errors[] = "Employee ID is required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // Check for existing email and employee ID in the database
    if (empty($errors)) {
        try {
            // Prepare statements to check for existing email and ID
            $stmt_email = $pdo->prepare("SELECT COUNT(*) FROM administrateur WHERE email = :email");
            $stmt_id = $pdo->prepare("SELECT COUNT(*) FROM administrateur WHERE ID_Administrateur = :id_employee");
            
            // Bind parameters and execute
            $stmt_email->bindParam(':email', $email);
            $stmt_id->bindParam(':id_employee', $id_employee);
            $stmt_email->execute();
            $stmt_id->execute();
            
            // Check if email or ID already exists
            if ($stmt_email->fetchColumn() > 0) {
                $errors[] = "An account with this email already exists.";
            }
            if ($stmt_id->fetchColumn() > 0) {
                $errors[] = "An account with this employee ID already exists.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    // If no errors, proceed to insert into the database
    if (empty($errors)) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Prepare an SQL query to insert the new admin using PDO
            $sql = "INSERT INTO administrateur (Nom, Prenom, ID_Administrateur, email, mot_de_passe) 
                    VALUES (:lastname, :firstname, :id_employee, :email, :hashed_password)";

            // Prepare the statement
            $stmt = $pdo->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':id_employee', $id_employee);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':hashed_password', $hashed_password);

            // Execute the statement
            if ($stmt->execute()) {
                // Store admin info in session
                $_SESSION['admin_id'] = $pdo->lastInsertId(); // Get the last inserted ID
                $_SESSION['admin_prenom'] = $firstname;
                $_SESSION['admin_nom'] = $lastname;
                
                // Redirect to the dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Something went wrong. Please try again.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="profile-frame">
            <h1>Create an Admin Account</h1>

            <!-- Display error messages if any -->
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="post">
                <div class="input-container">
                    <i class="fa fa-user icon"></i>
                    <input type="text" name="firstname" placeholder="First Name" value="<?php echo htmlspecialchars($firstname); ?>" required>
                </div>
                <div class="input-container">
                    <i class="fa fa-user icon"></i>
                    <input type="text" name="lastname" placeholder="Last Name" value="<?php echo htmlspecialchars($lastname); ?>" required>
                </div>
                <div class="input-container">
                    <i class="fa fa-id-card icon"></i>
                    <input type="text" name="ID" placeholder="Employee ID" value="<?php echo htmlspecialchars($id_employee); ?>" required>
                </div>
                <div class="input-container">
                    <i class="fa fa-envelope icon"></i>
                    <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="input-container">
                    <i class="fa fa-lock icon"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit">SIGN UP</button>
            </form>
            <div class="signup-text">
                <p>Already an Admin? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
