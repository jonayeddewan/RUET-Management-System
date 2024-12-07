<?php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        $coursename = $_POST['coursename'];
        $coursecode = $_POST['coursecode'];
        $coursecredit = $_POST['coursecredit'];
        $department = $_POST['department'];
        $semester = $_POST['semester'];

        // Check for duplicate CourseCode
        $sql_check = "SELECT COUNT(*) FROM tblsubjects WHERE CourseCode = :coursecode";
        $query_check = $dbh->prepare($sql_check);
        $query_check->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
        $query_check->execute();
        $exists = $query_check->fetchColumn();

        if ($exists) {
            $error = "Course Code already exists. Please use a unique Course Code.";
        } else {
            // Proceed with insertion
            $sql = "INSERT INTO tblsubjects (CourseName, CourseCode, CourseCredit, Department, Semester)
                VALUES (:coursename, :coursecode, :coursecredit, :department, :semester)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':coursename', $coursename, PDO::PARAM_STR);
            $query->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
            $query->bindParam(':coursecredit', $coursecredit, PDO::PARAM_STR);
            $query->bindParam(':department', $department, PDO::PARAM_STR);
            $query->bindParam(':semester', $semester, PDO::PARAM_INT); // Correct binding

            $query->execute();
            $lastInsertId = $dbh->lastInsertId();
            if ($lastInsertId) {
                $msg = "Course Created successfully";
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
        <title>SRMS Admin Course Creation </title>
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
                                    <h2 class="title">Course Creation</h2>

                                </div>

                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li> Courses</li>
                                        <li class="active">Create Course</li>
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
                                                <h5>Create Subject</h5>
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
                                                    <label for="default" class="col-sm-2 control-label">Course Name</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="coursename" class="form-control"
                                                            id="default" placeholder="Course Name" required="required">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Course Code</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="coursecode" class="form-control"
                                                            id="default" placeholder="Course Code" required="required">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Course
                                                        Credit</label>
                                                    <div class="col-sm-10">
                                                        <input type="number" name="coursecredit" class="form-control"
                                                            id="default" placeholder="Credit" step="0.01"
                                                            required="required">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Department</label>
                                                    <div class="col-sm-10">
                                                        <select name="department" class="form-control" id="default"
                                                            required="required">
                                                            <option value="">Select Department</option>
                                                            <?php
                                                            $sql = "SELECT DISTINCT Department FROM tbldept";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                            if ($query->rowCount() > 0) {
                                                                foreach ($results as $result) { ?>
                                                                    <option
                                                                        value="<?php echo htmlentities($result->Department); ?>">
                                                                        <?php echo htmlentities($result->Department); ?>
                                                                    </option>
                                                            <?php }
                                                            } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="default" class="col-sm-2 control-label">Semester</label>
                                                    <div class="col-sm-10">
                                                        <input type="number" name="semester" class="form-control"
                                                            id="default" placeholder="Semester" required="required">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="col-sm-offset-2 col-sm-10">
                                                        <button type="submit" name="submit"
                                                            class="btn btn-primary">Submit</button>
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
            <script sr c="js/main.js"></script>
            <script>
                $(function($) {
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