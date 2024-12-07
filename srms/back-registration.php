<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
} else {
    $rollId = $_SESSION['login'];
    $sql = "SELECT * FROM tblstudents WHERE RollId=:rollId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        header("Location: index.php");
        exit();
    }
}
?>
<?php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['login']) == "") {
    header("Location: index.php");
} else {
    $rollId = $_SESSION['login'];
    $error = '';
    $results = [];

    if (isset($_POST['register'])) {
        $semester = $_POST['semester'];
        $selected_courses = isset($_POST['selected_courses']) ? $_POST['selected_courses'] : [];

        // Check if filtering has been done
        if (empty($semester) || empty($selected_courses)) {
            if (empty($semester)) {
                $error = "Please filter first!";
            } elseif (empty($selected_courses)) {
                $error = "Please select at least one course!";
            }
        } else {
            $registeredCourses = implode(",", $selected_courses);

            // Check if the registration request already exists in tblbackregistrationqueue
            $sql_check_queue = "SELECT * FROM tblbackregistrationqueue WHERE RollId = :rollId AND Semester = :semester";
            $query_check_queue = $dbh->prepare($sql_check_queue);
            $query_check_queue->bindParam(':rollId', $rollId, PDO::PARAM_INT);
            $query_check_queue->bindParam(':semester', $semester, PDO::PARAM_STR);
            $query_check_queue->execute();
            $count_queue = $query_check_queue->rowCount();

            // Check if the student is already registered
            $sql_check = "SELECT * FROM tblbackregistration WHERE RollId = :rollId AND Semester = :semester";
            $query_check = $dbh->prepare($sql_check);
            $query_check->bindParam(':rollId', $rollId, PDO::PARAM_INT);
            $query_check->bindParam(':semester', $semester, PDO::PARAM_STR);
            $query_check->execute();
            $count = $query_check->rowCount();

            if ($count > 0) {
                $error = "Registration for this semester already exists!";
            } elseif ($count_queue > 0) {
                $error = "Registration request for this semester is already in queue!";
            } else {
                // Insert new registration request into tblregistrationqueue
                $sql = "INSERT INTO tblbackregistrationqueue (RollId, Semester, RequestedCourses) 
                        VALUES (:rollId, :semester, :registeredCourses)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':rollId', $rollId, PDO::PARAM_INT);
                $query->bindParam(':semester', $semester, PDO::PARAM_STR);
                $query->bindParam(':registeredCourses', $registeredCourses, PDO::PARAM_STR);
                $query->execute();

                // Store success message in session and redirect
                $_SESSION['msg'] = "Registration request has been submitted successfully! Please wait for the approval.";
                header("Location: course-registration.php");
                exit();  // Stop further execution
            }
        }
    }

    // Fetch the success message from session if available
    $msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
    unset($_SESSION['msg']);  // Clear the message from session after showing it

    if (isset($_POST['filter'])) {
        $department = $_POST['department'];
        $semester = $_POST['semester'];

        if (empty($department) && empty($semester)) {
            $error = "Please select Department and Semester!";
        } elseif (empty($department)) {
            $error = "Please select Department!";
        } elseif (empty($semester)) {
            $error = "Please select Semester!";
        } else {
            // Fetch courses with GPA 0.00 for the logged-in student
            $sql = "SELECT 
                        ts.CourseName, 
                        ts.CourseCode, 
                        ts.CourseCredit, 
                        ts.Department, 
                        ts.Semester 
                    FROM tblsubjects ts
                    INNER JOIN tblgpa tg ON ts.CourseCode = tg.CourseCode
                    WHERE tg.RollId = :rollId 
                    AND tg.GPA = 0.00";

            if (!empty($department)) {
                $sql .= " AND ts.Department = :department";
            }
            if (!empty($semester)) {
                $sql .= " AND ts.Semester = :semester";
            }
            $sql .= " ORDER BY ts.id";

            $query = $dbh->prepare($sql);
            $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
            if (!empty($department)) {
                $query->bindParam(':department', $department, PDO::PARAM_STR);
            }
            if (!empty($semester)) {
                $query->bindParam(':semester', $semester, PDO::PARAM_STR);
            }
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            if (!$results) {
                $error = "No courses found where GPA is 0.00.";
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

    <body class="top-navbar-fixed">
        <div class="main-wrapper">
            <?php include('includes/student-topbar.php'); ?>
            <div class="content-wrapper">
                <div class="content-container">
                    <?php include('includes/student-leftbar.php'); ?>
                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Course Registration</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="student-dash.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li>Course Management</li>
                                        <li class="active">Backlog Registration</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>View Backlog Registration Info</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body p-20">
                                                <?php if ($msg) { ?>
                                                    <div class="succWrap"><strong>SUCCESS</strong>:
                                                        <?php echo htmlentities($msg); ?>
                                                    </div>
                                                <?php } ?>
                                                <?php if ($error) { ?>
                                                    <div class="errorWrap"><strong>ERROR</strong>:
                                                        <?php echo htmlentities($error); ?>
                                                    </div>
                                                <?php } ?>
                                                <form method="post" action="" class="filter-form">
                                                    <div class="form-group">
                                                        <label for="department">Department</label>
                                                        <select name="department" id="department" class="form-control">
                                                            <option value="">Select Department</option>
                                                            <?php
                                                            $sql = "SELECT DISTINCT Department FROM tblclasses";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $departments = $query->fetchAll(PDO::FETCH_OBJ);
                                                            if ($query->rowCount() > 0) {
                                                                foreach ($departments as $result) { ?>
                                                                    <option
                                                                        value="<?php echo htmlentities($result->Department); ?>">
                                                                        <?php echo htmlentities($result->Department); ?>
                                                                    </option>
                                                            <?php }
                                                            } ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="semester">Semester</label>
                                                        <select name="semester" id="semester" class="form-control">
                                                            <option value="">Select Semester</option>
                                                            <?php
                                                            $sql = "SELECT DISTINCT Semester FROM tblclasses";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $semesters = $query->fetchAll(PDO::FETCH_OBJ);
                                                            if ($query->rowCount() > 0) {
                                                                foreach ($semesters as $result) { ?>
                                                                    <option value="<?php echo htmlentities($result->Semester); ?>">
                                                                        <?php echo htmlentities($result->Semester); ?>
                                                                    </option>
                                                            <?php }
                                                            } ?>
                                                        </select>
                                                    </div>
                                                    <button type="submit" name="filter"
                                                        class="btn btn-primary">Filter</button>
                                                </form>

                                                <form method="post" action="">
                                                    <table id="example" class="display table table-striped table-bordered"
                                                        cellspacing="0" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Course Name</th>
                                                                <th>Course Code</th>
                                                                <th>Course Credit</th>
                                                                <th>Fee</th>
                                                                <th>
                                                                    <input type="checkbox" id="select-all"> Select All
                                                                </th>
                                                                <!-- <th>Action</th> -->
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            <?php
                                                            $cnt = 1;
                                                            if (!empty($results)) {
                                                                foreach ($results as $result) { ?>
                                                                    <tr>
                                                                        <td><?php echo htmlentities($cnt); ?></td>
                                                                        <td><?php echo htmlentities($result->CourseName); ?></td>
                                                                        <td><?php echo htmlentities($result->CourseCode); ?></td>
                                                                        <td><?php echo htmlentities($result->CourseCredit); ?></td>
                                                                        <td><?php echo htmlentities($result->CourseCredit * 20); ?>
                                                                        </td>
                                                                        <td>
                                                                            <input type="checkbox" name="selected_courses[]" value="<?php echo htmlentities($result->CourseCode); ?>" class="select-course">
                                                                        </td>
                                                                        <!-- <td>
                                                                            <a href="edit-student.php?stid=<?php echo htmlentities($result->id); ?>"
                                                                                class="btn btn-primary btn-xs"
                                                                                target="_blank">Edit</a>
                                                                            <a href="edit-result.php?stid=<?php echo htmlentities($result->id); ?>"
                                                                                class="btn btn-warning btn-xs" target="_blank">View
                                                                                Result</a>
                                                                        </td> -->

                                                                    </tr>
                                                                <?php $cnt++;
                                                                }
                                                            } elseif (isset($_POST['filter'])) { // Check if filter button was pressed 
                                                                ?>
                                                                <tr>
                                                                    <td colspan="7" style="text-align: center;">No courses found
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                    <input type="hidden" name="semester"
                                                        value="<?php echo htmlentities($semester); ?>">
                                                    <button type="submit" name="register"
                                                        class="btn btn-success">Register</button>
                                                </form>
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
        <script>
            $(document).ready(function() {
                // Select All Checkbox
                $('#select-all').click(function() {
                    $('.select-course').prop('checked', this.checked);
                });

                // If a single course checkbox is unchecked, uncheck the "Select All" checkbox
                $('.select-course').click(function() {
                    if (!this.checked) {
                        $('#select-all').prop('checked', false);
                    }
                });
            });
        </script>
    </body>

    </html>
<?php } ?>