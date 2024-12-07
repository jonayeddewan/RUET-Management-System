<?php
session_start();
include('includes/config.php');

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// Get form data from query parameters
$department = $_GET['department'] ?? null;
$series = $_GET['series'] ?? null;
$semester = $_GET['semester'] ?? null;
$course = $_GET['course'] ?? null;

if (!$department || !$series || !$semester || !$course) {
    die("Invalid input. Please try again.");
}

// Fetch students and their marks
$sql = "SELECT 
            s.StudentName, s.RollId, s.StudentEmail,
            m.CT_1, m.CT_2, m.CT_3, m.CT_4, 
            m.Assignment, m.Attendance
        FROM tblstudents s
        LEFT JOIN tblmarks m ON s.RollId = m.RollId
        INNER JOIN tblregistration r ON s.RollId = r.RollId
        WHERE s.Department = :department 
          AND s.Series = :series 
          AND r.Semester = :semester 
          AND r.RegisteredCourse = :course";

$query = $dbh->prepare($sql);
$query->execute([
    ':department' => $department,
    ':series' => $series,
    ':semester' => $semester,
    ':course' => $course
]);

$students = $query->fetchAll(PDO::FETCH_OBJ);

if (empty($students)) {
    die("No students found for the selected filters.");
}

// Initialize email-sending process
$mail = new PHPMailer(true);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sending Emails</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script>
        async function sendEmails(students) {
            for (let i = 0; i < students.length; i++) {
                const student = students[i];
                document.getElementById('status').innerHTML = `Sending result to ${student.StudentName}...`;

                // Make an AJAX request to send the email
                const response = await fetch('send-email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(student)
                });
                const result = await response.text();

                // Show the result
                const log = document.getElementById('log');
                const div = document.createElement('div');
                div.innerHTML = result;
                log.appendChild(div);
            }

            document.getElementById('status').innerHTML = 'All emails sent!';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const students = <?php echo json_encode($students); ?>;
            sendEmails(students);
        });
    </script>
</head>

<body>
    <div class="container">
        <h1>Email Sending Progress</h1>
        <p id="status"></p>
        <div id="log" style="margin-top: 20px;"></div>
    </div>
</body>

</html>