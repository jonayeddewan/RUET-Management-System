<?php
session_start();
include('includes/config.php');

// Check if the admin is logged in
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    // Handle Decline action (removal from tblregistration)
    if (isset($_POST['action']) && $_POST['action'] == 'decline') {
        $rollId = intval($_POST['RollId']);
        $semester = $_POST['Semester'];
        $courseCode = $_POST['CourseCode']; // Specific course to decline

        // Delete the specific registration for the given RollId, Semester, and CourseCode
        $sql = "DELETE FROM tblbackregistration WHERE RollId = :rollId AND Semester = :semester AND RegisteredCourse = :courseCode";
        $query = $dbh->prepare($sql);
        $query->bindParam(':rollId', $rollId, PDO::PARAM_INT);
        $query->bindParam(':semester', $semester, PDO::PARAM_STR);
        $query->bindParam(':courseCode', $courseCode, PDO::PARAM_STR);
        $query->execute();

        // Delete the specific registration for the given RollId, Semester, and CourseCode
        $sql = "DELETE FROM tblbackmanageregistration WHERE RollId = :rollId AND Semester = :semester AND CourseCode = :courseCode";
        $query = $dbh->prepare($sql);
        $query->bindParam(':rollId', $rollId, PDO::PARAM_INT);
        $query->bindParam(':semester', $semester, PDO::PARAM_STR);
        $query->bindParam(':courseCode', $courseCode, PDO::PARAM_STR);
        $query->execute();

        $_SESSION['msg'] = "Registration Declined Successfully!";
        header('location: manage-back-registration.php');
    }

    // Fetch approved registrations (individual rows for each course)
    $sql = "SELECT 
                tblbackmanageregistration.id,
                tblbackmanageregistration.RollId,
                tblstudents.StudentName,
                tblbackmanageregistration.CourseCode,
                tblsubjects.CourseName,
                tblbackmanageregistration.Semester,
                tblbackmanageregistration.RegistrationTime
            FROM tblbackmanageregistration
            JOIN tblstudents ON tblbackmanageregistration.RollId = tblstudents.RollId
            JOIN tblsubjects ON tblbackmanageregistration.CourseCode = tblsubjects.CourseCode
            WHERE tblbackmanageregistration.RegistrationStatus = 1
            ORDER BY tblbackmanageregistration.RegistrationTime DESC";

    $query = $dbh->prepare($sql);
    $query->execute();
    $registrations = $query->fetchAll(PDO::FETCH_OBJ);
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Manage Students</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" href="css/prism/prism.css" media="screen">
        <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css" />
        <link rel="stylesheet" href="css/main.css" media="screen">
        <script src="js/modernizr/modernizr.min.js"></script>
        <style>
            .errorWrap {
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #dd3d36;
                -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            }

            .succWrap {
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #5cb85c;
                -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            }
        </style>
    </head>

    <body>
        <div class="main-wrapper">
            <?php include('includes/topbar.php'); ?>
            <div class="content-wrapper">
                <div class="content-container">
                    <?php include('includes/leftbar.php'); ?>

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Approved Backlog Registration Requests</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li>Registration Requests</li>
                                        <li class="active">Manage Backlog Registrations</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Page Title and Breadcrumb -->
                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>View and ManageBacklog Registration Requests</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body">
                                                <table id="example" class="display table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Student Name</th>
                                                            <th>Roll Id</th>
                                                            <th>Course Code</th>
                                                            <th>Course Name</th>
                                                            <th>Semester</th>
                                                            <th>Registration Date</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $cnt = 1;
                                                        foreach ($registrations as $registration) { ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($cnt); ?></td>
                                                                <td><?php echo htmlentities($registration->StudentName); ?></td>
                                                                <td><?php echo htmlentities($registration->RollId); ?></td>
                                                                <td><?php echo htmlentities($registration->CourseCode); ?></td>
                                                                <td><?php echo htmlentities($registration->CourseName); ?></td>
                                                                <td><?php echo htmlentities($registration->Semester); ?></td>
                                                                <td><?php echo htmlentities($registration->RegistrationTime); ?></td>
                                                                <td>
                                                                    <form method="post" action="">
                                                                        <input type="hidden" name="RollId" value="<?php echo htmlentities($registration->RollId); ?>">
                                                                        <input type="hidden" name="Semester" value="<?php echo htmlentities($registration->Semester); ?>">
                                                                        <input type="hidden" name="CourseCode" value="<?php echo htmlentities($registration->CourseCode); ?>">
                                                                        <button type="submit" name="action" value="decline" class="btn btn-danger btn-xs">Decline</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php $cnt++;
                                                        } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== COMMON JS FILES ========== -->
        <script src="js/jquery/jquery-2.2.4.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>
        <script src="js/prism/prism.js"></script>
        <script src="js/DataTables/datatables.min.js"></script>
        <script src="js/main.js"></script>
        <script>
            $(function($) {
                $('#example').DataTable();
            });
        </script>
    </body>

    </html>

<?php } ?>