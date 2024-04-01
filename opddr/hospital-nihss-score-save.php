<?php   require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        if(!(
                Session::checkPermission('ADMISSION_NOTE','ADD')
            )){
            return;
        }
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $vn = $_REQUEST['vn'];
       // $hn = $_REQUEST['hn'];
        $rxdate = $_REQUEST['rxdate'];
        $rxtime = $_REQUEST['rxtime'];
        $onset_h = $_REQUEST['onset_h'];
        $onset_m = $_REQUEST['onset_m'];
        $no1a = $_REQUEST['no1a'];
        $no1a_2 = $_REQUEST['no1a_2'];
        $no1b = $_REQUEST['no1b'];
        $no1b_2 = $_REQUEST['no1b_2'];
        $no1c = $_REQUEST['no1c'];
        $no1c_2 = $_REQUEST['no1c_2'];
        $no2 = $_REQUEST['no2'];
        $no2_2 = $_REQUEST['no2_2'];
        $no3 = $_REQUEST['no3'];
        $no3_2 = $_REQUEST['no3_2'];
        $no4 = $_REQUEST['no4'];
        $no4_2 = $_REQUEST['no4_2'];
        $no5r = $_REQUEST['no5r'];
        $no5r_2 = $_REQUEST['no5r_2'];
        $no5l = $_REQUEST['no5l'];
        $no5l_2 = $_REQUEST['no5l_2'];
        $no6r = $_REQUEST['no6r'];
        $no6r_2 = $_REQUEST['no6r_2'];
        $no6l = $_REQUEST['no6l'];
        $no6l_2 = $_REQUEST['no6l_2'];
        $no7r = $_REQUEST['no7r'];
        $no7r_2 = $_REQUEST['no7r_2'];
        $no7l = $_REQUEST['no7l'];
        $no7l_2 = $_REQUEST['no7l_2'];
        $no8r = $_REQUEST['no8r'];
        $no8r_2 = $_REQUEST['no8r_2'];
        $no8l = $_REQUEST['no8l'];
        $no8l_2 = $_REQUEST['no8l_2'];
        $no9 = $_REQUEST['no9'];
        $no9_2 = $_REQUEST['no9_2'];
        $no10 = $_REQUEST['no10'];
        $no10_2 = $_REQUEST['no10_2'];
        $no11 = $_REQUEST['no11'];
        $no11_2 = $_REQUEST['no11_2'];
        $sum_all = $_REQUEST['sum_all'];
        $sumall_2 = $_REQUEST['sumall_2'];
  
        $create_user = $_SESSION['loginname'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name'];
        $nurse_pos = empty($_REQUEST['nurse_pos']) ? null : $_REQUEST['nurse_pos'];
        $update_user = $_SESSION['loginname'];
        $version = 1;
     
        try {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_nihss_score
                (vn,rxdate,rxtime,onset_h,onset_m)
                VALUES (:vn,:rxdate,:rxtime,:onset_h,:onset_m)");
                $stmt->execute(array('vn'=>$vn,'rxdate'=>$rxdate,'rxtime'=>$rxtime,'onset_h'=>$onset_h,'onset_m'=>$onset_m));
                /*$stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_consultation_summary
                (hn,an,rxdate,create_user,nurse_name,nurse_pos,update_user,create_datetime,update_datetime,version
                )
                VALUES (:hn,:an,:rxdate,:create_user,:nurse_name,:nurse_pos,:update_user,:create_datetime,:update_datetime,:version
                )");
                $stmt->execute(array('hn'=>$hn, 'an'=>$an, 'rxdate'=>$rxdate, 'create_user'=>$create_user,'nurse_name'=>$nurse_name,'nurse_pos'=>$nurse_pos,'update_user'=>$update_user,'version'=>$version
            ));*/

               // $admission_note_id = $conn->lastInsertId();

          /*      if(!empty($_REQUEST['admission_note_doctor'])){
                    foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                        $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_consultation_summary_item(id,an,doctor,create_user,create_datetime,update_user,update_datetime,version)
                        VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                        $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                        'admission_note_doctor'=>$admission_note_doctor,
                        'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                    }
                }
*/
                $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

            } catch (PDOException  $e) {
                echo $e->getMessage();
                $output_error = '<div class="alert alert-danger">ERROR !!</div>';
            }
        echo $output_error;
?>