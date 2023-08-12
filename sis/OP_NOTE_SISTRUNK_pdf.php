<?php
 require('setting/fpdf/fpdf.php');
 include('setting/query.php');
 /* var_dump($result); */
 include('setting/connecttkp_db.php');
 mysqli_query($conn, "SET character_set_results=utf8");
mysqli_query($conn, "SET character_set_client=utf8");
mysqli_query($conn, "SET character_set_connection=utf8");
header('Content-Type: text/html; charset=UTF-8');
 mysqli_set_charset($conn, "utf8");
  if(isset($_REQUEST['id'])){
     $sql = 'select * from sis where an like "%'.$result["an"].'%" and surgery_No = "'.$_REQUEST['id'].'"';
     $query = mysqli_query($conn2,$sql);
     $result2 = mysqli_fetch_assoc($query);

 }
 else{
     $sql = 'select * from sis where an like "%'.$result["an"].'%" order by ttnd_date DESC';
     $query = mysqli_query($conn2,$sql);
     $result2 = mysqli_fetch_assoc($query);
 }
 $pdf = new FPDF();
    define('FPDF_FONTPATH','font/');
    $pdf->SetTitle('Sistrunk');
    $pdf->AddFont('THSarabun','','THSarabun.php');
    $pdf->AddPage();
    $pdf->SetFont('THSarabun','',20);
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','บันทึกการผ่าตัด'),0,1,"C"); 
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620',''),0,1,"C"); 
    $pdf->SetFont('THSarabun','',16);
    $pdf->Cell(90,5,iconv('UTF-8','TIS-620','Date of operation : '.$result2['sis_doo'].''),"LT",0,"L"); 
    $pdf->Cell(60,5,iconv('UTF-8','TIS-620','Time Started : '.$result2['sis_ts'].''),"T",0,"L"); 
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','Time Ended : '.$result2['sis_te'].''),"TR",1,"L");
    $pdf->Cell(90,5,iconv('UTF-8','TIS-620','Surgeon : '.$result2['sis_sur'].''),"L",0,"L"); 
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','First assistant : '.$result2['sis_fa'].''),"R",1,"L");
    $pdf->Cell(90,5,iconv('UTF-8','TIS-620','Second diagnosis : '.$result2['sis_sa'].''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','Surgical nurse : '.$result2['sis_sn'].''),"R",1,"");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(70,5,iconv('UTF-8','TIS-620','Clinical diagnosis : '.$result2['sis_cd'].''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','Thyroglossal duct cyst : '.$result2['sis_tdc'].''),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(70,5,iconv('UTF-8','TIS-620','Post-operative diagnosis : '.$result2['sis_pod'].''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','Thyroglossal duct cyst : '.$result2['sis_tdc2'].''),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(70,5,iconv('UTF-8','TIS-620','Operation : '.$result2['sis_operation'].''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620',"Sistrunk's operation : ".$result2['sis_so'].""),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(90,3,iconv('UTF-8','TIS-620','Anesthesia : '.$result2['sis_anesthesia'].''),"L",0,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620','Anesthesist : '.$result2['sis_anesthesist'].''),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->SetFont('THSarabun','',16);
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','DESCRIPTION OF OPERATIVE'),"RL",1,"C");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->SetFont('THSarabun','',16);
    $pdf->Cell(30,5,iconv('UTF-8','TIS-620','Position'),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','Supine with neck extended'),'R',1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(29,5,iconv('UTF-8','TIS-620','Incision'),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','Horizontal incision just below the level of the hyoid bone '),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);


    $pdf->Cell(25,5,iconv('UTF-8','TIS-620','Findings'),"L",0,"L");
    $pdf->MultiCell(0,5,iconv('UTF-8','TIS-620',$result2['sis_findings']),"R",1);
    $pdf->SetX(150);
    $pdf->SetY(90);
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620',''),"LR",1,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620',''),"LR",1,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620',''),"LR",1,"L");
    $pdf->SetX(150);
    $pdf->SetY(100);
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620',''),"LR",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620',''),"LR",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(25,5,iconv('UTF-8','TIS-620','Procedure'),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','-Incision was done.'),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(25,5,iconv('UTF-8','TIS-620',''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','-The skin flap in the plane of the superficial layer of the deep cervical fascia was elevated.'),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(25,5,iconv('UTF-8','TIS-620',''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','The infrahyoid strap muscle was divided at their insertion into the body of the hyoid.'),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(25,5,iconv('UTF-8','TIS-620',''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','-The body of the hyoid bone was diviede at ppproximately the lesser cornu bilaterally.'),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    
    $pdf->Cell(25,5,iconv('UTF-8','TIS-620',''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','The thyroglossal duct cyst was dissected, and the tract was followed up to the foramen'),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

  
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620','cecum and then suture ligation was done.'),"LR",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(25,5,iconv('UTF-8','TIS-620',''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','-Bleeding was stopped.'),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    if(isset($result2['sis_pd'])){
        if($result2['sis_pd']==''){
            $pdf->Image('setting/pic/checkbank.jpg',40,171,4);
        }
        else{
            $pdf->Image('setting/pic/checkbox.jpg',40,171,4);
        }
    }
    if(isset($result2['sis_rdwp'])){
        if($result2['sis_rdwp']==''){
            $pdf->Image('setting/pic/checkbank.jpg',73,171,4);
        }
        else{
            $pdf->Image('setting/pic/checkbox.jpg',73,171,4);
        }
    }
    $pdf->Cell(25,5,iconv('UTF-8','TIS-620',''),"L",0,"L");
    $pdf->Cell(43,5,iconv('UTF-8','TIS-620','-       penrose drain/'),0,0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','Radivac drain was placed.'),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(25,5,iconv('UTF-8','TIS-620',''),"L",0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','-Incision was close layer by layer.'),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(25,3,iconv('UTF-8','TIS-620',''),"L",0,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620','-EBL =     '.$result2['sis_ebl'].'     cc.'),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);

    $pdf->Cell(0,3,iconv('UTF-8','TIS-620','Special note : '.$result2['sis_special'].''),"LR",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    if(isset($result2['sis_yes'])){
        if($result2['sis_yes']==''){
            $pdf->Image('setting/pic/checkbank.jpg',70,253,4);
        }
        else{
            $pdf->Image('setting/pic/checkbox.jpg',70,253,4);
        }
    }
    if(isset($result2['sis_no'])){
        if($result2['sis_no']==''){
            $pdf->Image('setting/pic/checkbank.jpg',90,253,4);
        }
        else{
            $pdf->Image('setting/pic/checkbox.jpg',90,253,4);
        }
    }
    $pdf->Cell(65,5,iconv('UTF-8','TIS-620','PATHOLGIST REQUEST EXAM'),"L",0,"L");
    $pdf->Cell(20,5,iconv('UTF-8','TIS-620','YES'),0,0,"L");
    $pdf->Cell(20,5,iconv('UTF-8','TIS-620','NO'),0,0,"L");
    $pdf->Cell(0,5,iconv('UTF-8','TIS-620','Surgeon : '.$result2['sis_surr'].'     '.$result['licenseno']),"R",1,"L");
    $pdf->Cell(0,3,iconv('UTF-8','TIS-620',''),"LR",1,1);
    $pdf->SetFont('THSarabun','',16);
    $pdf->Cell(80,8,iconv('UTF-8','TIS-620','Name of patient : '.$result["pname"].''.$result["fname"].' '.$result["lname"].''),1,0,"L");
    $pdf->Cell(43,8,iconv('UTF-8','TIS-620','Age : '.$result["age_y"].'ปี '.$result["age_m"].'เดือน '.$result["age_d"].'วัน'),1,0,"L");
    $pdf->Cell(67,8,iconv('UTF-8','TIS-620','Hospital Number : '.$result["hcode"].''),1,1,"L");
    $pdf->Cell(80,8,iconv('UTF-8','TIS-620','Department of Service : '.$result['SPCLTY_NAME'].''),1,0,"L");
    $pdf->Cell(43,8,iconv('UTF-8','TIS-620','Ward : '.$result["WARD_NAME"].''),1,0,"L");
    $pdf->Cell(67,8,iconv('UTF-8','TIS-620','AN : '.$result['an'].''),1,1,"L");
    $pdf->Output();
    
?>