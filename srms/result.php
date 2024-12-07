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
    $student = $query->fetch(PDO::FETCH_OBJ);

    if (!$student) {
        header("Location: index.php");
        exit();
    }

    $results = [];
    $selectedSemester = '';
    $sgpa = 0.0;
    $cgpa = 0.0;

    if (isset($_POST['filter'])) {
        $selectedSemester = $_POST['semester'];

        if (empty($selectedSemester)) {
            $error = "Please select a semester!";
        } else {
            // Fetch results for the selected semester from tblmarks
            $sql = "SELECT CourseCode, CT_1, CT_2, CT_3, CT_4, Attendance, Assignment, Semester_Final 
                    FROM tblmarks 
                    WHERE RollId = :rollId AND Semester = :semester";
            $query = $dbh->prepare($sql);
            $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
            $query->bindParam(':semester', $selectedSemester, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            // Fetch SGPA from tblsgpa
            $sql = "SELECT SGPA FROM tblsgpa WHERE RollId = :rollId AND Semester = :semester";
            $query = $dbh->prepare($sql);
            $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
            $query->bindParam(':semester', $selectedSemester, PDO::PARAM_INT);
            $query->execute();
            $sgpaResult = $query->fetch(PDO::FETCH_OBJ);
            $sgpa = $sgpaResult ? $sgpaResult->SGPA : 0.0;

            // Fetch CGPA from tblcgpa
            $sql = "SELECT CGPA FROM tblcgpa WHERE RollId = :rollId AND Semester = :semester";
            $query = $dbh->prepare($sql);
            $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
            $query->bindParam(':semester', $selectedSemester, PDO::PARAM_INT);
            $query->execute();
            $cgpaResult = $query->fetch(PDO::FETCH_OBJ);
            $cgpa = $cgpaResult ? $cgpaResult->CGPA : 0.0;
        }
    }
}

// Function to calculate letter grade based on GPA
function getLetterGrade($gradePoint)
{
    if ($gradePoint >= 4.0) return 'A+';
    elseif ($gradePoint >= 3.75) return 'A';
    elseif ($gradePoint >= 3.5) return 'A-';
    elseif ($gradePoint >= 3.25) return 'B+';
    elseif ($gradePoint >= 3.0) return 'B';
    elseif ($gradePoint >= 2.75) return 'B-';
    elseif ($gradePoint >= 2.5) return 'C+';
    elseif ($gradePoint >= 2.25) return 'C';
    elseif ($gradePoint >= 2.0) return 'D';
    else return 'F';
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
                                <h2 class="title">Semester Result</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="student-dash.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li>Result</li>
                                    <li class="active">Semester Result</li>
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
                                                <h5>View Semester Result</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body p-20">
                                            <?php if ($msg) { ?>
                                                <div class="succWrap"><strong>SUCCESS</strong>: <?php echo htmlentities($msg); ?></div>
                                            <?php } ?>
                                            <?php if ($error) { ?>
                                                <div class="errorWrap"><strong>ERROR</strong>: <?php echo htmlentities($error); ?></div>
                                            <?php } ?>
                                            <form method="post" action="" class="filter-form">
                                                <style>
                                                    .filter-form {
                                                        margin-bottom: 20px;
                                                    }
                                                </style>
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
                                                <button type="submit" name="filter" class="btn btn-primary">Filter</button>
                                            </form>

                                            <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Course Code</th>
                                                        <th>Course Credit</th>
                                                        <th>Average CT</th>
                                                        <th>Attendance</th>
                                                        <th>Assignment</th>
                                                        <th>Grade</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php
                                                    $cnt = 1;
                                                    if (!empty($results)) {
                                                        foreach ($results as $result) {
                                                            $ctAvg = (max($result->CT_1, $result->CT_2, $result->CT_3) +
                                                                max($result->CT_2, $result->CT_3, $result->CT_4) +
                                                                max($result->CT_1, $result->CT_3, $result->CT_4)) / 3;

                                                            // Fetch Course Credit from tblsubjects
                                                            $sql = "SELECT CourseCredit FROM tblsubjects WHERE CourseCode = :courseCode";
                                                            $query = $dbh->prepare($sql);
                                                            $query->bindParam(':courseCode', $result->CourseCode, PDO::PARAM_STR);
                                                            $query->execute();
                                                            $subject = $query->fetch(PDO::FETCH_OBJ);
                                                            $courseCredit = $subject ? $subject->CourseCredit : "N/A";

                                                            // Fetch GPA for each course from tblgpa
                                                            $sql = "SELECT GPA FROM tblgpa WHERE RollId = :rollId AND CourseCode = :courseCode AND Semester = :semester";
                                                            $query = $dbh->prepare($sql);
                                                            $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
                                                            $query->bindParam(':courseCode', $result->CourseCode, PDO::PARAM_STR);
                                                            $query->bindParam(':semester', $selectedSemester, PDO::PARAM_STR);
                                                            $query->execute();
                                                            $gpaResult = $query->fetch(PDO::FETCH_OBJ);
                                                            $gpa = $gpaResult ? $gpaResult->GPA : 0.0;
                                                            $letterGrade = getLetterGrade($gpa);
                                                    ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($cnt); ?></td>
                                                                <td><?php echo htmlentities($result->CourseCode); ?></td>
                                                                <td><?php echo htmlentities($courseCredit); ?></td>
                                                                <td><?php echo number_format($ctAvg, 2); ?></td>
                                                                <td><?php echo htmlentities($result->Attendance); ?></td>
                                                                <td><?php echo htmlentities($result->Assignment); ?></td>
                                                                <td><?php echo htmlentities($letterGrade); ?></td>
                                                            </tr>
                                                        <?php $cnt++;
                                                        }
                                                    } elseif (isset($_POST['filter'])) { ?>
                                                        <tr>
                                                            <td colspan="8" style="text-align: center;">No Results Found</td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                                <?php if (!empty($results)) { ?>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="6" style="text-align: right;"><strong>SGPA</strong></td>
                                                            <td><?php echo htmlentities(number_format($sgpa, 2)); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6" style="text-align: right;"><strong>CGPA</strong></td>
                                                            <td><?php echo htmlentities(number_format($cgpa, 2)); ?></td>
                                                        </tr>
                                                    </tfoot>
                                                <?php } ?>
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