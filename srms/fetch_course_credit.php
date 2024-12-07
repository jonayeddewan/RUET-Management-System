<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (isset($_GET['courseCode'])) {
    $courseCode = $_GET['courseCode'];

    $sql = "SELECT CourseCredit FROM tblsubjects WHERE CourseCode = :courseCode";
    $query = $dbh->prepare($sql);
    $query->bindParam(':courseCode', $courseCode, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        echo json_encode(['CourseCredit' => $result->CourseCredit]);
    } else {
        echo json_encode(['CourseCredit' => null]);
    }
}
?>