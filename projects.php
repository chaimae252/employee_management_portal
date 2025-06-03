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

// Handle deletion if the form is submitted
if (isset($_POST['confirm_delete_project'])) {
    $project_id = $_POST['project_id'];

    // Prepare and execute the DELETE query
    $delete_query = "DELETE FROM projects WHERE ID_Projet = :project_id";
    $stmt = $pdo->prepare($delete_query);
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirect to the same page with a success message
        header("Location: projects.php?status=success&message=Project deleted successfully");
        exit();
    } else {
        $status_message = "Error deleting project.";
    }
}

// Fetch projects from the database
$query = "SELECT * FROM projects"; // Corrected table name
$stmt = $pdo->query($query);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/04f0db2fa7.js" crossorigin="anonymous"></script>
    <title>Projects</title>
    <link rel="stylesheet" href="projects.css">
</head>
<body>
    <div class="container">
        <?php include 'layout.php'; ?>
        <main>
            <h1>Projects</h1>

            <!-- Display success message if present -->
            <?php if (isset($_GET['status']) && $_GET['status'] == 'success' && isset($_GET['message'])): ?>
                <div class="notification show" id="successNotification">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <a href="add_project.php" class="add-project-button">Add New Project</a>
            <section class="right">
                <div class="container2">
                    <div class="projects-container">
                        <?php foreach ($projects as $project): ?>
                            <div class="project-frame">
                                <img src="<?php echo htmlspecialchars($project['logo']); ?>" alt="<?php echo htmlspecialchars($project['Nom_Projet']); ?> Logo" style="width: 100%; height: auto; border-radius: 1rem;">
                                <h2 class="project-title"><?php echo htmlspecialchars($project['Nom_Projet']); ?></h2>
                                <p class="project-description"><?php echo htmlspecialchars($project['Description_Projet']); ?></p>
                                <div class="project-dates">
                                    <span><strong>Start Date:</strong> <?php echo htmlspecialchars($project['Date_Debut']); ?></span>
                                    <span><strong>End Date:</strong> <?php echo htmlspecialchars($project['Date_Fin']); ?></span>
                                </div>
                                <!-- Edit Icon -->
                                <a href="edit_project.php?ID_Projet=<?php echo $project['ID_Projet']; ?>" class="edit-icon">
                                    <i class="fa fa-edit" aria-hidden="true" style="color: red; font-size: 15px;"></i>
                                </a>
                                <!-- Delete Icon -->
                                <a href="#" class="delete-icon" onclick="showDeleteProjectModal(<?php echo $project['ID_Projet']; ?>)">
                                    <i class="fa fa-trash" aria-hidden="true" style="color: red; font-size: 15px;"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Confirmation Modal for Project Deletion -->
    <div id="deleteProjectModal" class="modal">
        <div class="modal-content">
            <h2>Are you sure?</h2>
            <p>Do you really want to delete this project?</p>
            <form method="POST" id="deleteProjectForm">
                <input type="hidden" name="project_id" id="projectId" value="">
                <div class="modal-buttons">
                    <button type="submit" name="confirm_delete_project" class="confirm-button">Confirm</button>
                    <button type="button" class="cancel-button" onclick="closeDeleteProjectModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show modal function for project deletion
        function showDeleteProjectModal(projectId) {
            document.getElementById('projectId').value = projectId;
            document.getElementById('deleteProjectModal').style.display = 'flex';
        }

        // Close modal function for project deletion
        function closeDeleteProjectModal() {
            document.getElementById('deleteProjectModal').style.display = 'none';
        }

        // Hide notification after a few seconds and remove it from the DOM
        setTimeout(function() {
            const notification = document.getElementById('successNotification');
            if (notification) {
                notification.classList.add('fade-out'); // Apply fade-out animation

                // Remove the element from the DOM after the transition ends
                notification.addEventListener('transitionend', function() {
                    notification.remove();
                });
            }
        }, 3000);
    </script>
</body>
</html>
