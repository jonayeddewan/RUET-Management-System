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
    <title>Teacher's Portal | Dashboard</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="css/toastr/toastr.min.css" media="screen">
    <link rel="stylesheet" href="css/icheck/skins/line/blue.css">
    <link rel="stylesheet" href="css/icheck/skins/line/red.css">
    <link rel="stylesheet" href="css/icheck/skins/line/green.css">
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="../js/modernizr/modernizr.min.js"></script>
    <style>
        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            /* Adjust this value as needed */
        }

        .student-info {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .student-info h3 {
            margin-top: 0;
            color: #333;
        }

        .student-info p {
            font-size: 14px;
            color: #555;
        }

        .student-info p strong {
            color: #000;
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
                            <div class="col-sm-6">
                                <h2 class="title">Teacher's Dashboard</h2>
                            </div>
                            <ul class="nav navbar-nav navbar-right" data-dropdown-in="fadeIn"
                                data-dropdown-out="fadeOut">
                                <li class="hidden-xs">
                                    <a href="#">Welcome, <?php echo htmlentities($result->TeacherName); ?></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <section class="section">
                        <div class="container-fluid">
                            <div class="row center-container">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="panel student-info">
                                        <div class="panel-body">
                                            <h3>Teacher Information</h3>
                                            <p><strong>Name:</strong> <?php echo htmlentities($result->TeacherName); ?>
                                            </p>

                                            <p><strong>Teacher ID:</strong>
                                                <?php echo htmlentities($result->TeacherId); ?>
                                            </p>
                                            <p><strong>Email:</strong>
                                                <?php echo htmlentities($result->TeacherEmail); ?></p>
                                            <p><strong>Department:</strong>
                                                <?php echo htmlentities($result->Department); ?></p>
                                            <p><strong>Designation:</strong>
                                                <?php echo htmlentities($result->Designation); ?>
                                            </p>
                                            <p><strong>Phone Number:</strong>
                                                0<?php echo htmlentities($result->TeacherPhone); ?>
                                            </p>
                                            <p><strong>Joining Date:</strong>
                                                <?php echo htmlentities($result->JoiningDate); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

            </div>
            <!-- /.content-container -->
        </div>
        <!-- /.content-wrapper -->

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
    <script src="js/prism/prism.js"></script>
    <script src="js/waypoint/waypoints.min.js"></script>
    <script src="js/counterUp/jquery.counterup.min.js"></script>
    <script src="js/amcharts/amcharts.js"></script>
    <script src="js/amcharts/serial.js"></script>
    <script src="js/amcharts/plugins/export/export.min.js"></script>
    <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css" media="all" />
    <script src="js/amcharts/themes/light.js"></script>
    <script src="js/toastr/toastr.min.js"></script>
    <script src="js/icheck/icheck.min.js"></script>

    <!-- ========== THEME JS ========== -->
    <script src="js/main.js"></script>
    <script src="js/production-chart.js"></script>
    <script src="js/traffic-chart.js"></script>
    <script src="js/task-list.js"></script>
    <script>
        $(function() {
            // Counter for dashboard stats
            $('.counter').counterUp({
                delay: 10,
                time: 1000
            });
            // Welcome notification
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
            toastr["success"]("Welcome to student Result Management System!");
        });
    </script>
</body>

</html>