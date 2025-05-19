<?php    
require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

    $vn = $_REQUEST['vn'];
   
    $hn = KphisQueryUtils::getHnByVn($vn);
    
    $bw= $_REQUEST['bw'];
    $level_of_consciousness= $_REQUEST['level_of_consciousness'];
        
       



        $create_user = $_SESSION['loginname'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name'];
        // $doc_name = empty($_REQUEST['doc_name']) ? null : $_REQUEST['doc_name'];
        $nurse_pos = empty($_REQUEST['nurse_pos']) ? null : $_REQUEST['nurse_pos'];
        //$doc_pos = empty($_REQUEST['doc_pos']) ? null : $_REQUEST['doc_pos'];
        $create_datetime = $_REQUEST['create_datetime'];
        $update_user = $_SESSION['loginname'];
        $update_datetime = $_REQUEST['update_datetime'];
        $version = 1;

        try {

           /* if (  $create_user != ''
    ) */{
    
        $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_stroke_fast_track(vn
        ,create_user,version,create_datetime,update_user,update_datetime)
        VALUES(:vn
        ,:create_user,:version,now(),:update_user,now())");
        
                $stmt->execute(array('vn'=>$vn
                ,'create_user'=>$create_user,'version'=>$version,'update_user'=>$create_user));
    
            /*    $output_error = '<script>
            NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");
            </script>';*/
    
    
    
            }

              
               // $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

            } catch (PDOException  $e) {
                echo $e->getMessage();
                $output_error = '<div class="alert alert-danger">ERROR !!</div>';
            }
        echo $output_error;
?>
