<?php   require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        if(!(Session::checkPermission('ADMISSION_NOTE','EDIT'))){
            return;
        }
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $id = $_REQUEST['id'];

        $vn = $_REQUEST['vn'];
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
        $version = $_REQUEST['version'];
        $version = $version + 1;
            // empty($_REQUEST['']) ? null : $_REQUEST[''];

             

        try {
            $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_nihss_score SET  vn=:vn,rxdate=:rxdate,rxtime=:rxtime,onset_h=:onset_h,onset_m=:onset_m
            ,no1a=:no1a,no1a_2=:no1a_2
            ,nurse_name=:nurse_name,nurse_pos=:nurse_pos,update_user=:update_user,update_datetime = NOW(),version=:version
            WHERE id=:id");
            $stmt->execute(array('id'=>$id, 'vn'=>$vn,'rxdate'=>$rxdate,'rxtime'=>$rxtime,'onset_h'=>$onset_h,'onset_m'=>$onset_m
            ,'no1a'=>$no1a,'no1a_2'=>$no1a_2
            ,'nurse_name'=>$nurse_name,'nurse_pos'=>$nurse_pos,'update_user'=>$update_user,'version'=>$version
        ));

      /*      if(!empty($_REQUEST['admission_note_doctor'])){
                $stmt_item_check = "SELECT COUNT(*) AS count_item  FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item WHERE an=:an";
                $stmt_item_check = $conn->prepare($stmt_item_check);
                $stmt_item_check->execute(['an'=>$an]);
                $row_item_check = $stmt_item_check->fetch();
                $count_item = $row_item_check['count_item'];
                if($count_item > 0){
                    $stmt_item_delete = "DELETE FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item WHERE an=:an";
                    $stmt_item_delete = $conn->prepare($stmt_item_delete);
                    $stmt_item_delete->execute(['an'=>$an]);
                }
                foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                    $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item
                    (admission_note_id,an,admission_note_doctor,create_user,create_datetime,update_user,update_datetime,version)
                    VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                    $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                    'admission_note_doctor'=>$admission_note_doctor,
                    'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                }
            }
*/
            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'IPD-CONSULTATION-SUMMARY',
                'action'=>'UPDATE',
                'an'=>$an,
            ),JSON_UNESCAPED_UNICODE));

            $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

            } catch (PDOException  $e) {
                echo $e->getMessage();
                $output_error = '<div class="alert alert-danger">ERROR !!</div>';
            }

        echo $output_error;

?>