<?php
// Start a session to access user data
session_start();

// Include database connection
include 'database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Fetch admin details from session
$admin_id = $_SESSION['admin_id'];
$admin_nom = $_SESSION['admin_nom'];
$admin_prenom = $_SESSION['admin_prenom'];

// Initialize variables for admin's profile information
$admin_email = '';
$default_picture = 'img/default_profile_picture.jpg'; // Default picture in 'img' folder
$profile_picture = $default_picture;

// Fetch the admin's profile from the database
$query = "SELECT email, profile_picture FROM Administrateur WHERE ID_Administrateur = :admin_id";
$stmt = $pdo->prepare($query); // Change $conn to $pdo
$stmt->bindParam(':admin_id', $admin_id);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if ($admin) {
    $admin_email = $admin['email'];
    $_SESSION['admin_email'] = $admin_email;  // Set the session email variable
    $profile_picture = $admin['profile_picture'] ?: $default_picture;
}

// Process profile update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['first_name'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    // Handle profile picture upload first
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "img/"; // Ensure the folder exists and is writable
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a valid image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            // Move the uploaded file
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $target_file;

                // Update the profile picture in the database
                $updatePicQuery = "UPDATE Administrateur SET profile_picture = :profile_picture WHERE ID_Administrateur = :admin_id";
                $updatePicStmt = $pdo->prepare($updatePicQuery); // Change $conn to $pdo
                $updatePicStmt->bindParam(':profile_picture', $profile_picture);
                $updatePicStmt->bindParam(':admin_id', $admin_id);
                $updatePicStmt->execute();

                // Update session variable for the profile picture
                $_SESSION['profile_picture'] = $profile_picture;

            } else {
                $_SESSION['error_message'] = "Sorry, there was an error uploading your profile picture.";
            }
        } else {
            $_SESSION['error_message'] = "File is not a valid image.";
        }
    }

    // Now update the admin's other profile information
    $updateQuery = "UPDATE Administrateur SET Nom = :last_name, Prenom = :first_name, email = :email WHERE ID_Administrateur = :admin_id";
    $updateStmt = $pdo->prepare($updateQuery); // Change $conn to $pdo
    $updateStmt->bindParam(':last_name', $last_name);
    $updateStmt->bindParam(':first_name', $first_name);
    $updateStmt->bindParam(':email', $email);
    $updateStmt->bindParam(':admin_id', $admin_id);

    if ($updateStmt->execute()) {
        // Set the success message in the session
        $_SESSION['success_message'] = "Profile updated successfully.";

        // Update session variables
        $_SESSION['admin_nom'] = $last_name;
        $_SESSION['admin_prenom'] = $first_name;
        $_SESSION['admin_email'] = $email;

        // Redirect to the same page to fetch updated data
        header("Location: profile.php");
        exit();
    }
}

// Process password change form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['current_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Fetch current password from the database
    $query = "SELECT mot_de_passe FROM Administrateur WHERE ID_Administrateur = :admin_id";
    $stmt = $pdo->prepare($query); // Change $conn to $pdo
    $stmt->bindParam(':admin_id', $admin_id);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($current_password, $admin['mot_de_passe'])) {
        // Update password if current password is correct
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updatePasswordQuery = "UPDATE Administrateur SET mot_de_passe = :new_password WHERE ID_Administrateur = :admin_id";
        $updatePasswordStmt = $pdo->prepare($updatePasswordQuery); // Change $conn to $pdo
        $updatePasswordStmt->bindParam(':new_password', $hashed_password);
        $updatePasswordStmt->bindParam(':admin_id', $admin_id);

        if ($updatePasswordStmt->execute()) {
            $_SESSION['success_message'] = "Password updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update password.";
        }
    } else {
        $_SESSION['error_message'] = "Current password is incorrect.";
    }
}

// Clear messages after displaying
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/04f0db2fa7.js" crossorigin="anonymous"></script>
    <title>Profile</title>
    <link rel="stylesheet" href="st.css">
</head>
<body>
    <div class="container">
        <?php include 'layout.php'; ?>
        <main>
            <h1>My Profile</h1>

            <!-- Display error message if available -->
            <?php if (isset($error_message)): ?>
                <div class="error-message" style="color: red;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Display success message if available -->
            <?php if (isset($success_message)): ?>
                <div class="success-message" style="color: green;">
                    <i class="fa fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <section class="info">
                <div class="container2">
                    <div class="profile-frame">
                        <div class="user-name">
                            <?php echo htmlspecialchars($admin_nom . ' ' . $admin_prenom); ?>
                        </div>
                        <h1 class="h">Edit your information here</h1>
                        <div class="right">
                            <form action="profile.php" method="post" class="edit-form" enctype="multipart/form-data">
                                <div class="user-img">
                                    <img id="photo" src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                                    <input type="file" id="file" name="profile_picture" style="display:none;">
                                    <label for="file" id="uploadbtn"><i class="fa fa-camera"></i></label>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="first_name" type="text" value="<?php echo htmlspecialchars($admin_prenom); ?>">
                                    <label>First Name</label>
                                    <i class="icon fa-solid fa-address-card"></i>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="last_name" type="text" value="<?php echo htmlspecialchars($admin_nom); ?>">
                                    <label>Last Name</label>
                                    <i class="icon fa-solid fa-address-card"></i>
                                </div>
                                <div class="input-wrap">
                                    <input class="edit-input" autocomplete="off" name="email" type="email" value="<?php echo htmlspecialchars($admin_email); ?>">
                                    <label>Email Address</label>
                                    <i class="icon fa-solid fa-envelope"></i>
                                </div>
                                <div class="save">
                                    <input type="submit" value="Save changes" class="btn">
                                </div>
                            </form>

                            <div class="change-password">
                                <p>Do you want to change your password? <a href="#" id="toggle-password-form">Click here</a></p>
                            </div>

                            <!-- Hidden password change form -->
                            <div id="password-form" style="display: none;">
                                <form action="profile.php" method="post" class="edit-form">
                                    <div class="input-wrap">
                                        <input class="edit-input" autocomplete="off" name="current_password" type="password" required>
                                        <label>Current Password</label>
                                        <i class="icon fa-solid fa-lock"></i>
                                    </div>
                                    <div class="input-wrap">
                                        <input class="edit-input" autocomplete="off" name="new_password" type="password" required>
                                        <label>New Password</label>
                                        <i class="icon fa-solid fa-lock"></i>
                                    </div>
                                    <div class="save">
                                        <input type="submit" value="Change Password" class="btn">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // JavaScript to toggle the visibility of the password form
        document.getElementById('toggle-password-form').addEventListener('click', function(e) {
            e.preventDefault();
            var passwordForm = document.getElementById('password-form');
            passwordForm.style.display = passwordForm.style.display === 'none' ? 'block' : 'none';
        });

        // Preview uploaded profile picture
        document.getElementById('file').addEventListener('change', function() {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo').src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        });

        // Fade out success and error messages after 3 seconds
        setTimeout(function () {
            const successMessage = document.querySelector('.success-message');
            const errorMessage = document.querySelector('.error-message');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
        }, 3000);
    </script>
</body>
</html>
