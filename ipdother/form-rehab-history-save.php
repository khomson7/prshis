<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

   // $output_error = '';

  /*  if(empty($an)

    ){
        exit;
    }
*/


$rxdate = empty($_REQUEST['rxdate']) ? null : $_REQUEST['rxdate'];
$cc = empty($_REQUEST['cc']) ? null : $_REQUEST['cc'];
$hpi =  empty($_REQUEST['hpi']) ? null : $_REQUEST['hpi'];
$past_history=  empty($_REQUEST['past_history']) ? null : $_REQUEST['past_history'];
$phychosocial=  empty($_REQUEST['phychosocial']) ? null : $_REQUEST['phychosocial'];
$disease=  empty($_REQUEST['disease']) ? null : $_REQUEST['disease'];
$treatment_received =  empty($_REQUEST['treatment_received']) ? null : $_REQUEST['treatment_received'];
$pe_1st =  empty($_REQUEST['pe_1st']) ? null : $_REQUEST['pe_1st'];
$pe_1st_date =  empty($_REQUEST['pe_1st_date']) ? null : $_REQUEST['pe_1st_date'];
$diagnosis =  empty($_REQUEST['diagnosis']) ? null : $_REQUEST['diagnosis'];
$goal_date = empty($_REQUEST['goal_date']) ? null : $_REQUEST['goal_date'];
$goal = empty($_REQUEST['goal']) ? null : $_REQUEST['goal'];
$due_date = empty($_REQUEST['due_date']) ? null : $_REQUEST['due_date'];
$treatment_plan = empty($_REQUEST['treatment_plan']) ? null : $_REQUEST['treatment_plan'];
$summary_date = empty($_REQUEST['summary_date']) ? null : $_REQUEST['summary_date'];
$summary_of_dc = empty($_REQUEST['summary_of_dc']) ? null : $_REQUEST['summary_of_dc'];
     
$update_datetime= date('Y-m-d H:i:s');
$update_user = $_SESSION['loginname'];
$version0 = $_REQUEST['version'];
$version = $version0 + 1;



    //$create_datetime = ใช้ NOW()
    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    //$update_datetime = ใช้ NOW()
   // $update_datetime  = $datenow = date('Y-m-d H:i:s');
    $update_user  = $_SESSION['loginname'];

    $version = 1;

    try {

        if ( $rxdate != '' 
) {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_rehab_history(hn,an,rxdate
            ,cc,hpi,past_history,phychosocial,disease
            ,treatment_received,pe_1st,pe_1st_date,diagnosis,goal_date
            ,goal,due_date,treatment_plan,summary_date,summary_of_dc
            ,create_user,create_datetime,update_user,version,update_datetime)
            VALUES(:hn,:an,:rxdate,:cc,:hpi,:past_history,:phychosocial,:disease
            ,:treatment_received,:pe_1st,:pe_1st_date,:diagnosis,:goal_date 
            ,:goal,:due_date,:treatment_plan,:summary_date,:summary_of_dc
            ,:create_user,:create_datetime,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('hn'=>$hn,'an'=>$an,'rxdate'=>$rxdate
            ,'cc'=>$cc,'hpi'=>$hpi,'past_history'=>$past_history,'phychosocial'=>$phychosocial,'disease'=>$disease
            ,'treatment_received'=>$treatment_received,'pe_1st'=>$pe_1st,'pe_1st_date'=>$pe_1st_date,'diagnosis'=>$diagnosis,'goal_date'=>$goal_date 
            ,'goal'=>$goal,'due_date'=>$due_date,'treatment_plan'=>$treatment_plan,'summary_date'=>$summary_date,'summary_of_dc'=>$summary_of_dc
            ,'create_user'=>$create_user,'create_datetime' => $create_datetime,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime));
            $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");
        </script>';

       // $pre_nursenote_id = $conn->lastInsertId();
//echo $pre_nursenote_id ;
       // echo $_REQUEST['doctor'];
   /*  if(!empty($_REQUEST['doctor'])){
            foreach($_REQUEST['doctor'] as $doctor){
                $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote_item(pre_nursenote_id,an,doctor,create_user,create_datetime,update_user,update_datetime,version)
                VALUES (:pre_nursenote_id,:an,:doctor,:create_user,now(),:update_user,now(),:version)");
                $stmt_item->execute(array('pre_nursenote_id'=>$pre_nursenote_id,'an'=>$an,'doctor'=>$doctor
                ,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version
                ));
            }
        }  */

        } /*else if( $doctor0 ==''){
            $output_error = '<script>
            alert("กรุณา Attending physician");
        </script>';
        }*/
        
//บันทึกรายการ
     /*   $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote(rxdate,rxtime,refer_from,cc,hpi
        ,an,create_user,update_user,version,create_datetime,update_datetime)
        VALUES(:rxdate,:rxtime,:refer_from,:cc,:hpi
        ,:an,:create_user,:update_user,:version,:create_datetime,:update_datetime)");

        $stmt->execute(array('rxdate'=>$rxdate,'rxtime'=>$rxtime,'refer_from'=>$refer_from ,'cc'=>$cc ,'hpi'=>$hpi      
        ,'an'=>$an,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version , 'create_datetime' =>$create_datetime , 'update_datetime' =>$create_datetime));

        $output_error = '<div class="alert alert-success">บันทึกข้อมูลสำเร็จ</div>';

        */
    }catch (PDOException  $e) {
        echo $e->getMessage();
        $output_error = '<div class="alert alert-danger">ERROR !!</div>';
    }
    echo $output_error;
?>
