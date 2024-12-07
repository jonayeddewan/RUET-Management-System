<?php
session_start();
include('includes/config.php');

// Check if admin is logged in
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    // Handle Approve or Decline action
    if (isset($_POST['action'])) {
        $requestId = $_POST['requestId'];
        $action = $_POST['action'];
        $rollId = $_POST['rollId'];
        $semester = $_POST['semester'];
        $courses = $_POST['courses']; // Comma-separated course list

        if ($action == "approve") {
            // Split the courses into an array
            $courseList = explode(',', $courses);

            // Insert each course as a separate entry in tblbackregistration
            foreach ($courseList as $course) {
                $sql = "INSERT INTO tblbackregistration (RollId, Semester, RegisteredCourse, RegistrationStatus) 
                        VALUES (:rollId, :semester, :registeredCourse, 1)"; // Status 1 = Approved
                $query = $dbh->prepare($sql);
                $query->bindParam(':rollId', $rollId, PDO::PARAM_INT);
                $query->bindParam(':semester', $semester, PDO::PARAM_STR);
                $query->bindParam(':registeredCourse', $course, PDO::PARAM_STR);
                $query->execute();


                // Insert into tblmanageregistration
                $sql = "INSERT INTO tblbackmanageregistration (RollId, Semester, CourseCode, RegistrationStatus) 
                        VALUES (:rollId, :semester, :courseCode, 1)";  // Status 1 = Approved
                $query = $dbh->prepare($sql);
                $query->bindParam(':rollId', $rollId, PDO::PARAM_INT);
                $query->bindParam(':semester', $semester, PDO::PARAM_STR);
                $query->bindParam(':courseCode', $course, PDO::PARAM_STR);
                $query->execute();
            }
        }

        // Remove the request from tblregistrationqueue (after approval or decline)
        $sql = "DELETE FROM tblbackregistrationqueue WHERE id = :requestId";
        $query = $dbh->prepare($sql);
        $query->bindParam(':requestId', $requestId, PDO::PARAM_INT);
        $query->execute();

        // Redirect to manage-registration.php to track handled requests
        header("Location: manage-back-registration.php");
        exit;
    }

    // Fetch pending registration requests
    $sql = "SELECT tblbackregistrationqueue.id, tblstudents.RollId, tblstudents.StudentName, 
                   tblbackregistrationqueue.Semester, tblbackregistrationqueue.RequestedCourses, 
                   tblbackregistrationqueue.RegistrationTime 
            FROM tblbackregistrationqueue 
            JOIN tblstudents ON tblbackregistrationqueue.RollId = tblstudents.RollId
            ORDER BY tblbackregistrationqueue.RegistrationTime ASC";
    $query = $dbh->prepare($sql);
    $query->execute();
    $requests = $query->fetchAll(PDO::FETCH_OBJ);
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

            /* Added margin to create gap between the filter button and the table */
            .filter-form {
                margin-bottom: 20px;
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
                                    <h2 class="title">Check Registration Requests</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li>Registration Requests</li>
                                        <li class="active">Check Backlog Registration Requests</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Registration Request Table Section -->
                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>View and Manage Backlog Registration Requests</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body">
                                                <!-- Registration Requests Table -->
                                                <table id="example" class="display table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Student Name</th>
                                                            <th>Roll Id</th>
                                                            <th>Requested Courses</th>
                                                            <th>Semester</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $cnt = 1;
                                                        if (count($requests) > 0) {
                                                            foreach ($requests as $request) { ?>
                                                                <tr>
                                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                                    <td><?php echo htmlentities($request->StudentName); ?></td>
                                                                    <td><?php echo htmlentities($request->RollId); ?></td>
                                                                    <td><?php echo htmlentities($request->RequestedCourses); ?></td>
                                                                    <td><?php echo htmlentities($request->Semester); ?></td>
                                                                    <td>
                                                                        <form method="post" action="">
                                                                            <input type="hidden" name="requestId"
                                                                                value="<?php echo $request->id; ?>">
                                                                            <input type="hidden" name="rollId"
                                                                                value="<?php echo $request->RollId; ?>">
                                                                            <input type="hidden" name="semester"
                                                                                value="<?php echo $request->Semester; ?>">
                                                                            <input type="hidden" name="courses"
                                                                                value="<?php echo $request->RequestedCourses; ?>">
                                                                            <button type="submit" name="action" value="approve"
                                                                                class="btn btn-success btn-xs">Approve</button>
                                                                            <button type="submit" name="action" value="decline"
                                                                                class="btn btn-danger btn-xs">Decline</button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                        <?php $cnt++;
                                                            }
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

            setInterval(function() {
                location.reload();
            }, 30000); // Refresh every 30 seconds
        </script>
    </body>

    </html>

<?php } ?>