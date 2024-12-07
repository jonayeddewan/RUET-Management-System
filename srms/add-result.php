<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
} else {
    $teacherid = $_SESSION['login'];
    $sql = "SELECT * FROM tblteachers WHERE TeacherId=:teacherid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':teacherid', $teacherid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Manage Students Results</title>
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
        <?php include('includes/teacher-topbar.php'); ?>
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/teacher-leftbar.php'); ?>
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Add Result</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="teacher-dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> Result</li>
                                    <li class="active">Add Result</li>
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
                                                <h5>View Results Info</h5>
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
                                                    <select name="department" id="department" class="form-control"
                                                        onchange="updateSeries()">
                                                        <option value="">Select Department</option>
                                                        <?php
                                                        $sql = "SELECT DISTINCT Department FROM tblclasses";
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
                                                <div class="form-group">
                                                    <label for="series">Series</label>
                                                    <select name="series" id="series" class="form-control"
                                                        onchange="updateSemesters()">
                                                        <option value="">Select Series</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="semester">Semester</label>
                                                    <select name="semester" id="semester" class="form-control"
                                                        onchange="updateCourses()">
                                                        <option value="">Select Semester</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="course">Course</label>
                                                    <select name="course" id="course" class="form-control">
                                                        <option value="">Select Course</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="marksType">Marks Type</label>
                                                    <select name="marksType" id="marksType" class="form-control">
                                                        <option value="">Select Marks Type</option>
                                                    </select>
                                                </div>
                                                <button type="submit" name="filter"
                                                    class="btn btn-primary">Filter</button>
                                            </form>

                                            <form method="post" action="">
                                                <table id="example" class="display table table-striped table-bordered"
                                                    cellspacing="0" width="100%">
                                                    <tbody>
                                                        <?php
                                                        if (isset($_POST['filter'])) {
                                                            $department = $_POST['department'] ?? null;
                                                            $series = $_POST['series'] ?? null;
                                                            $semester = $_POST['semester'] ?? null;
                                                            $course = $_POST['course'] ?? null;
                                                            $marksType = $_POST['marksType'] ?? null;



                                                            // Construct SQL query
                                                            if (in_array($marksType, ['Attendance', 'Quiz', 'BoardViva', 'Performance'])) {
                                                                $sql = "SELECT DISTINCT s.StudentName, s.RollId, s.RegistrationId, s.Department, s.Section, s.Series, s.RegDate, s.Status,
                        m.Attendance, m.Quiz, m.BoardViva, m.Performance
                        FROM tblstudents s
                        LEFT JOIN tblsessional m ON s.RollId = m.RollId
                        INNER JOIN tblregistration r ON s.RollId = r.RollId
                        WHERE m.CourseCode = :course";
                                                            } else {
                                                                $sql = "SELECT DISTINCT s.StudentName, s.RollId, s.RegistrationId, s.Department, s.Section, s.Series, s.RegDate, s.Status,
                        m.CT_1, m.CT_2, m.CT_3, m.CT_4, m.Assignment, m.Semester_Final
                        FROM tblstudents s
                        LEFT JOIN tblmarks m ON s.RollId = m.RollId
                        INNER JOIN tblregistration r ON s.RollId = r.RollId
                        WHERE m.CourseCode = :course";
                                                            }

                                                            // Add filters
                                                            $params = [':course' => $course];
                                                            if ($department) {
                                                                $sql .= " AND s.Department = :department";
                                                                $params[':department'] = $department;
                                                            }
                                                            if ($series) {
                                                                $sql .= " AND s.Series = :series";
                                                                $params[':series'] = $series;
                                                            }
                                                            if ($semester) {
                                                                $sql .= " AND m.Semester = :semester";
                                                                $params[':semester'] = $semester;
                                                            }

                                                            // Finalize SQL
                                                            $sql .= " ORDER BY s.RollId";

                                                            // Execute query
                                                            try {
                                                                $query = $dbh->prepare($sql);
                                                                $query->execute($params);
                                                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                            } catch (PDOException $e) {
                                                                echo "Error: " . $e->getMessage();
                                                                $results = [];
                                                            }
                                                        } else {
                                                            $results = [];
                                                        }

                                                        // Dynamically display the marks based on the selected marksType
                                                        $cnt = 1;
                                                        if (count($results) > 0) {
                                                            echo '<form method="POST" action="">'; // Open form to submit marks
                                                            echo '<table class="table table-bordered">';
                                                            echo '<thead><tr>';
                                                            echo '<th>#</th><th>Student Name</th><th>Roll ID</th>';

                                                            // Display dynamic columns based on selected marksType
                                                            echo "<th>" . htmlentities($marksType) . "</th>"; // Display the selected marksType as the header

                                                            echo '</tr></thead><tbody>';

                                                            // Display data rows dynamically based on the selected marksType
                                                            foreach ($results as $result) {
                                                                echo '<tr>';
                                                                echo '<td>' . htmlentities($cnt) . '</td>';
                                                                echo '<td>' . htmlentities($result->StudentName) . '</td>';
                                                                echo '<td>' . htmlentities($result->RollId) . '</td>';
                                                                echo '<input type="hidden" name="marksType" value="' . htmlentities($marksType) . '">'; // Store marksType
                                                                echo '<input type="hidden" name="semester" value="' . htmlentities($semester) . '">'; // Store semester
                                                                echo '<input type="hidden" name="course" value="' . htmlentities($course) . '">'; // Store semester
                                                                // Display the marksType value as an input field for each student
                                                                echo '<td>';
                                                                // If the marks are already available, populate the input field with the existing mark
                                                                $existingMark = htmlentities($result->$marksType ?? '');
                                                                echo '<input type="text" name="marks[' . $result->RollId . ']" class="form-control" placeholder="Enter Marks" value="' . $existingMark . '">';
                                                                echo '</td>';
                                                                echo '</tr>';
                                                                $cnt++;
                                                            }
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                                <button type="submit" name="submitMarks">Submit Marks</button>
                                            </form>
                                            <?php
                                            if (isset($_POST['submitMarks'])) {
                                                $marks = $_POST['marks']; // Array of RollId => Mark
                                                $semester = $_POST['semester'];
                                                $course = $_POST['course'];
                                                $marksType = $_POST['marksType'];

                                                // Define allowed columns and their maximum marks
                                                $allowedColumns = [
                                                    'CT_1' => 20,
                                                    'CT_2' => 20,
                                                    'CT_3' => 20,
                                                    'CT_4' => 20,
                                                    'Assignment' => 10,
                                                    'Attendance' => 10,
                                                    'Semester_Final' => 60,
                                                    'Quiz' => 20,
                                                    'BoardViva' => 25,
                                                    'Performance' => 45
                                                ];

                                                // Check if the selected marksType is valid
                                                if (!array_key_exists($marksType, $allowedColumns)) {
                                                    die("Invalid marks type specified."); // Exit if the column name is not allowed
                                                }

                                                // Get the maximum allowed marks for the selected marksType
                                                $maxMarks = $allowedColumns[$marksType];

                                                foreach ($marks as $rollId => $mark) {
                                                    // Validate that the entered mark does not exceed the maximum allowed
                                                    if ($mark > $maxMarks) {
                                                        echo "Error: Marks for Roll ID $rollId cannot exceed $maxMarks for $marksType.<br>";
                                                        continue; // Skip this record and move to the next
                                                    }

                                                    // Dynamically construct the query with the validated column name
                                                    if (in_array($marksType, ['Attendance', 'Quiz', 'BoardViva', 'Performance'])) {
                                                        $sql = "INSERT INTO tblsessional (RollId, Semester, CourseCode, $marksType) 
                                                                    VALUES (:rollId, :semester, :course, :mark)
                                                                    ON DUPLICATE KEY UPDATE $marksType = :mark";
                                                    } else {
                                                        $sql = "INSERT INTO tblmarks (RollId, Semester, CourseCode, $marksType) 
                                                                    VALUES (:rollId, :semester, :course, :mark)
                                                                    ON DUPLICATE KEY UPDATE $marksType = :mark";
                                                    }

                                                    // Prepare and bind parameters
                                                    $query = $dbh->prepare($sql);
                                                    $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
                                                    $query->bindParam(':semester', $semester, PDO::PARAM_STR);
                                                    $query->bindParam(':course', $course, PDO::PARAM_STR);
                                                    $query->bindParam(':mark', $mark, PDO::PARAM_INT);

                                                    $query->execute();

                                                    //Execute the query and handle errors
                                                    // try {
                                                    //     $query->execute();
                                                    //     echo "Marks successfully inserted/updated for Roll ID: $rollId<br>";
                                                    // } catch (PDOException $e) {
                                                    //     echo "Error inserting/updating marks for Roll ID: $rollId - " . $e->getMessage() . "<br>";
                                                    // }
                                                }
                                            }
                                            ?>
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
    <script>
        // Update series dropdown based on department selection
        function updateSeries() {
            var department = document.getElementById("department").value;
            var seriesDropdown = document.getElementById("series");

            seriesDropdown.innerHTML = '<option value="">--Select a series--</option>';

            if (seriesOptions[department]) {
                seriesOptions[department].forEach(function(series) {
                    var optionElement = document.createElement("option");
                    optionElement.value = series;
                    optionElement.text = series;
                    seriesDropdown.appendChild(optionElement);
                });
            }
            updateSemesters(); // Clear the next dropdowns when department changes
        }

        function updateSemesters() {
            var department = document.getElementById("department").value;
            var series = document.getElementById("series").value;
            var semesterDropdown = document.getElementById("semester");

            semesterDropdown.innerHTML = '<option value="">--Select a semester--</option>';

            var key = department + '|' + series;

            if (semesterOptions[key]) {
                semesterOptions[key].forEach(function(semester) {
                    var optionElement = document.createElement("option");
                    optionElement.value = semester;
                    optionElement.text = semester;
                    semesterDropdown.appendChild(optionElement);
                });
            }
            updateCourses(); // Clear the next dropdown when series changes
        }

        function updateCourses() {
            var department = document.getElementById("department").value;
            var semester = document.getElementById("semester").value;
            var courseDropdown = document.getElementById("course");
            var marksTypeDropdown = document.getElementById("marksType");

            courseDropdown.innerHTML = '<option value="">--Select a course--</option>';
            marksTypeDropdown.innerHTML = '<option value="">--Select Marks Type--</option>';

            var key = department + '|' + semester;

            if (courseOptions[key]) {
                courseOptions[key].forEach(function(course) {
                    var optionElement = document.createElement("option");
                    optionElement.value = course;
                    optionElement.text = course;
                    courseDropdown.appendChild(optionElement);
                });
            }

            // Fetch CourseCredit and update MarksType dynamically
            courseDropdown.addEventListener('change', function() {
                var selectedCourse = courseDropdown.value;

                if (selectedCourse) {
                    fetch(`fetch_course_credit.php?courseCode=${selectedCourse}`)
                        .then(response => response.json())
                        .then(data => {
                            marksTypeDropdown.innerHTML = '<option value="">--Select Marks Type--</option>';
                            if (data.CourseCredit >= 3.0) {
                                marksTypeDropdown.innerHTML +=
                                    `
                                                                                                <option value="CT_1">CT-1</option>
                                                                                                <option value="CT_2">CT-2</option>
                                                                                                <option value="CT_3">CT-3</option>
                                                                                                <option value="CT_4">CT-4</option>
                                                                                                <option value="Attendance">Attendance</option>
                                                                                                <option value="Assignment">Assignment</option>
                                                                                                <option value="Semester_Final">Semester Final</option>`;
                            } else {
                                marksTypeDropdown.innerHTML +=
                                    `
                                                                                                 <option value="Attendance">Attendance</option>
                                                                                                 <option value="Quiz">Quiz</option>
                                                                                                 <option value="BoardViva">Board Viva</option>
                                                                                                 <option value="Performance">Performance</option>`;
                            }
                        });
                }
            });
        }

        var seriesOptions = {
            <?php
            // Fetch department and series data from tblclasses
            $sql = "SELECT DISTINCT Department, Series FROM tblclasses";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            $departments = [];
            if ($query->rowCount() > 0) {
                foreach ($results as $result) {
                    $departments[$result->Department][] = $result->Series;
                }
            }
            // Generate the JavaScript object for seriesOptions
            foreach ($departments as $department => $series) {
                $uniqueSeries = array_unique($series); // Remove duplicate series
                echo '"' . $department . '": ["' . implode('", "', $uniqueSeries) . '"],';
            }
            ?>
        };
        // Update semesters dropdown based on department and series selection
        var semesterOptions = {
            <?php
            // Fetch department, series, and semester data from tblclasses
            $sql = "SELECT Department, Series, Semester FROM tblclasses";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            $deptSeries = [];
            if ($query->rowCount() > 0) {
                foreach ($results as $result) {
                    // Combine Department and Series as the key
                    $key = $result->Department . '|' . $result->Series;
                    $deptSeries[$key][] = $result->Semester;
                }
            }

            // Generate the JavaScript object for semesterOptions
            foreach ($deptSeries as $key => $semesters) {
                $uniqueSemesters = array_unique($semesters); // Remove duplicate semesters
                echo '"' . $key . '": ["' . implode('", "', $uniqueSemesters) . '"],';
            }
            ?>
        };
        // Update courses dropdown based on department and semester selection
        var courseOptions = {
            <?php
            // Fetch department, semester, and course code data from tblsubjects
            $sql = "SELECT Department, Semester, CourseCode FROM tblsubjects";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            $deptSemesters = [];
            if ($query->rowCount() > 0) {
                foreach ($results as $result) {
                    // Combine Department and Semester as the key
                    $key = $result->Department . '|' . $result->Semester;
                    $deptSemesters[$key][] = $result->CourseCode;
                }
            }

            // Generate the JavaScript object for courseOptions
            foreach ($deptSemesters as $key => $courses) {
                $uniqueCourses = array_unique($courses); // Remove duplicate courses
                echo '"' . $key . '": ["' . implode('", "', $uniqueCourses) . '"],';
            }
            ?>
        };
    </script>

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