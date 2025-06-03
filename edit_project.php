<?php
// Start session
session_start();

// Include the database connection
include 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Check if ID_Projet is provided in the URL
if (isset($_GET['ID_Projet'])) {
    $project_id = $_GET['ID_Projet'];

    // Fetch project details for editing
    $query = "SELECT * FROM projects WHERE ID_Projet = :project_id";
    $stmt = $pdo->prepare($query);  // Make sure to use $pdo here
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if project exists
    if (!$project) {
        echo "Project not found.";
        exit();
    }
} else {
    echo "No project selected.";
    exit();
}

// Handle the form submission for updating project details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get updated data from form
    $project_name = $_POST['nom_projet'];
    $description = $_POST['description_projet'];
    $start_date = $_POST['date_debut'];
    $end_date = $_POST['date_fin'];

    // Handle logo upload if provided
    if (!empty($_FILES['project_logo']['name'])) {
        $logo_path = '' . basename($_FILES['project_logo']['name']);
        move_uploaded_file($_FILES['project_logo']['tmp_name'], $logo_path);
    } else {
        $logo_path = $project['logo'];
    }

    // Update query for the project
    $update_query = "UPDATE projects SET Nom_Projet = :project_name, Description_Projet = :description, Date_Debut = :start_date, Date_Fin = :end_date, logo = :logo_path WHERE ID_Projet = :project_id";
    $stmt = $pdo->prepare($update_query);
    $stmt->bindParam(':project_name', $project_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':logo_path', $logo_path);
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: projects.php?status=success&message=Project updated successfully");
        exit();
    } else {
        echo "Error updating project.";
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
    <title>Edit Project</title>
    <link rel="stylesheet" href="addprojects.css">
</head>
<body>
    <div class="container">
        <?php include 'layout.php'; ?>
        <main>
            <h1>Edit Project</h1>
            <!-- Display success message if present -->
                <?php if (isset($_GET['status']) && $_GET['status'] == 'success' && isset($_GET['message'])): ?>
                    <div class="notification show" id="successNotification">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

            <section class="right">
                <div class="container2">
                    <div class="profile-frame">
                    <form class="edit-form" method="POST" action="edit_project.php?ID_Projet=<?php echo $project['ID_Projet']; ?>" enctype="multipart/form-data">
                            <!-- Include the project ID to identify the project in update_project.php -->
                            <input type="hidden" name="ID_Projet" value="<?php echo $project['ID_Projet']; ?>">

                            <div class="input-wrap">
                                <label for="nom_projet">Project Name</label>
                                <input type="text" class="edit-input" id="nom_projet" name="nom_projet" value="<?php echo htmlspecialchars($project['Nom_Projet']); ?>" required>
                            </div>

                            <div class="input-wrap">
                                <label for="description_projet">Project Description</label>
                                <input type="text" class="edit-input" id="description_projet" name="description_projet" value="<?php echo htmlspecialchars($project['Description_Projet']); ?>" required>
                            </div>

                            <div class="input-wrap">
                                <label for="date_debut">Start Date</label>
                                <input type="date" class="edit-input" id="date_debut" name="date_debut" value="<?php echo $project['Date_Debut']; ?>" required>
                            </div>

                            <div class="input-wrap">
                                <label for="date_fin">End Date</label>
                                <input type="date" class="edit-input" id="date_fin" name="date_fin" value="<?php echo $project['Date_Fin']; ?>" required>
                            </div>

                            <div class="logo-upload">
                                <label for="upload-logo">Upload logo</label>
                                <input type="file" id="upload-logo" accept="image/*" name="project_logo"/>
                                <div id="file-success-message" class="success-message" style="display: none;"></div>
                            </div>

                            <div class="save">
                                <button type="submit" class="btn">Save Changes</button>
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
