<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include('includes/config.php');

// Get the data from the request
$data = json_decode(file_get_contents('php://input'), true);

$studentName = $data['StudentName'] ?? 'Student';
$email = $data['StudentEmail'] ?? null;
$ctMarks = [
    'CT_1' => $data['CT_1'] ?? 'Not Published Yet',
    'CT_2' => $data['CT_2'] ?? 'Not Published Yet',
    'CT_3' => $data['CT_3'] ?? 'Not Published Yet',
    'CT_4' => $data['CT_4'] ?? 'Not Published Yet'
];
$assignment = $data['Assignment'] ?? 'Not Available';
$attendance = $data['Attendance'] ?? 'Not Available';

// Validate email
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Failed to send email to {$studentName}: Invalid email address ($email).";
    exit();
}

// Calculate average of the best 3 CT marks
$validMarks = array_filter($ctMarks, fn($mark) => is_numeric($mark));
rsort($validMarks);
$averageCT = count($validMarks) >= 3
    ? ceil(array_sum(array_slice($validMarks, 0, 3)) / 3)
    : 'Not Available';

// Email body content
$body = "<p>Dear $studentName,</p>
         <p>Here are your marks for the selected course:</p>
         <ul>
            <li>CT 1: {$ctMarks['CT_1']}</li>
            <li>CT 2: {$ctMarks['CT_2']}</li>
            <li>CT 3: {$ctMarks['CT_3']}</li>
            <li>CT 4: {$ctMarks['CT_4']}</li>
            <li>Average of Best 3 CTs: $averageCT</li>
            <li>Assignment: $assignment</li>
            <li>Attendance: $attendance</li>
         </ul>
         <p>Best Regards,<br>RUET ECE</p>";

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ruetecemailer@gmail.com';
    $mail->Password = 'vmwtflzdhqppllum';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('ruetecemailer@gmail.com', 'RUET ECE');
    $mail->addAddress($email, $studentName);

    $mail->isHTML(true);
    $mail->Subject = 'Your CT, Assignment & Attendance Marks';
    $mail->Body = $body;

    $mail->send();
    echo "Successfully sent to {$studentName} ($email).";
} catch (Exception $e) {
    echo "Failed to send email to {$studentName} ($email). Error: {$mail->ErrorInfo}.";
}
