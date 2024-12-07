<?php
session_start();
include('includes/config.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $id = $_POST['id'];
    $sql = "SELECT * FROM tblteachers WHERE TeacherEmail =:email and TeacherId=:id";

    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':id', $id, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result) {
        $_SESSION['login'] = $result->TeacherId;
        header("Location: teacher-dashboard.php");
        exit();
    } else {
        $error = "Invalid  ID or Email";
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher's Login</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
    <style>
        .logo {
            display: block;
            margin: 0 auto;
            max-width: 100px; /* Adjust the size of the logo as needed */
        }
    </style>
</head>

<body class="">
    <div class="main-wrapper">

        <div class="">
            <div class="row">
                <h1 align="center">Rajshahi University Of Engineering And Technology
                </h1>
                <center><img src="images/RUET.png" alt="RUET Logo" class="logo"></center>

                <div class="col-lg-3"></div>
                <div class="col-lg-6">
                    <section class="section">
                        <div class="row mt-40">
                            <div class="col-md-10 col-md-offset-1 pt-50">

                                <div class="row mt-30 ">
                                    <div class="col-md-11">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title text-center">
                                                    <h4>Teacher's Login</h4>
                                                </div>
                                            </div>
                                            <div class="panel-body p-20">

                                                <form class="form-horizontal" method="post">
                                                    <div class="form-group">
                                                        <label for="inputEmail3"
                                                            class="col-sm-2 control-label">Email</label>
                                                        <div class="col-sm-10">
                                                            <input type="text" name="email" class="form-control"
                                                                id="InputEmail3" placeholder="UserName">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="inputPassword3"
                                                            class="col-sm-2 control-label">Password</label>
                                                        <div class="col-sm-10">
                                                            <input type="password" name="id" class="form-control"
                                                                id="inputPassword3" placeholder="Password">
                                                        </div>
                                                    </div>

                                                    <div class="form-group mt-20">
                                                        <div class="col-sm-offset-2 col-sm-10">

                                                            <button type="submit" name="login"
                                                                class="btn btn-success btn-labeled pull-right">Sign
                                                                in<span class="btn-label btn-label-right"><i
                                                                        class="fa fa-check"></i></span></button>
                                                            <div class="col-sm-6">
                                                                <a href="index.php">Back to Home</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                        <!-- /.panel -->
                                        <p class="text-muted text-center"><small>Developed by ECE-20</small></p>
                                    </div>
                                    <!-- /.col-md-11 -->
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- /.col-md-12 -->
                        </div>
                        <!-- /.row -->
                    </section>

                </div>
                <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /. -->

    </div>
    <!-- /.main-wrapper -->

    <!-- ========== COMMON JS FILES ========== -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/jquery-ui/jquery-ui.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>

    <!-- ========== PAGE JS FILES ========== -->

    <!-- ========== THEME JS ========== -->
    <script src="js/main.js"></script>
    <script>
        $(function () { });
    </script>

</body>

</html>