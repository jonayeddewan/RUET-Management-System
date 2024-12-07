<?php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    if (isset($_GET['id'])) {
        $classid = $_GET['id'];
        $sql = "delete from tblteacher where id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        echo '<script>alert("Data deleted.")</script>';
        echo "<script>window.location.href ='manage-teachers.php'</script>";
    }
?>

    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Manage Teachers</title>
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

            <!-- ========== TOP NAVBAR ========== -->
            <?php include('includes/topbar.php'); ?>
            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">
                    <?php include('includes/leftbar.php'); ?>

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Manage Teachers</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li>Teachers</li>
                                        <li class="active">Manage Teachers</li>
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
                                                    <h5>View Teachers Info</h5>
                                                </div>
                                            </div>
                                            <?php if ($msg) { ?>
                                                <div class="alert alert-success left-icon-alert" role="alert">
                                                    <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                                </div><?php } else if ($error) { ?>
                                                <div class="alert alert-danger left-icon-alert" role="alert">
                                                    <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                            <?php } ?>
                                            <div class="panel-body p-20">
                                                <form method="post" action="" class="filter-form">
                                                    <div class="form-group">
                                                        <label for="department">Department</label>
                                                        <select name="department" id="department" class="form-control">
                                                            <option value="">Select Department</option>
                                                            <?php
                                                            $sql = "SELECT DISTINCT Department FROM tbldept";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                            if ($query->rowCount() > 0) {
                                                                foreach ($results as $result) { ?>
                                                                    <option value="<?php echo htmlentities($result->Department); ?>"><?php echo htmlentities($result->Department); ?></option>
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
                                                            <th>TeachersID</th>
                                                            <th>Name</th>
                                                            <th>Email</th>
                                                            <th>Phone</th>
                                                            <th>Gender</th>
                                                            <th>Department</th>
                                                            <th>Desigation</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tfoot>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>TeachersID</th>
                                                            <th>Name</th>
                                                            <th>Email</th>
                                                            <th>Phone</th>
                                                            <th>Gender</th>
                                                            <th>Department</th>
                                                            <th>Desigation</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </tfoot>
                                                    <tbody>
                                                        <?php
                                                        if (isset($_POST['filter'])) {
                                                            $department = $_POST['department'];
                                                            $sql = "SELECT id, TeacherId, TeacherName, TeacherEmail, TeacherPhone, Gender, Department, JoiningDate, Designation, Status FROM tblteachers WHERE 1=1";
                                                            if ($department != "") {
                                                                $sql .= " AND Department=:department";
                                                            }
                                                            $sql .= " ORDER BY id"; // Sort by RollId
                                                            $query = $dbh->prepare($sql);
                                                            if ($department != "") {
                                                                $query->bindParam(':department', $department, PDO::PARAM_STR);
                                                            }
                                                            $query->execute();
                                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        } else {
                                                            $results = [];
                                                        }
                                                        $cnt = 1;
                                                        if (count($results) > 0) {
                                                            foreach ($results as $result) { ?>
                                                                <tr>
                                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                                    <td><?php echo htmlentities($result->TeacherId); ?></td>
                                                                    <td><?php echo htmlentities($result->TeacherName); ?></td>
                                                                    <td><?php echo htmlentities($result->TeacherEmail); ?></td>
                                                                    <td><?php echo htmlentities($result->TeacherPhone); ?></td>
                                                                    <td><?php echo htmlentities($result->Gender); ?></td>
                                                                    <td><?php echo htmlentities($result->Department); ?></td>
                                                                    <td><?php echo htmlentities($result->Designation); ?></td>
                                                                    <td><?php echo htmlentities($result->RegDate); ?></td>
                                                                    <td><?php echo htmlentities($result->Status == 1 ? 'Active' : 'Abroad'); ?></td>
                                                                    <td>
                                                                        <a href="edit-teachers.php?stid=<?php echo htmlentities($result->StudentId); ?>" class="btn btn-primary btn-xs" target="_blank">Edit</a>
                                                                        <a href="delete-teachers.php?classid=<?php echo htmlentities($result->id); ?>" class="btn btn-danger btn-xs" target="_blank" onclick="return confirm('Are you sure you want to delete this Teacher?');">Delete</a>
                                                                    </td>
                                                                </tr>
                                                        <?php $cnt++;
                                                            }
                                                        } ?>
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
<?php } ?>