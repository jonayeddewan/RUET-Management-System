<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
} else {
    $rollId = $_SESSION['login'];
    $error = '';
    $results = [];
    $scgpa = 0;
    $cgpa = 0;

    if (isset($_POST['filter'])) {
        $semester = $_POST['semester'];

        if (empty($semester)) {
            $error = "Please select a Semester!";
        } else {
            // Fetch semester results
            $sql = "SELECT CourseCode, CT_1, CT_2, CT_3, CT_4, Attendance, Assignment, Semester_Final 
                    FROM tblmarks 
                    WHERE RollId = :rollId AND Semester = :semester";
            $query = $dbh->prepare($sql);
            $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
            $query->bindParam(':semester', $semester, PDO::PARAM_STR);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            // Fetch CGPA from tblcgpa
            $sql_cgpa = "SELECT CGPA FROM tblcgpa WHERE RollId = :rollId AND Semester = :semester LIMIT 1";
            $query_cgpa = $dbh->prepare($sql_cgpa);
            $query_cgpa->bindParam(':rollId', $rollId, PDO::PARAM_STR);
            $query_cgpa->bindParam(':semester', $semester, PDO::PARAM_STR);
            $query_cgpa->execute();
            $cgpa_result = $query_cgpa->fetch(PDO::FETCH_OBJ);
            $cgpa = $cgpa_result ? $cgpa_result->CGPA : 0;

            // Calculate SCGPA
            $total_points = 0;
            $total_courses = count($results);
            foreach ($results as $result) {
                $average_ct = (array_sum([$result->CT_1, $result->CT_2, $result->CT_3, $result->CT_4]) - min($result->CT_1, $result->CT_2, $result->CT_3, $result->CT_4)) / 3;
                $total_score = $average_ct + $result->Attendance + $result->Assignment + $result->Semester_Final;
                $total_points += calculateGPA($total_score);
            }
            $scgpa = $total_courses > 0 ? $total_points / $total_courses : 0;
        }
    }

    function calculateGPA($score)
    {
        if ($score >= 80) return 4.0;
        elseif ($score >= 75) return 3.75;
        elseif ($score >= 70) return 3.5;
        elseif ($score >= 65) return 3.25;
        elseif ($score >= 60) return 3.0;
        elseif ($score >= 55) return 2.75;
        elseif ($score >= 50) return 2.5;
        elseif ($score >= 45) return 2.25;
        elseif ($score >= 40) return 2.0;
        else return 0.0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Semester Result</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <style>
        .errorWrap {
            padding: 10px;
            background: #fff;
            border-left: 4px solid #dd3d36;
        }

        .succWrap {
            padding: 10px;
            background: #fff;
            border-left: 4px solid #5cb85c;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="text-center">Search Semester Result</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="semester">Semester</label>
                <select name="semester" id="semester" class="form-control">
                    <option value="">Select Semester</option>
                    <?php
                    $sql = "SELECT DISTINCT Semester FROM tblmarks WHERE RollId = :rollId";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':rollId', $rollId, PDO::PARAM_STR);
                    $query->execute();
                    $semesters = $query->fetchAll(PDO::FETCH_OBJ);
                    foreach ($semesters as $result) { ?>
                        <option value="<?php echo htmlentities($result->Semester); ?>">
                            <?php echo htmlentities($result->Semester); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" name="filter" class="btn btn-primary">Filter</button>
        </form>

        <?php if ($error) { ?>
            <div class="errorWrap"><strong>ERROR</strong>: <?php echo htmlentities($error); ?></div>
        <?php } ?>

        <?php if (!empty($results)) { ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Course Code</th>
                        <th>Average CT</th>
                        <th>Attendance</th>
                        <th>Assignment</th>
                        <th>Semester Final</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cnt = 1;
                    foreach ($results as $result) {
                        $average_ct = (array_sum([$result->CT_1, $result->CT_2, $result->CT_3, $result->CT_4]) - min($result->CT_1, $result->CT_2, $result->CT_3, $result->CT_4)) / 3;
                        $total_score = $average_ct + $result->Attendance + $result->Assignment + $result->Semester_Final;
                    ?>
                        <tr>
                            <td><?php echo htmlentities($cnt); ?></td>
                            <td><?php echo htmlentities($result->CourseCode); ?></td>
                            <td><?php echo htmlentities(round($average_ct, 2)); ?></td>
                            <td><?php echo htmlentities($result->Attendance); ?></td>
                            <td><?php echo htmlentities($result->Assignment); ?></td>
                            <td><?php echo htmlentities($result->Semester_Final); ?></td>
                            <td><?php echo htmlentities(calculateGPA($total_score)); ?></td>
                        </tr>
                    <?php $cnt++;
                    } ?>
                    <tr>
                        <td colspan="6"><strong>SCGPA:</strong> <?php echo htmlentities(round($scgpa, 2)); ?></td>
                        <td><strong>CGPA:</strong> <?php echo htmlentities(round($cgpa, 2)); ?></td>
                    </tr>
                </tbody>
            </table>
            <button onclick="window.print();" class="btn btn-success">Print</button>
        <?php } ?>
    </div>
</body>

</html>