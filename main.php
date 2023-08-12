<?php
   require_once 'header.php';
?>
<body>
<nav class="navbar navbar-expand-sm  navbar-dark bg-success">

        <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#collapsibleNavId"
            aria-controls="collapsibleNavId" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="collapsibleNavId">
            <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                <li class="nav-item active dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><em class="fas fa-user-md"></em> แพทย์</a>
                    <div class="dropdown-menu" aria-labelledby="dropdownId">

                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-dr-search-patient.php">รายการผู้ป่วยใน</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-dr-pre-order-list.php">Order ล่วงหน้า</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-consult-list.php?view_by=doctor">รายการผู้ป่วย Consult</a>


                    </div>
                </li>
                <li class="nav-item active dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><em class="fas fa-user-md"></em> แพทย์ ER</a>
                    <div class="dropdown-menu" aria-labelledby="dropdownId">
                        <a class="dropdown-item" href="<?php echo $mylink ?>prs-er-search-patient.php">ผู้ป่วย ER</a>
                    </div>
                </li>

                <li class="nav-item active dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><em class="fas fa-user-nurse"></em> พยาบาล</a>
                    <div class="dropdown-menu" aria-labelledby="dropdownId">
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-nurse-search-patient.php">รายการผู้ป่วยใน</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-vital-sign-main.php">Vital Sign</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-nurse-index-plan-monitor.php">Nurse Planning</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-dr-pre-order-list.php">Order ล่วงหน้า</a>
                        <a class="dropdown-item" href="<?php echo $mylink ?>ipd-consult-list.php?view_by=nurse">รายการผู้ป่วย Consult</a>
                    </div>
                </li>
                </ul>


        </div>
    </nav>

    </body>

</html>
                