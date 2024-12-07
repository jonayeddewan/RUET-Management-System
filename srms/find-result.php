<?php
session_start();
include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rollid = $_POST['rollid'];
    $department = $_POST['department'];

    $sql = "SELECT * FROM tblstudents WHERE RollId=:rollid AND Department=:department";
    $query = $dbh->prepare($sql);
    $query->bindParam(':rollid', $rollid, PDO::PARAM_STR);
    $query->bindParam(':department', $department, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        $_SESSION['login'] = $result->RollId; // Store RollId in session
        header("Location: student-dash.php");
        exit();
    } else {
        $error = "Invalid Roll ID or Department";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Result Management System</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/icheck/skins/flat/blue.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
</head>

<body class="">
    <div class="main-wrapper">
        <div class="login-bg-color bg-black-300">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <div class="panel login-box">
                        <div class="panel-heading">
                            <div class="panel-title text-center">
                                <h4>School Result Management System</h4>
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <form action="" method="post">
                                <div class="form-group">
                                    <label for="rollid">Enter your Roll Id</label>
                                    <input type="text" class="form-control" id="rollid" placeholder="Enter Your Roll Id"
                                        autocomplete="off" name="rollid" required>
                                </div>
                                <div class="form-group">
                                    <label for="default" class="col-sm-2 control-label">Department</label>
                                    <select name="department" class="form-control" id="default" required="required">
                                        <option value="">Select Department</option>
                                        <?php
                                        $sql = "SELECT DISTINCT Department from tbldept";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $result) {
                                                ?>
                                        <option value="<?php echo htmlentities($result->Department); ?>">
                                            <?php echo htmlentities($result->Department); ?>
                                        </option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php if (isset($error)) { ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                                <?php } ?>
                                <div class="form-group mt-20">
                                    <button type="submit" class="btn btn-success btn-labeled pull-right">Search<span
                                            class="btn-label btn-label-right"><i
                                                class="fa fa-check"></i></span></button>
                                    <div class="col-sm-6">
                                        <a href="index.php">Back to Home</a>
                                    </div>
                                </div>
                            </form>
                            <hr>
                        </div>
                    </div>
                    <p class="text-muted text-center"><small>Student Result Management System</small></p>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/jquery-ui/jquery-ui.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>
    <script src="js/icheck/icheck.min.js"></script>
    <script src="js/main.js"></script>
    <script>
    $(function() {
        $('input.flat-blue-style').iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });
    });
    </script>
</body>

</html>
