<?php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
} else {
    if (isset($_GET['stid'])) {
        $stid = intval($_GET['stid']);
        $sql = "DELETE FROM tblstudents WHERE id=:stid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':stid', $stid, PDO::PARAM_INT);
        $query->execute();
        header("Location: manage-students.php"); // Redirect to manage students page after deletion
    }
}
?>