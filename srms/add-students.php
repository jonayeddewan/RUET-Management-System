<?php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        $studentname = $_POST['fullname'];
        $rollid = $_POST['rollid'];
        $registrationid = $_POST['registrationid'];
        $studentemail = $_POST['emailid'];
        $gender = $_POST['gender'];
        $department = $_POST['department'];
        $section = $_POST['section'];
        $series = $_POST['series'];
        $dob = $_POST['dob'];
        $fathername = $_POST['fathername'];
        $mothername = $_POST['mothername'];
        $contact = $_POST['contact'];
        $status = 1;

        // Check if student already exists
        $sql = "SELECT * FROM tblstudents WHERE RollId=:rollid AND RegistrationId=:registrationid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':rollid', $rollid, PDO::PARAM_STR);
        $query->bindParam(':registrationid', $registrationid, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $error = "Student Already Exists";
        } else {
            $sql = "INSERT INTO tblstudents(StudentName, RollId, RegistrationId, StudentEmail, Gender, Department, Section, Series, DOB, FatherName, MotherName, Contact, Status) 
                    VALUES(:studentname, :rollid, :registrationid, :studentemail, :gender, :department, :section, :series, :dob, :fathername, :mothername, :contact, :status)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':studentname', $studentname, PDO::PARAM_STR);
            $query->bindParam(':rollid', $rollid, PDO::PARAM_STR);
            $query->bindParam(':registrationid', $registrationid, PDO::PARAM_STR);
            $query->bindParam(':studentemail', $studentemail, PDO::PARAM_STR);
            $query->bindParam(':gender', $gender, PDO::PARAM_STR);
            $query->bindParam(':department', $department, PDO::PARAM_STR);
            $query->bindParam(':section', $section, PDO::PARAM_STR);
            $query->bindParam(':series', $series, PDO::PARAM_STR);
            $query->bindParam(':dob', $dob, PDO::PARAM_STR);
            $query->bindParam(':fathername', $fathername, PDO::PARAM_STR);
            $query->bindParam(':mothername', $mothername, PDO::PARAM_STR);
            $query->bindParam(':contact', $contact, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $dbh->lastInsertId();
            if ($lastInsertId) {
                $msg = "Student info added successfully";
            } else {
                $error = "Something went wrong. Please try again";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SRMS Admin | Student Admission</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="css/select2/select2.min.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
</head>

<body class="top-navbar-fixed">
    <div class="main-wrapper">

        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php'); ?>
        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">

                <!-- ========== LEFT SIDEBAR ========== -->
                <?php include('includes/leftbar.php'); ?>
                <!-- /.left-sidebar -->

                <div class="main-page">

                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Student Admission</h2>
                            </div>
                        </div>
                        <!-- /.row -->
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li class="active">Student Admission</li>
                                </ul>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <div class="container-fluid">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Fill the Student info</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <?php if ($msg) { ?>
                                            <div class="alert alert-success left-icon-alert" role="alert">
                                                <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                            </div>
                                        <?php } else if ($error) { ?>
                                            <div class="alert alert-danger left-icon-alert" role="alert">
                                                <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                            </div>
                                        <?php } ?>
                                        <form class="form-horizontal" method="post">

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Full Name</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="fullname" class="form-control" id="fullname" required="required" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Father's Name</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="fathername" class="form-control" id="fathername" required="required" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Mother's Name</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="mothername" class="form-control" id="mothername" required="required" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Roll Id</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="rollid" class="form-control" id="rollid" maxlength="7" required="required" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Registration Id</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="registrationid" class="form-control" id="registrationid" required="required" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Contact</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="contact" class="form-control" id="contact" required="required" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Email id</label>
                                                <div class="col-sm-10">
                                                    <input type="email" name="emailid" class="form-control" id="email" required="required" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Gender</label>
                                                <div class="col-sm-10">
                                                    <input type="radio" name="gender" value="Male" required="required" checked="">Male
                                                    <input type="radio" name="gender" value="Female" required="required">Female
                                                    <input type="radio" name="gender" value="Other" required="required">Other
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Department</label>
                                                <div class="col-sm-10">
                                                    <select name="department" class="form-control" id="default" required="required">
                                                        <option value="">Select Department</option>
                                                        <?php
                                                        $sql = "SELECT DISTINCT Department FROM tbldept";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $result) { ?>
                                                                <option value="<?php echo htmlentities($result->Department); ?>">
                                                                    <?php echo htmlentities($result->Department); ?>
                                                                </option>
                                                            <?php }
                                                        } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Section</label>
                                                <div class="col-sm-10">
                                                    <select name="section" class="form-control" id="default" required="required">
                                                        <option value="">Select Section</option>
                                                        <?php
                                                        $sql = "SELECT DISTINCT Section FROM tblclasses";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $result) { ?>
                                                                <option value="<?php echo htmlentities($result->Section); ?>">
                                                                    <?php echo htmlentities($result->Section); ?>
                                                                </option>
                                                            <?php }
                                                        } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="default" class="col-sm-2 control-label">Series</label>
                                                <div class="col-sm-10">
                                                    <select name="series" class="form-control" id="default" required="required">
                                                        <option value="">Select Series</option>
                                                        <?php
                                                        $sql = "SELECT DISTINCT Series FROM tblclasses";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $result) { ?>
                                                                <option value="<?php echo htmlentities($result->Series); ?>">
                                                                    <?php echo htmlentities($result->Series); ?>
                                                                </option>
                                                            <?php }
                                                        } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="date" class="col-sm-2 control-label">DOB</label>
                                                <div class="col-sm-10">
                                                    <input type="date" name="dob" class="form-control" id="date">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <button type="submit" name="submit" class="btn btn-primary">Add</button>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->
        </div>
        <!-- /.main-wrapper -->
        <script src="js/jquery/jquery-2.2.4.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>
        <script src="js/prism/prism.js"></script>
        <script src="js/select2/select2.min.js"></script>
        <script src="js/main.js"></script>
        <script>
            $(function ($) {
                $(".js-states").select2();
                $(".js-states-limit").select2({
                    maximumSelectionLength: 2
                });
                $(".js-states-hide").select2({
                    minimumResultsForSearch: Infinity
                });
            });
        </script>
</body>

</html>
<?php } ?>
