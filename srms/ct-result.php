<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
} else {
    $rollId = $_SESSION['login'];

    // Fetch all semesters registered by the student
    $sql = "SELECT DISTINCT Semester 
            FROM tblregistration 
            WHERE RollId = :rollId 
            ORDER BY Semester";
    $query = $dbh->prepare($sql);
    $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
    $query->execute();
    $semesters = $query->fetchAll(PDO::FETCH_OBJ);

    // Fetch all courses mapped to semesters for the student with CourseCredit >= 3.00
    $sql = "SELECT DISTINCT r.Semester, s.CourseName, s.CourseCode 
        FROM tblregistration r 
        JOIN tblsubjects s ON r.RegisteredCourse = s.CourseCode 
        WHERE r.RollId = :rollId AND s.CourseCredit >= 3.00";
    $query = $dbh->prepare($sql);
    $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
    $query->execute();
    $registrations = $query->fetchAll(PDO::FETCH_OBJ);

    // Prepare JavaScript objects for frontend
    $courseOptions = [];
    foreach ($registrations as $registration) {
        $semester = $registration->Semester;
        $courseName = $registration->CourseName;
        $courseCode = $registration->CourseCode;

        if (!isset($courseOptions[$semester])) {
            $courseOptions[$semester] = [];
        }
        $courseOptions[$semester][] = [
            "CourseCode" => $courseCode,
            "CourseName" => $courseName,
        ];
    }

    // Filter results
    $results = [];
    $selectedSemester = $_POST['semester'] ?? '';
    $selectedCourse = $_POST['course'] ?? '';
    $courseCredit = 0;
    $isSessional = false;

    if (isset($_POST['filter'])) {
        if (empty($selectedSemester) || empty($selectedCourse)) {
            $error = "Please select both semester and course!";
        } else {
            // Fetch Class Test Marks
            $sql = "SELECT CourseCode, CT_1, CT_2, CT_3, CT_4 
                    FROM tblmarks 
                    WHERE RollId = :rollId AND Semester = :semester AND CourseCode = :course";
            $query = $dbh->prepare($sql);
            $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
            $query->bindParam(':semester', $selectedSemester, PDO::PARAM_STR);
            $query->bindParam(':course', $selectedCourse, PDO::PARAM_STR);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
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

    <script>
        const courseOptions = <?php echo json_encode($courseOptions); ?>;

        function updateCourses() {
            const semesterDropdown = document.getElementById('semester');
            const courseDropdown = document.getElementById('course');

            const selectedSemester = semesterDropdown.value;

            courseDropdown.innerHTML = '<option value="">Select Course</option>';

            if (selectedSemester && courseOptions[selectedSemester]) {
                courseOptions[selectedSemester].forEach((course) => {
                    const option = document.createElement('option');
                    option.value = course.CourseCode;
                    option.textContent = course.CourseName;
                    courseDropdown.appendChild(option);
                });
            }
        }

        // Reset the form on page load
        window.onload = function() {
            const semesterDropdown = document.getElementById('semester');
            const courseDropdown = document.getElementById('course');

            // Reset the semester and course dropdowns
            semesterDropdown.selectedIndex = 0; // Set to "Select Semester"
            courseDropdown.innerHTML = '<option value="">Select Course</option>';
        };
    </script>
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
                                <h2 class="title">Class Test Result</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="student-dash.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li>Result</li>
                                    <li class="active">CT Result</li>
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
                                                <h5>View CT Result</h5>
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
                                                <div class="form-group">
                                                    <label for="semester">Semester</label>
                                                    <select name="semester" id="semester" class="form-control" onchange="updateCourses()">
                                                        <option value="">Select Semester</option>
                                                        <?php foreach ($semesters as $semester) { ?>
                                                            <option value="<?php echo htmlentities($semester->Semester); ?>">
                                                                <?php echo htmlentities($semester->Semester); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="course">Course</label>
                                                    <select name="course" id="course" class="form-control">
                                                        <option value="">Select Course</option>
                                                    </select>
                                                </div>
                                                <button type="submit" name="filter" class="btn btn-primary">Filter</button>
                                            </form>

                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Course Code</th>
                                                        <th>CT-1</th>
                                                        <th>CT-2</th>
                                                        <th>CT-3</th>
                                                        <th>CT-4</th>
                                                        <th>Average CT</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $cnt = 1;
                                                    if (!empty($results)) {
                                                        foreach ($results as $result) {
                                                            $ctMarks = array_filter([$result->CT_1, $result->CT_2, $result->CT_3, $result->CT_4]);
                                                            $averageCT = count($ctMarks) >= 3 ? ceil(array_sum(array_slice($ctMarks, -3)) / 3) : '';
                                                    ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($cnt); ?></td>
                                                                <td><?php echo htmlentities($result->CourseCode); ?></td>
                                                                <td><?php echo htmlentities($result->CT_1); ?></td>
                                                                <td><?php echo htmlentities($result->CT_2); ?></td>
                                                                <td><?php echo htmlentities($result->CT_3); ?></td>
                                                                <td><?php echo htmlentities($result->CT_4); ?></td>
                                                                <td><?php echo htmlentities($averageCT); ?></td>
                                                            </tr>
                                                        <?php
                                                            $cnt++;
                                                        }
                                                    } elseif (isset($_POST['filter'])) { ?>
                                                        <tr>
                                                            <td colspan="7" style="text-align: center;">No Results Found</td>
                                                        </tr>
                                                    <?php } ?>
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