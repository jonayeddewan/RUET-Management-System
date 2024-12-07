<?php
session_start();
error_reporting(0);

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
}
include('includes/config.php');

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// Create an instance; passing true enables exceptions
$mail = new PHPMailer(true);

$teacherid = $_SESSION['login'];

// Fetch teacher's department to ensure proper access
$sql = "SELECT Department FROM tblteachers WHERE TeacherId=:teacherid";
$query = $dbh->prepare($sql);
$query->bindParam(':teacherid', $teacherid, PDO::PARAM_STR);
$query->execute();
$teacherDepartment = $query->fetchColumn();

if (!$teacherDepartment) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['send_marks'])) {
    // Get filters from the form
    $department = $_POST['department'] ?? null;
    $series = $_POST['series'] ?? null;
    $semester = $_POST['semester'] ?? null;
    $course = $_POST['course'] ?? null;

    if (!$department || !$series || !$semester || !$course) {
        echo "Please fill all the required fields.";
        exit();
    }

    // SQL query to fetch student and marks data based on filters
    $sql = "SELECT 
                s.StudentName, s.RollId, s.StudentEmail,
                m.CT_1, m.CT_2, m.CT_3, m.CT_4, 
                m.Assignment, m.Attendance
            FROM tblstudents s
            LEFT JOIN tblmarks m ON s.RollId = m.RollId
            INNER JOIN tblbackregistration r ON s.RollId = r.RollId
            WHERE s.Department = :department 
              AND s.Series = :series 
              AND r.Semester = :semester 
              AND m.CourseCode = :course";

    $query = $dbh->prepare($sql);
    $query->execute([
        ':department' => $department,
        ':series' => $series,
        ':semester' => $semester,
        ':course' => $course
    ]);

    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if (empty($results)) {
        echo "No students found for the selected filters.";
        exit();
    }

    // Array to track emails that have been sent
    $sentEmails = [];

    // Email each student their marks
    foreach ($results as $row) {
        $email = $row->StudentEmail;

        // Skip if the email is already processed
        if (in_array($email, $sentEmails)) {
            continue;
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Invalid email address for student {$row->StudentName}: $email<br>";
            continue; // Skip invalid email addresses
        }

        $studentName = $row->StudentName;
        $ctMarks = [
            'CT_1' => $row->CT_1 ?? 'Not Published Yet',
            'CT_2' => $row->CT_2 ?? 'Not Published Yet',
            'CT_3' => $row->CT_3 ?? 'Not Published Yet',
            'CT_4' => $row->CT_4 ?? 'Not Published Yet'
        ];
        $assignment = $row->Assignment ?? 'Not Available';
        $attendance = $row->Attendance ?? 'Not Available';

        // Calculate average of the best 3 CT marks
        $validMarks = array_filter($ctMarks, fn($mark) => is_numeric($mark));
        rsort($validMarks);
        $averageCT = count($validMarks) >= 3
            ? ceil(array_sum(array_slice($validMarks, 0, 3)) / 3)
            : 'Not Available';

        // Email body content
        $body = "<p>Dear $studentName,</p>
                 <p>Here are your marks for $course:</p>
                 <ul>
                    <li>CT 1: {$ctMarks['CT_1']}</li>
                    <li>CT 2: {$ctMarks['CT_2']}</li>
                    <li>CT 3: {$ctMarks['CT_3']}</li>
                    <li>CT 4: {$ctMarks['CT_4']}</li>
                    <li>Average of Best 3 CTs: $averageCT</li>
                    <li>Assignment: $assignment</li>
                    <li>Attendance: $attendance</li>
                 </ul>
                 <p>Best Regards,<br>RUET ECE</p>";

        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ruetecemailer@gmail.com';
            $mail->Password = 'vmwtflzdhqppllum';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Recipients
            $mail->setFrom('ruetecemailer@gmail.com', 'RUET ECE');
            $mail->addAddress($email, $studentName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your CT, Assignment & Attendance Marks';
            $mail->Body = $body;

            $mail->send();
            $sentEmails[] = $email; // Add to sent emails list
            echo "Email Send successfully for Roll ID: $studentName<br>";
        } catch (Exception $e) {
            echo "Failed to send email to $studentName ($email). Error: {$mail->ErrorInfo}<br>";
        }

        // Clear recipients for the next iteration
        $mail->clearAddresses();
    }
    header("Location: manage-results.php?status=success");
    exit();
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
                                <h2 class="title">Manage Results</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="teacher-dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> Result</li>
                                    <li class="active">Manage Results</li>
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
                                                                <option value="<?php echo htmlentities($result->Department); ?>"
                                                                    <?php echo isset($_POST['department']) && $_POST['department'] == $result->Department ? 'selected' : ''; ?>>
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
                                                        <?php if (isset($_POST['series'])) { ?>
                                                            <option value="<?php echo $_POST['series']; ?>" selected>
                                                                <?php echo $_POST['series']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="semester">Semester</label>
                                                    <select name="semester" id="semester" class="form-control"
                                                        onchange="updateCourses()">
                                                        <option value="">Select Semester</option>
                                                        <?php if (isset($_POST['semester'])) { ?>
                                                            <option value="<?php echo $_POST['semester']; ?>" selected>
                                                                <?php echo $_POST['semester']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="course">Course</label>
                                                    <select name="course" id="course" class="form-control">
                                                        <option value="">Select Course</option>
                                                        <?php if (isset($_POST['course'])) { ?>
                                                            <option value="<?php echo $_POST['course']; ?>" selected>
                                                                <?php echo $_POST['course']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <button type="submit" name="filter"
                                                    class="btn btn-primary">Filter</button>
                                            </form>
                                            <form action="" method="post">
                                                <table id="example" class="display table table-striped table-bordered"
                                                    cellspacing="0" width="100%">
                                                    <tbody>
                                                    <tbody>
                                                        <?php
                                                        if (isset($_POST['filter'])) {
                                                            $GLOBALS['department'] = $_POST['department'];
                                                            $GLOBALS['series'] = $_POST['series'];
                                                            $GLOBALS['semester'] = $_POST['semester'];
                                                            $GLOBALS['course'] = $_POST['course'];

                                                            $department = $GLOBALS['department'];
                                                            $series = $GLOBALS['series'];
                                                            $semester = $GLOBALS['semester'];
                                                            $course = $GLOBALS['course'];

                                                            // Fetch course credit to determine marks columns
                                                            $sql = "SELECT CourseCredit FROM tblsubjects WHERE CourseCode = :course";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute([':course' => $course]);
                                                            $courseCredit = $query->fetchColumn();

                                                            // Initialize marks columns and query string
                                                            if ($courseCredit < 3.0) {
                                                                // Use tblsessional columns (no Best 3 CT Average)
                                                                $marksColumns = ['Attendance', 'Quiz', 'BoardViva', 'Performance'];
                                                                $sql = "SELECT DISTINCT s.StudentName, s.RollId, s.RegistrationId, s.Department, s.Section, s.Series, s.RegDate, s.Status,
                                                            m.Attendance, m.Quiz, m.BoardViva, m.Performance
                                                            FROM tblstudents s
                                                            LEFT JOIN tblsessional m ON s.RollId = m.RollId
                                                            INNER JOIN tblbackregistration r ON s.RollId = r.RollId
                                                            WHERE m.CourseCode = :course";
                                                            } else {
                                                                // Use tblmarks columns (include Best 3 CT Average)
                                                                $marksColumns = ['CT_1', 'CT_2', 'CT_3', 'CT_4', 'Attendance', 'Assignment', 'Semester_Final'];
                                                                $sql = "SELECT DISTINCT s.StudentName, s.RollId, s.RegistrationId, s.Department, s.Section, s.Series, s.RegDate, s.Status,
                                                            m.CT_1, m.CT_2, m.CT_3, m.CT_4, m.Assignment, m.Semester_Final, m.Attendance
                                                            FROM tblstudents s
                                                            LEFT JOIN tblmarks m ON s.RollId = m.RollId
                                                            INNER JOIN tblbackregistration r ON s.RollId = r.RollId
                                                            WHERE m.CourseCode = :course";
                                                            }

                                                            // Add filters for department, series, and semester
                                                            if ($department)
                                                                $sql .= " AND s.Department = :department";
                                                            if ($series)
                                                                $sql .= " AND s.Series = :series";
                                                            if ($semester)
                                                                $sql .= " AND m.Semester = :semester";

                                                            // Prepare and execute query
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute([
                                                                ':course' => $course,
                                                                ':department' => $department,
                                                                ':series' => $series,
                                                                ':semester' => $semester
                                                            ]);

                                                            $results = $query->fetchAll(PDO::FETCH_ASSOC);

                                                            if ($query->rowCount() > 0) {
                                                                // Table start
                                                                echo '<table class="table table-bordered">';

                                                                // Table header
                                                                echo '<thead><tr>';
                                                                echo '<th>#</th><th>Student Name</th><th>Roll ID</th>';

                                                                // Dynamically create table headers for marks columns
                                                                foreach ($marksColumns as $column) {
                                                                    echo "<th>" . htmlentities($column) . "</th>"; // Display column name in header
                                                                }

                                                                // Add the "Best 3 CT Average" column header if fetching from tblmarks
                                                                if ($courseCredit >= 3.0) {
                                                                    echo '<th>Best 3 CT Average</th>';
                                                                }

                                                                echo '</tr></thead><tbody>';

                                                                // Display data rows dynamically based on marks columns
                                                                $counter = 1; // Counter for row numbering
                                                                foreach ($results as $row) {
                                                                    echo '<tr>';
                                                                    echo '<td>' . $counter++ . '</td>'; // Row number
                                                                    echo '<td>' . htmlentities($row['StudentName']) . '</td>';
                                                                    echo '<td>' . htmlentities($row['RollId']) . '</td>';

                                                                    // Loop through each marks column and display the corresponding value
                                                                    $ctScores = []; // Array to store CT marks for calculating the average
                                                                    foreach ($marksColumns as $column) {
                                                                        // If the column is a CT score, add it to the CT scores array
                                                                        if (in_array($column, ['CT_1', 'CT_2', 'CT_3', 'CT_4'])) {
                                                                            $ctScores[] = $row[$column] ?? 0; // Use 0 if the value is null
                                                                        }
                                                                        echo '<td>' . htmlentities($row[$column] ?? 'N/A') . '</td>'; // Display marks or 'N/A' if not available
                                                                    }

                                                                    // Calculate the best 3 average for CT marks if fetching from tblmarks
                                                                    if ($courseCredit >= 3.0 && count($ctScores) > 0) {
                                                                        // Sort the array in descending order to get the best 3 marks
                                                                        rsort($ctScores);
                                                                        // Take the top 3 scores and calculate their average
                                                                        $bestThreeAverage = array_sum(array_slice($ctScores, 0, 3)) / 3;
                                                                        $bestThreeAverage = ceil($bestThreeAverage);
                                                                        echo '<td>' . $bestThreeAverage . '</td>'; // Display the average
                                                                    } else {
                                                                        // If no CT marks or fetching from tblsessional, do not add an empty column for Best 3 CT Average
                                                                        if ($courseCredit >= 3.0) {
                                                                            echo '<td></td>'; // This is to ensure no empty cell when course credit is less than 3
                                                                        }
                                                                    }

                                                                    echo '</tr>';
                                                                }

                                                                echo '</tbody></table>';
                                                            } else {
                                                                echo '<tr><td colspan="9" align="center">No records found</td></tr>';
                                                            }
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>

                                                <input type="hidden" name="department"
                                                    value="<?php echo $_POST['department'] ?? ''; ?>">
                                                <input type="hidden" name="series"
                                                    value="<?php echo $_POST['series'] ?? ''; ?>">
                                                <input type="hidden" name="semester"
                                                    value="<?php echo $_POST['semester'] ?? ''; ?>">
                                                <input type="hidden" name="course"
                                                    value="<?php echo $_POST['course'] ?? ''; ?>">
                                                <button type="submit" name="send_marks" class="btn btn-primary">Send
                                                    Marks</button>
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