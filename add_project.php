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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_projet = $_POST['nom_projet'];
    $description_projet = $_POST['description_projet'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];

    // Handle file upload
    if (isset($_FILES['project_logo']) && $_FILES['project_logo']['error'] == 0) {
        $target_dir = "img\\"; // Directory where the logo will be uploaded
        $target_file = $target_dir . basename($_FILES["project_logo"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a valid image
        $check = getimagesize($_FILES["project_logo"]["tmp_name"]);
        if ($check !== false) {
            // Move the uploaded file
            if (move_uploaded_file($_FILES["project_logo"]["tmp_name"], $target_file)) {
                // Prepare an insert statement
                $sql = "INSERT INTO projects (Nom_Projet, Description_Projet, Date_Debut, Date_Fin, Logo) 
                        VALUES (:nom_projet, :description_projet, :date_debut, :date_fin, :logo)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nom_projet', $nom_projet);
                $stmt->bindParam(':description_projet', $description_projet);
                $stmt->bindParam(':date_debut', $date_debut);
                $stmt->bindParam(':date_fin', $date_fin);
                $stmt->bindParam(':logo', $target_file);

                if ($stmt->execute()) {
                    // Redirect to projects.php with success message
                    header("Location: projects.php?status=success&message=Project added successfully!");
                    exit();
                } else {
                    $status_message = "Failed to add the project.";
                }
            } else {
                $status_message = "Sorry, there was an error uploading your file.";
            }
        } else {
            $status_message = "File is not a valid image.";
        }
    } else {
        $status_message = "Error in file upload.";
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
    <title>Add Projects</title>
    <link rel="stylesheet" href="addprojects.css">
</head>
<body>
    <div class="container">
        <?php include 'layout.php'; ?>
        <main>
            <h1>Add Projects</h1>
            <section class="right">
                <div class="container2">
                    <div class="profile-frame">
                        <!-- Display status message -->
                        <?php if (!empty($status_message)): ?>
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($status_message); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Form starts here -->
                        <form class="edit-form" method="POST" action="add_project.php" enctype="multipart/form-data">
                            <div class="input-wrap">
                                <label for="nom_projet">Project Name</label>
                                <input type="text" class="edit-input" id="nom_projet" name="nom_projet" required>
                            </div>

                            <div class="input-wrap">
                                <label for="description_projet">Project Description</label>
                                <input type="text" class="edit-input" id="description_projet" name="description_projet" required>
                            </div>

                            <div class="input-wrap">
                                <label for="date_debut">Start Date</label>
                                <input type="date" class="edit-input" id="date_debut" name="date_debut" required>
                            </div>

                            <div class="input-wrap">
                                <label for="date_fin">End Date</label>
                                <input type="date" class="edit-input" id="date_fin" name="date_fin" required>
                            </div>

                            <div class="logo-upload">
                                <label for="upload-logo">
                                    Upload logo
                                </label>
                                <input type="file" id="upload-logo" accept="image/*" name="project_logo"/>
                                <div id="file-success-message" class="success-message" style="display: none;"></div> <!-- New div for success message -->
                            </div>
                            <div class="save">
                                <button type="submit" class="btn">Add Project</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
    // Fade-out effect for the success message
    setTimeout(function() {
        const messageBox = document.querySelector('.success-message');
        if (messageBox) {
            messageBox.classList.add('fade-out');
        }
    }, 3000);

    const fileInput = document.getElementById("upload-logo");
    const fileSuccessMessage = document.getElementById("file-success-message");

    fileInput.addEventListener("change", () => {
        let [file] = fileInput.files;

        if (file) {
            fileSuccessMessage.textContent = "File selected successfully!";
            fileSuccessMessage.style.display = "block";
        } else {
            fileSuccessMessage.style.display = "none";
        }
    });
    </script>

    <style>
    /* Hide empty success messages */
    .success-message:empty {
        display: none;
    }
    </style>
</body>
</html>
