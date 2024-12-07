<?php
session_start();
error_reporting(0);
include ('includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        $teachername = $_POST['fullname'];
        $teacherid = $_POST['teacherid'];
        $teacheremail = $_POST['emailid'];
        $number = $_POST['phonenumber'];
        $gender = $_POST['gender'];
        $deptartment = $_POST['department'];
        $date = $_POST['date'];
        $designation = $_POST['designation'];
        $status = $_POST['status'];
        $sql = "INSERT INTO  tblteachers(TeacherName,TeacherId,TeacherEmail,TeacherPhone,Gender,Department,JoiningDate,Designation,Status)
                 VALUES(:teachername,:teacherid,:teacheremail,:number,:gender,:department,:date,:designation,:status)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':teachername', $teachername, PDO::PARAM_STR);
        $query->bindParam(':teacherid', $teacherid, PDO::PARAM_STR);
        $query->bindParam(':teacheremail', $teacheremail, PDO::PARAM_STR);
        $query->bindParam(':number', $number, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':department', $department, PDO::PARAM_STR);
        $query->bindParam(':date', $date, PDO::PARAM_STR);
        $query->bindParam(':designation', $designation, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();
        if ($lastInsertId) {
            $msg = "Teacher info added successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }

    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SRMS Admin| Teacher's Information< </title>
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
            <?php include ('includes/topbar.php'); ?>
            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">

                    <!-- ========== LEFT SIDEBAR ========== -->
                    <?php include ('includes/leftbar.php'); ?>
                    <!-- /.left-sidebar -->

                    <div class="main-page">

                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Teachers</h2>

                                </div>

                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>

                                        <li class="active">Teachers</li>
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
                                                <h5>Fill the Teachers info</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">
                                            <?php if ($msg) { ?>
                                                <div class="alert alert-success left-icon-alert" role="alert">
                                                    <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                                </div><?php } else if ($error) { ?>
                                                    <div class="alert alert-danger left-icon-alert" role="alert">
                                                        <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                                    </div>
                                            <?php } ?>
                                            <form class="form-horizontal" method="post">

                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Full Name</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="fullname" class="form-control"
                                                            id="fullanme" required="required" autocomplete="off">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Teacher Id</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="teacherid" class="form-control" id="rollid"
                                                            maxlength="10" required="required" autocomplete="off">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Email id</label>
                                                    <div class="col-sm-10">
                                                        <input type="email" name="emailid" class="form-control" id="email"
                                                            required="required" autocomplete="off"
                                                            placeholder="x@teacher.ruet.ac.bd">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Phone Number</label>
                                                    <div class="col-sm-10">
                                                        <input type="number" name="phonenumber" class="form-control"
                                                            id="number" required="required" autocomplete="off"
                                                            placeholder="01XXXXXXXXX">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Gender</label>
                                                    <div class="col-sm-10">
                                                        <input type="radio" name="gender" value="Male" required="required"
                                                            checked="">Male <input type="radio" name="gender" value="Female"
                                                            required="required">Female <input type="radio" name="gender"
                                                            value="Other" required="required">Other
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Department</label>
                                                    <div class="col-sm-10">
                                                        <select name="department" class="form-control" id="default"
                                                            required="required">
                                                            <option value="">Select Department</option>
                                                            <?php $sql = "SELECT * from tbldept";
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
                                                    <label for="default" class="col-sm-2 control-label">Joining Date
                                                    </label>
                                                    <div class="col-sm-10">
                                                        <input type="date" name="date" class="form-control" id="date"
                                                            required="required" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Desigation</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="designation" class="form-control"
                                                            id="designation" required="required" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Status</label>
                                                    <div class="col-sm-10">
                                                        <input type="radio" name="status" value="Employed"
                                                            required="required" checked="">Employed <input type="radio"
                                                            name="status" value="On Leave" required="required">On Leave
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-offset-2 col-sm-10">
                                                        <button type="submit" name="submit"
                                                            class="btn btn-primary">Add</button>
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
<?PHP } ?>