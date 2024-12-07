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
    <title>Calculate GPA </title>
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
                                <h2 class="title">Calculate GPA</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="teacher-dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> Result</li>
                                    <li class="active">Calculate GPA</li>
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
                                                <h5>View GPA Info</h5>
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
                                                    </select>
                                                </div>
                                                <button type="submit" name="filter"
                                                    class="btn btn-primary">Filter</button>
                                            </form>
                                            <form id="gpaForm" method="post" action="">
                                                <table id="example" class="display table table-striped table-bordered"
                                                    cellspacing="0" width="100%">
                                                    <tbody>
                                                        <?php
                                                        if (isset($_POST['filter'])) {
                                                            $department = $_POST['department'];
                                                            $series = $_POST['series'];
                                                            $semester = $_POST['semester'];
                                                            $course = $_POST['course'];
                                                            echo "$course";
                                                            // Fetch course credit to determine marks columns
                                                            $sql = "SELECT CourseCredit FROM tblsubjects WHERE CourseCode = :course";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute([':course' => $course]);
                                                            $courseCredit = $query->fetchColumn();

                                                            // Start table
                                                            echo '<table class="table table-bordered">';

                                                            // Table header
                                                            echo '<thead><tr>';
                                                            echo '<th>#</th><th>Student Name</th><th>Roll ID</th>';

                                                            // Generate headers based on course credit
                                                            if ($courseCredit >= 3.0) {
                                                                echo '<th>CT 1</th><th>CT 2</th><th>CT 3</th><th>CT 4</th><th>Attendance</th><th>Assignment</th><th>Semester Final</th>';
                                                                echo '<th>Best 3 CT Average</th>';
                                                            } else {
                                                                echo '<th>Attendance</th><th>Quiz</th><th>Board Viva</th><th>Performance</th>';
                                                            }

                                                            echo '<th>Total Marks</th><th>Alphabetical Grade</th><th>Numerical Grade</th>';
                                                            echo '</tr></thead><tbody>';

                                                            // Prepare the SQL query based on course credit
                                                            if ($courseCredit >= 3.0) {
                                                                $sql = "SELECT DISTINCT s.StudentName, s.RollId, s.RegistrationId, s.Department, s.Section, s.Series, s.RegDate, s.Status,
                    m.CT_1, m.CT_2, m.CT_3, m.CT_4, m.Attendance, m.Assignment, m.Semester_Final
                    FROM tblstudents s
                    LEFT JOIN tblmarks m ON s.RollId = m.RollId
                    INNER JOIN tblregistration r ON s.RollId = r.RollId
                    WHERE m.CourseCode = :course";
                                                            } else {
                                                                $sql = "SELECT DISTINCT s.StudentName, s.RollId, s.RegistrationId, s.Department, s.Section, s.Series, s.RegDate, s.Status,
                    m.Attendance, m.Quiz, m.BoardViva, m.Performance
                    FROM tblstudents s
                    LEFT JOIN tblsessional m ON s.RollId = m.RollId
                    INNER JOIN tblregistration r ON s.RollId = r.RollId
                    WHERE m.CourseCode = :course";
                                                            }

                                                            // Add filters for department, series, and semester
                                                            if ($department)
                                                                $sql .= " AND s.Department = :department";
                                                            if ($series)
                                                                $sql .= " AND s.Series = :series";
                                                            if ($semester)
                                                                $sql .= " AND r.Semester = :semester";

                                                            // Execute the query
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute([
                                                                ':course' => $course,
                                                                ':department' => $department,
                                                                ':series' => $series,
                                                                ':semester' => $semester
                                                            ]);

                                                            $results = $query->fetchAll(PDO::FETCH_ASSOC);

                                                            if ($query->rowCount() > 0) {
                                                                $counter = 1; // Counter for row numbering
                                                                foreach ($results as $row) {
                                                                    echo '<tr>';
                                                                    echo '<td>' . $counter++ . '</td>'; // Row number
                                                                    echo '<td>' . htmlentities($row['StudentName']) . '</td>';
                                                                    echo '<td>' . htmlentities($row['RollId']) . '</td>';

                                                                    $totalMarks = 0; // Initialize total marks for calculation

                                                                    // For courses with credit >= 3 (fetching from tblmarks)
                                                                    if ($courseCredit >= 3.0) {
                                                                        $ctScores = [
                                                                            isset($row['CT_1']) ? $row['CT_1'] : 0,
                                                                            isset($row['CT_2']) ? $row['CT_2'] : 0,
                                                                            isset($row['CT_3']) ? $row['CT_3'] : 0,
                                                                            isset($row['CT_4']) ? $row['CT_4'] : 0
                                                                        ];
                                                                        rsort($ctScores);
                                                                        $bestThreeAverage = ceil(array_sum(array_slice($ctScores, 0, 3)) / 3);

                                                                        $totalMarks = $bestThreeAverage +
                                                                            (isset($row['Attendance']) ? $row['Attendance'] : 0) +
                                                                            (isset($row['Semester_Final']) ? $row['Semester_Final'] : 0) +
                                                                            (isset($row['Assignment']) ? $row['Assignment'] : 0);

                                                                        echo '<td>' . htmlentities($row['CT_1']) . '</td>';
                                                                        echo '<td>' . htmlentities($row['CT_2']) . '</td>';
                                                                        echo '<td>' . htmlentities($row['CT_3']) . '</td>';
                                                                        echo '<td>' . htmlentities($row['CT_4']) . '</td>';
                                                                        echo '<td>' . htmlentities($row['Attendance']) . '</td>';
                                                                        echo '<td>' . htmlentities($row['Assignment']) . '</td>';
                                                                        echo '<td>' . htmlentities($row['Semester_Final']) . '</td>';
                                                                        echo '<td>' . $bestThreeAverage . '</td>';
                                                                    } else {
                                                                        $totalMarks =
                                                                            (isset($row['Attendance']) ? $row['Attendance'] : 0) +
                                                                            (isset($row['Quiz']) ? $row['Quiz'] : 0) +
                                                                            (isset($row['BoardViva']) ? $row['BoardViva'] : 0) +
                                                                            (isset($row['Performance']) ? $row['Performance'] : 0);

                                                                        echo '<td>' . htmlentities($row['Attendance']) . '</td>';
                                                                        echo '<td>' . htmlentities($row['Quiz']) . '</td>';
                                                                        echo '<td>' . htmlentities($row['BoardViva']) . '</td>';
                                                                        echo '<td>' . htmlentities($row['Performance']) . '</td>';
                                                                    }

                                                                    $totalMarks = max(0, $totalMarks);
                                                                    $numericalGrade = 0;
                                                                    $alphabeticalGrade = '';

                                                                    if ($totalMarks >= 80) {
                                                                        $numericalGrade = 4.0;
                                                                        $alphabeticalGrade = 'A+';
                                                                    } elseif ($totalMarks >= 75) {
                                                                        $numericalGrade = 3.75;
                                                                        $alphabeticalGrade = 'A';
                                                                    } elseif ($totalMarks >= 70) {
                                                                        $numericalGrade = 3.5;
                                                                        $alphabeticalGrade = 'A-';
                                                                    } elseif ($totalMarks >= 65) {
                                                                        $numericalGrade = 3.25;
                                                                        $alphabeticalGrade = 'B+';
                                                                    } elseif ($totalMarks >= 60) {
                                                                        $numericalGrade = 3.0;
                                                                        $alphabeticalGrade = 'B';
                                                                    } elseif ($totalMarks >= 55) {
                                                                        $numericalGrade = 2.75;
                                                                        $alphabeticalGrade = 'B-';
                                                                    } elseif ($totalMarks >= 50) {
                                                                        $numericalGrade = 2.5;
                                                                        $alphabeticalGrade = 'C';
                                                                    } else {
                                                                        $numericalGrade = 0;
                                                                        $alphabeticalGrade = 'F';
                                                                    }

                                                                    echo '<td>' . $totalMarks . '</td>';
                                                                    echo '<td>' . $alphabeticalGrade . '</td>';
                                                                    echo '<td>' . $numericalGrade . '</td>';

                                                                    // Add hidden fields for GPA calculation
                                                                    echo '<input type="hidden" name="rollIds[]" value="' . htmlentities($row['RollId']) . '">';
                                                                    echo '<input type="hidden" name="numericalGrades[]" value="' . $numericalGrade . '">';
                                                                    echo '<input type="hidden" name="semester" value="' . htmlentities($semester) . '">';
                                                                    echo '<input type="hidden" name="course" value="' . htmlentities($course) . '">';
                                                                    echo '</tr>';
                                                                }
                                                            } else {
                                                                echo '<tr><td colspan="12" align="center">No records found</td></tr>';
                                                            }

                                                            echo '</tbody></table>';
                                                            echo '<button type="submit" name="calculateGPA" class="btn btn-primary">Calculate GPA</button>';
                                                            echo '</form>';
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </form>

                                            <?php
                                            if (isset($_POST['calculateGPA'])) {
                                                $semester = $_POST['semester'];
                                                $course = $_POST['course'];

                                                // Query to get the Course Credit
                                                $sql = "SELECT CourseCredit FROM tblsubjects WHERE CourseCode = :course";
                                                $query = $dbh->prepare($sql);
                                                $query->bindParam(':course', $course, PDO::PARAM_STR);
                                                $query->execute();

                                                // Fetch the Course Credit
                                                $courseCredit = $query->fetchColumn();
                                                if (!$courseCredit) {
                                                    die("Error: Course Credit not found for the selected course.");
                                                }

                                                // Get Roll IDs and Numerical Grades from the table form submission
                                                $rollIds = $_POST['rollIds']; // Array of Roll IDs from the table
                                                $numericalGrades = $_POST['numericalGrades']; // Array of Numerical Grades from the table

                                                foreach ($rollIds as $index => $rollId) {
                                                    $numericalGrade = $numericalGrades[$index];

                                                    // Prepare the SQL query for inserting GPA (Numerical Grade as GPA)
                                                    $sql = "INSERT INTO tblgpa (RollId, Semester, CourseCode, GPA)
                VALUES (:rollId, :semester, :course, :GPA)
                ON DUPLICATE KEY UPDATE GPA = :GPA";

                                                    // Prepare and bind parameters
                                                    $query = $dbh->prepare($sql);
                                                    $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
                                                    $query->bindParam(':semester', $semester, PDO::PARAM_STR);
                                                    $query->bindParam(':course', $course, PDO::PARAM_STR);
                                                    $query->bindParam(':GPA', $numericalGrade, PDO::PARAM_STR);

                                                    // Check if GPA is 0.00
                                                    if ($numericalGrade == 0.00) {
                                                        // Delete from tblmanageregistration
                                                        $sqlDeleteManage = "DELETE FROM tblmanageregistration 
                                    WHERE RollId = :rollId AND Semester = :semester AND CourseCode = :course";
                                                        $queryDeleteManage = $dbh->prepare($sqlDeleteManage);
                                                        $queryDeleteManage->bindParam(':rollId', $rollId, PDO::PARAM_STR);
                                                        $queryDeleteManage->bindParam(':semester', $semester, PDO::PARAM_STR);
                                                        $queryDeleteManage->bindParam(':course', $course, PDO::PARAM_STR);
                                                        $queryDeleteManage->execute();

                                                        // Delete from tblregistration
                                                        $sqlDeleteRegistration = "DELETE FROM tblregistration 
                                           WHERE RollId = :rollId AND Semester = :semester AND RegisteredCourse = :course";
                                                        $queryDeleteRegistration = $dbh->prepare($sqlDeleteRegistration);
                                                        $queryDeleteRegistration->bindParam(':rollId', $rollId, PDO::PARAM_STR);
                                                        $queryDeleteRegistration->bindParam(':semester', $semester, PDO::PARAM_STR);
                                                        $queryDeleteRegistration->bindParam(':course', $course, PDO::PARAM_STR);
                                                        $queryDeleteRegistration->execute();



                                                        // Execute the query and handle errors
                                                        try {
                                                            $query->execute();
                                                            echo "GPA (Numerical Grade) successfully inserted/updated for Roll ID: $rollId<br>";
                                                        } catch (PDOException $e) {
                                                            echo "Error inserting GPA for Roll ID: $rollId - " . $e->getMessage() . "<br>";
                                                        }
                                                    }
                                                }
                                            } ?>
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

            seriesDropdown.innerHTML = '<option value="">Select Series</option>';

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

            semesterDropdown.innerHTML = '<option value="">Select Semester</option>';

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

            courseDropdown.innerHTML = '<option value="">Select Course</option>';

            var key = department + '|' + semester;


            if (courseOptions[key]) {
                courseOptions[key].forEach(function(course) {
                    var optionElement = document.createElement("option");
                    optionElement.value = course;
                    optionElement.text = course;
                    courseDropdown.appendChild(optionElement);
                });
            }
        }

        var seriesOptions = {
            <?php
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
            foreach ($departments as $department => $series) {
                $uniqueSeries = array_unique($series);
                echo '"' . $department . '": ["' . implode('", "', $uniqueSeries) . '"],';
            }
            ?>
        };

        var semesterOptions = {
            <?php
            $sql = "SELECT Department, Series, Semester FROM tblclasses";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            $deptSeries = [];
            if ($query->rowCount() > 0) {
                foreach ($results as $result) {
                    $key = $result->Department . '|' . $result->Series;
                    $deptSeries[$key][] = $result->Semester;
                }
            }

            foreach ($deptSeries as $key => $semesters) {
                $uniqueSemesters = array_unique($semesters);
                echo '"' . $key . '": ["' . implode('", "', $uniqueSemesters) . '"],';
            }
            ?>
        };

        var courseOptions = {
            <?php
            $sql = "SELECT Department, Semester, CourseCode FROM tblsubjects";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            $deptSemesters = [];
            if ($query->rowCount() > 0) {
                foreach ($results as $result) {
                    $key = $result->Department . '|' . $result->Semester;
                    $deptSemesters[$key][] = $result->CourseCode;
                }
            }

            foreach ($deptSemesters as $key => $courses) {
                $uniqueCourses = array_unique($courses);
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