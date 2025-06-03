<?php
session_start();
require 'database.php';  // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all employees
$query = "SELECT ID_employe, Prenom, Nom FROM employe";
$stmt = $pdo->prepare($query);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceDate = $_POST['attendance_date'];
    $attendance = $_POST['attendance']; // Contains present/absent status for all employees
    $arrivalTimes = $_POST['arrival_time']; // Contains arrival times
    $departureTimes = $_POST['departure_time']; // Contains departure times

    // Validate inputs
    if (empty($attendanceDate) || empty($attendance)) {
        $_SESSION['error_message'] = "Attendance date and employee statuses are required.";
    } else {
        try {
            // Begin transaction
            $pdo->beginTransaction();

            foreach ($employees as $employee) {
                $employeeId = $employee['ID_employe'];
                $status = $attendance[$employeeId] ?? 'absent'; // Default to 'absent' if not set
                $arrival = $arrivalTimes[$employeeId] ?: 'Not Recorded'; // Handle missing time
                $departure = $departureTimes[$employeeId] ?: 'Not Recorded'; // Handle missing time

                // Insert or update the attendance record for this employee
                $sql = "INSERT INTO presence (ID_Employe, Date, Heure_arrivee, Heure_depart, Status) 
                        VALUES (:employeeId, :date, :arrival, :departure, :status)
                        ON DUPLICATE KEY UPDATE Heure_arrivee = :arrival2, Heure_depart = :departure2, Status = :status2";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':employeeId' => $employeeId,
                    ':date' => $attendanceDate,
                    ':arrival' => $arrival,
                    ':departure' => $departure,
                    ':status' => $status,
                    ':arrival2' => $arrival,
                    ':departure2' => $departure,
                    ':status2' => $status
                ]);
            }

            // Commit the transaction
            $pdo->commit();
            
            // Redirect to attendance.php with a success message
            $_SESSION['success_message'] = "Attendance successfully saved!";
            header("Location: attendance.php");  // Redirect to attendance page
            exit();
        } catch (Exception $e) {
            // Rollback the transaction on error
            $pdo->rollBack();
            $_SESSION['error_message'] = $e->getMessage(); // Set error message
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Add Attendance</title>
    <link rel="stylesheet" href="td.css">
    <style>
        /* Style for success message */
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
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        .success-msg.active {
            visibility: visible;
            opacity: 1;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        .error-msg.active {
            visibility: visible;
            opacity: 1;
        }
    </style>
    <script>
        // Function to show success message
        function showSuccessMessage() {
            const successMsg = document.getElementById('success-msg');
            successMsg.classList.add('active'); // Show success message

            setTimeout(() => {
                successMsg.classList.remove('active'); // Hide after 5 seconds
            }, 5000);
        }

        // Function to show error message
        function showErrorMessage() {
            const errorMsg = document.getElementById('error-msg');
            errorMsg.classList.add('active'); // Show error message

            setTimeout(() => {
                errorMsg.classList.remove('active'); // Hide after 5 seconds
            }, 5000);
        }
    </script>
</head>
<body>
    <div class="container">
        <?php include 'layout.php'; ?>
        <main>
            <h1>Add Attendance</h1>

            <!-- Success Message -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div id="error-msg" class="error-msg">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; ?>
                </div>
                <script>showErrorMessage();</script>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <section class="right">
                <div class="container2">
                    <div class="profile-frame">
                        <form action="today.php" method="POST">
                            <!-- Date Picker for Attendance -->
                            <label for="attendance_date">Attendance Date:</label>
                            <input type="date" name="attendance_date" required>

                            <!-- Buttons to mark all as Present or Absent -->
                            <button type="button" onclick="markAll('present')">Mark All Present</button>
                            <button type="button" onclick="markAll('absent')">Mark All Absent</button>

                            <!-- Table to display employees and select attendance status -->
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID Employee</th>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>Status</th>
                                        <th>Arrival Time</th>
                                        <th>Departure Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $employee): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($employee['ID_employe']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['Nom']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['Prenom']); ?></td>
                                            <td>
                                                <select name="attendance[<?php echo $employee['ID_employe']; ?>]" required>
                                                    <option value="present">Present</option>
                                                    <option value="absent">Absent</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="time" name="arrival_time[<?php echo $employee['ID_employe']; ?>]">
                                            </td>
                                            <td>
                                                <input type="time" name="departure_time[<?php echo $employee['ID_employe']; ?>]">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- Submit Button -->
                            <button type="submit">Submit Attendance</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // JavaScript function to mark all employees' attendance
        function markAll(status) {
            const selects = document.querySelectorAll('select[name^="attendance"]');
            selects.forEach(select => select.value = status);
        }
    </script>
</body>
</html>
