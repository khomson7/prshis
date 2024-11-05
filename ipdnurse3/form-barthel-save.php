<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
   // $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        
   $chronic_check = empty($_REQUEST['chronic_check']) ? null : $_REQUEST['chronic_check'];
   $type_check = empty($_REQUEST['type_check']) ? null : $_REQUEST['type_check'];
   $feeding = empty($_REQUEST['feeding']) ? null : $_REQUEST['feeding'];
   $tranfer = empty($_REQUEST['tranfer']) ? null : $_REQUEST['tranfer'];
   $grooming = empty($_REQUEST['grooming']) ? null : $_REQUEST['grooming'];
   $toilet_use = empty($_REQUEST['toilet_use']) ? null : $_REQUEST['toilet_use'];
   $bathing = empty($_REQUEST['bathing']) ? null : $_REQUEST['bathing'];
   $mobility = empty($_REQUEST['mobility']) ? null : $_REQUEST['mobility'];
   $stairs = empty($_REQUEST['stairs']) ? null : $_REQUEST['stairs'];
   $dressing = empty($_REQUEST['dressing']) ? null : $_REQUEST['dressing'];
   $bowel = empty($_REQUEST['bowel']) ? null : $_REQUEST['bowel'];
   $bladder = empty($_REQUEST['bladder']) ? null : $_REQUEST['bladder'];
   $feeding_dc = empty($_REQUEST['feeding_dc']) ? null : $_REQUEST['feeding_dc'];
   $tranfer_dc = empty($_REQUEST['tranfer_dc']) ? null : $_REQUEST['tranfer_dc'];
   $grooming_dc = empty($_REQUEST['grooming_dc']) ? null : $_REQUEST['grooming_dc'];
   $toilet_use_dc = empty($_REQUEST['toilet_use_dc']) ? null : $_REQUEST['toilet_use_dc'];
   $bathing_dc = empty($_REQUEST['bathing_dc']) ? null : $_REQUEST['bathing_dc'];
   $mobility_dc = empty($_REQUEST['mobility_dc']) ? null : $_REQUEST['mobility_dc'];
   $stairs_dc = empty($_REQUEST['stairs_dc']) ? null : $_REQUEST['stairs_dc'];
   $dressing_dc = empty($_REQUEST['dressing_dc']) ? null : $_REQUEST['dressing_dc'];
   $bowel_dc = empty($_REQUEST['bowel_dc']) ? null : $_REQUEST['bowel_dc'];
   $bladder_dc = empty($_REQUEST['bladder_dc']) ? null : $_REQUEST['bladder_dc'];
   $rankin_scale = empty($_REQUEST['rankin_scale']) ? null : $_REQUEST['rankin_scale'];
   $rankin_scale_dc = empty($_REQUEST['rankin_scale_dc']) ? null : $_REQUEST['rankin_scale_dc'];
   
   if($feeding == 1 || $feeding == null){
           $feedings = 0;
   }else{
           $feedings =  $feeding;
   }
   if($tranfer == 1 || $tranfer == null){
           $tranfers = 0;
   }else{
           $tranfers =  $tranfer;
   }
   if($grooming == 1 || $grooming == null){
           $groomings = 0;
   }else{
           $groomings =  $grooming;
   }
   if($toilet_use == 1 || $toilet_use == null){
           $toilet_uses = 0;
   }else{
           $toilet_uses =  $toilet_use;
   }
   if($bathing == 1 || $bathing == null){
           $bathings = 0;
   }else{
           $bathings =  $bathing;
   }
   if($mobility== 1 || $mobility== null){
           $mobilitys = 0;
   }else{
           $mobilitys =  $mobility;
   }
   if($stairs== 1 || $stairs== null){
           $stairss = 0;
   }else{
           $stairss =  $stairs;
   }
   if($dressing== 1 || $dressing== null){
           $dressings = 0;
   }else{
           $dressings =  $dressing;
   }
   if($bowel== 1 || $bowel== null){
           $bowels = 0;
   }else{
           $bowels =  $bowel;
   }
   if($bladder== 1 || $bladder== null){
           $bladders = 0;
   }else{
           $bladders =  $bladder;
   }

   $score = $feedings + $tranfers + $groomings + $toilet_uses + $bathings + $mobilitys + $stairss + $dressings + $bowels + $bladders;

   if($feeding_dc == 1 || $feeding_dc == null){
           $feedings_dc = 0;
   }else{
           $feedings_dc =  $feeding_dc;
   }
   if($tranfer_dc == 1 || $tranfer_dc == null){
           $tranfers_dc = 0;
   }else{
           $tranfers_dc =  $tranfer_dc;
   }
   if($grooming_dc == 1 || $grooming_dc == null){
           $groomings_dc = 0;
   }else{
           $groomings_dc =  $grooming_dc;
   }
   if($toilet_use_dc == 1 || $toilet_use_dc == null){
           $toilet_uses_dc = 0;
   }else{
           $toilet_uses_dc =  $toilet_use_dc;
   }
   if($bathing_dc == 1 || $bathing_dc == null){
           $bathings_dc = 0;
   }else{
           $bathings_dc =  $bathing_dc;
   }
   if($mobility_dc == 1 || $mobility_dc == null){
           $mobilitys_dc = 0;
   }else{
           $mobilitys_dc =  $mobility_dc;
   }
   if($stairs_dc == 1 || $stairs_dc == null){
           $stairss_dc = 0;
   }else{
           $stairss_dc =  $stairs_dc;
   }
   if($stairs_dc == 1 || $stairs_dc == null){
           $stairss_dc = 0;
   }else{
           $stairss_dc =  $stairs_dc;
   }
   if($dressing_dc == 1 || $dressing_dc == null){
           $dressings_dc = 0;
   }else{
           $dressings_dc =  $dressing_dc;
   }
   if($bowel_dc == 1 || $bowel_dc == null){
           $bowels_dc = 0;
   }else{
           $bowels_dc =  $bowel_dc;
   }
   if($bladder_dc == 1 || $bladder_dc == null){
           $bladders_dc = 0;
   }else{
           $bladders_dc =  $bladder_dc;
   }

   $score_dc = $feedings_dc + $tranfers_dc + $groomings_dc + $toilet_uses_dc + $bathings_dc + $mobilitys_dc + $stairss_dc + $dressings_dc + $bowels_dc + $bladders_dc;
        


    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    $update_user  = $_SESSION['loginname'];
    $update_datetime =  date('Y-m-d H:i:s');

    $version = 1;

    try {

        if ( $chronic_check != '' && $type_check != ''
) {


  $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_barthel_index(an,chronic_check,type_check
            ,feeding,tranfer,grooming,toilet_use,bathing,mobility
            ,stairs,dressing,bowel,bladder,score,feeding_dc,tranfer_dc,grooming_dc,toilet_use_dc,bathing_dc,mobility_dc
            ,stairs_dc,dressing_dc,bowel_dc,bladder_dc,score_dc,rankin_scale,rankin_scale_dc
            ,update_user,version,update_datetime)
            VALUES(:an,:chronic_check,:type_check,:feeding,:tranfer,:grooming,:toilet_use,:bathing,:mobility
            ,:stairs,:dressing,:bowel,:bladder,:score
            ,:feeding_dc,:tranfer_dc,:grooming_dc,:toilet_use_dc,:bathing_dc,:mobility_dc
            ,:stairs_dc,:dressing_dc,:bowel_dc,:bladder_dc,:score_dc,:rankin_scale,:rankin_scale_dc
            ,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('an'=>$an,'chronic_check'=>$chronic_check,'type_check'=>$type_check
            ,'feeding'=>$feeding,'tranfer'=>$tranfer,'grooming'=>$grooming,'toilet_use'=>$toilet_use,'bathing'=>$bathing,'mobility'=>$mobility
            ,'stairs'=>$stairs,'dressing'=>$dressing,'bowel'=>$bowel,'bladder'=>$bladder,'score'=>$score
            ,'feeding_dc'=>$feeding_dc,'tranfer_dc'=>$tranfer_dc,'grooming_dc'=>$grooming_dc,'toilet_use_dc'=>$toilet_use_dc,'bathing_dc'=>$bathing_dc,'mobility_dc'=>$mobility_dc
            ,'stairs_dc'=>$stairs_dc,'dressing_dc'=>$dressing_dc,'bowel_dc'=>$bowel_dc,'bladder_dc'=>$bladder_dc,'score_dc'=>$score_dc
            ,'rankin_scale'=>$rankin_scale,'rankin_scale_dc'=>$rankin_scale_dc
            ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime));


            $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");
        </script>';




        }

    }catch (PDOException  $e) {
        echo $e->getMessage();
        $output_error = '<div class="alert alert-danger">ERROR !!</div>';
    }
    echo $output_error;
?>
