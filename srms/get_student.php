<?php
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["department"]) && !empty($_POST["section"]) && !empty($_POST["series"])) {
        // Fetch Students
        $department = intval($_POST['department']);
        $section = $_POST['section'];
        $series = intval($_POST['series']);

        if (is_numeric($department) || !is_numeric($series)) {
            echo htmlentities("Invalid Department or Series");
            exit;
        } else {
            $stmt = $dbh->prepare("SELECT StudentName, StudentId FROM tblstudents WHERE DepartmentId = :department AND Series = :series AND Section = :section ORDER BY StudentName");
            $stmt->execute(array(':department' => $department, ':series' => $series, ':section' => $section));
            ?><option value="">Select Student</option><?php
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <option value="<?php echo htmlentities($row['StudentId']); ?>"><?php echo htmlentities($row['StudentName']); ?></option>
                <?php
            }
        }

        // Fetch Subjects
        $status = 0;
        $stmt = $dbh->prepare("SELECT tblsubjects.SubjectName, tblsubjects.id FROM tblsubjectcombination
                               JOIN tblsubjects ON tblsubjects.id = tblsubjectcombination.SubjectId
                               WHERE tblsubjectcombination.DepartmentId = :department AND tblsubjectcombination.Series = :series AND tblsubjectcombination.Section = :section AND tblsubjectcombination.status != :status
                               ORDER BY tblsubjects.SubjectName");
        $stmt->execute(array(':department' => $department, ':series' => $series, ':section' => $section, ':status' => $status));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <p><?php echo htmlentities($row['SubjectName']); ?>
                <input type="text" name="marks[]" value="" class="form-control" required="" placeholder="Enter marks out of 100" autocomplete="off">
            </p>
            <?php
        }
    }

    // Check if Result Already Declared
    if (!empty($_POST["studclass"])) {
        $id = $_POST['studclass'];
        $dta = explode("$", $id);
        $id = $dta[0];
        $id1 = $dta[1];

        $query = $dbh->prepare("SELECT StudentId, ClassId FROM tblresult WHERE StudentId = :id1 AND ClassId = :id");
        $query->bindParam(':id1', $id1, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
            ?>
            <p>
            <?php
            echo "<span style='color:red'> Result Already Declared.</span>";
            echo "<script>$('#submit').prop('disabled', true);</script>";
            ?>
            </p>
            <?php
        }
    }
}
?>
