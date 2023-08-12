<?php
session_start();
   require_once '../include/Session.php';

   $mylink = DbConstant::MAIN_LINK;
   $root = $_SERVER['DOCUMENT_ROOT']; 

 
?>
<!doctype html>
<html lang="en">

<head>
    <title>KPHIS</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="../include/favicon_io/favicon-32x32.png" rel="shortcut icon" type="image/vnd.microsoft.icon" /> 
    <!-- <link href="picture/favicon/icon12.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" /> -->
    <!-- <link href="picture/favicon/icon6.png" rel="shortcut icon" type="image/png" /> -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="..\vendor\twbs\bootstrap\dist\css\bootstrap.min.css">
    <link rel="stylesheet" href="../include/css/theme_new.css">


    <link href="../vendor/select2/select2/dist/css/select2.min.css" rel="stylesheet" />
    <link href="../node_modules/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../vendor/fortawesome/font-awesome/css/all.min.css"><!--  ICON -->
    <link rel="stylesheet" href="../include/project.css">

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="../vendor/components/jquery/jquery.min.js"></script>
    <script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/select2/select2/dist/js/select2.min.js"></script>
    <!-- <script src="vendor/fortawesome/font-awesome/js/all.min.js"></script> -->

    <script type="text/javascript" src="../vendor/datatables/datatables/media/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="../vendor/datatables/datatables/media/css/dataTables.bootstrap4.min.css"/>
    <script type="text/javascript" src="../vendor/datatables/datatables/media/js/dataTables.bootstrap4.min.js"></script>

    <script src="../vendor/moment/moment/min/moment-with-locales.min.js"></script>
    <script src="../vendor/nnnick/chartjs/dist/Chart.min.js"></script>
    <script src="../vendor/nnnick/chartjs/samples/utils.js"></script>
    <script src="../node_modules/chartjs-plugin-annotation/chartjs-plugin-annotation.min.js"></script>
    <script src="../node_modules/hammerjs/hammer.min.js"></script>
    <script src="../node_modules/chartjs-plugin-zoom/chartjs-plugin-zoom.min.js"></script>

    <script src="../node_modules/rtf.js/dist/jquery.svg.min.js"></script>
    <script src="../node_modules/rtf.js/dist/jquery.svgfilter.min.js"></script>
    <script src="../node_modules/rtf.js/dist/WMFJS.bundle.min.js"></script>
    <script src="../node_modules/rtf.js/dist/EMFJS.bundle.min.js"></script>
    <script src="../node_modules/rtf.js/dist/RTFJS.bundle.min.js"></script>

    <script src="../include/project.js"></script>
    <script src="../include/mews-score.js"></script>

    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        $.fn.select2.defaults.set("theme", "bootstrap4");

        /*
        * Hacky fix for a bug in select2 with jQuery 3.6.0's new nested-focus "protection"
        * see: https://github.com/select2/select2/issues/5993
        * see: https://github.com/jquery/jquery/issues/4382
        *
        * TODO: Recheck with the select2 GH issue and remove once this is fixed on their side
        */

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        session_keep_alive();

        
    </script>
    
</head>
<body>

<?php 

   if(!(Session::checkLoginSession())){
			// header('HTTP/1.1 401 Unauthorized');
			//header("Location: index.php");
			//echo 'no session';
			exit;
		}
 ?>

<nav class="navbar navbar-expand-sm  navbar-dark bg-success">

        <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#collapsibleNavId"
            aria-controls="collapsibleNavId" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="collapsibleNavId">
            <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
            <!--    <li class="nav-item active dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><em class="fas fa-user-md"></em> แพทย์</a>
                    <div class="dropdown-menu" aria-labelledby="dropdownId">

                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-dr-search-patient.php">รายการผู้ป่วยใน</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-dr-pre-order-list.php">Order ล่วงหน้า</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-consult-list.php?view_by=doctor">รายการผู้ป่วย Consult</a>


                    </div>
                </li> -->
                <!--<li class="nav-item active dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><em class="fas fa-user-md"></em> แพทย์ ER</a>
                    <div class="dropdown-menu" aria-labelledby="dropdownId">
                        <a class="dropdown-item" href="<?php echo $mylink ?>prs-er-search-patient.php">ผู้ป่วย ER</a>
                    </div>
                </li> -->

              <!--  <li class="nav-item active dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><em class="fas fa-user-nurse"></em> พยาบาล</a>
                    <div class="dropdown-menu" aria-labelledby="dropdownId">
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-nurse-search-patient.php">รายการผู้ป่วยใน</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-vital-sign-main.php">Vital Sign</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-nurse-index-plan-monitor.php">Nurse Planning</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-dr-pre-order-list.php">Order ล่วงหน้า</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-consult-list.php?view_by=nurse">รายการผู้ป่วย Consult</a>
                    </div>
                </li> -->

                <li class="nav-item active dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><em class="fas fa-pen"></em> บันทึก Vital Sign</a>
                    <div class="dropdown-menu" aria-labelledby="dropdownId">
                        
                        <a class="dropdown-item" href="ipd-vital-sign-main.php">Vital Sign</a>
                        
                    </div>
                </li>

                </ul>

                <ul class="navbar-nav">
                <li class="nav-item active dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="logoutDropdownId" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-user" aria-hidden="true"></i> <?=htmlspecialchars($_SESSION['name'])?></a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="logoutDropdownId">
                        
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/logout.php"><i class="fa fa-sign-out-alt" aria-hidden="true"></i>
                            ออกจากระบบ</a>
                    </div>
                </li>
                </ul>


        </div>
    </nav>

    </body>

</html>
