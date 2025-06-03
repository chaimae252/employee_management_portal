<?php
// Start a session to store admin information after login
session_start();

// Include the database connection
include 'database.php'; // Make sure this file initializes $pdo

// Initialize error message
$error_message = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare the SQL query to check if the admin exists
    $query = "SELECT * FROM Administrateur WHERE email = :email";
    $stmt = $pdo->prepare($query); // Change $conn to $pdo
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Fetch the admin from the database
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the admin exists
    if ($admin) {
        // Verify the password
        if (password_verify($password, $admin['mot_de_passe'])) {
            // Set the admin session and redirect to the dashboard
            $_SESSION['admin_id'] = $admin['ID_Administrateur'];
            $_SESSION['admin_nom'] = $admin['Nom']; // Use consistent variable naming
            $_SESSION['admin_prenom'] = $admin['Prenom']; // Optional: include first name

            header("Location: dashboard.php");
            exit();
        } else {
            // Incorrect password
            $error_message = "Invalid email or password.";
        }
    } else {
        // Admin not found
        $error_message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="profile-frame">
            <h1>Hello Admin!</h1>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="input-container">
                    <i class="fa fa-envelope icon"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-container">
                    <i class="fa fa-lock icon"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit">SIGN IN</button>
            </form>
            <div class="signup-text">
                <p>New Admin? <a href="signup.php">Sign up here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
