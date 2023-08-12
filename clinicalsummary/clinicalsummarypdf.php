<?php
header('Content-Type: text/html; charset=UTF-8');
 require('setting/fpdf/fpdf.php');
 include('setting/connecttkp_db.php');
 mysqli_query($conn2, "SET character_set_results=utf8");
mysqli_query($conn2, "SET character_set_client=utf8");
mysqli_query($conn2, "SET character_set_connection=utf8");
mysqli_set_charset($conn2, "utf8");
function datethainotime($strDate)
{
  $strYear = date("Y", strtotime($strDate)) + 543;
  $strMonth = date("n", strtotime($strDate));
  $strDay = date("j", strtotime($strDate));
  $strHour = date("H", strtotime($strDate));
  $strMinute = date("i", strtotime($strDate));
  $strSeconds = date("s", strtotime($strDate));
  $strMonthCut = array("", "เดือนมกราคม", "เดือนกุมภาพันธ์", "เดือนมีนาคม", "เดือนเมษายน", "เดือนพฤษภาคม", "เดือนมิถุนายน", "เดือนกรกฎาคม", "เดือนสิงหาคม", "เดือนกันยายน", "เดือนตุลาคม", "เดือนพฤศจิกายน", "เดือนธันวาคม");
  $strMonthThai = $strMonthCut[$strMonth];
  return "$strDay $strMonthThai $strYear";
}
     $sql = "select * from clinicalsummary where an ='".$_REQUEST['an']."'";
    $query=mysqli_query($conn2,$sql);
    $result2=mysqli_fetch_assoc($query);
    class PDF extends FPDF{
        function Header()
        {
            $this->SetFont('THSarabun','',16);
            $this->Cell(0,5,iconv('UTF-8','TIS-620','FM-NUR-159  R:0'),0,1,"R");
            $this->SetFont('THSarabun','',18);
            $this->Cell(0,10,iconv('UTF-8','TIS-620','แบบสรุปการจำหน่าย (CLINICAL SUMMARRY)'),"B",1,"C");
            $this->Rect(10, 25, 190, 250, 'S');
        } 
        function Footer(){
            $this->SetY(-44);
            $conn = new mysqli("192.168.5.1", "dbhosx", "Ic2rTc47p68H30","hos1");
            mysqli_query($conn, "SET character_set_results=utf8");
            mysqli_query($conn, "SET character_set_client=utf8");
            mysqli_query($conn, "SET character_set_connection=utf8");
            mysqli_set_charset($conn, "utf8");
            $sql = ' select ip.dchtime,ip.regtime,"Medicine" as admitdepart,i.*,pcn.cardno as CID_CODE,
pt.pname,pt.fname,pt.lname,pt.addrpart," ËÁÙè ",pt.moopart," µ.",t3.name as name2," Í.",t2.name as name3," ¨.",t1.name as name4,
sx.name ,mr.name as re,oc.name as dep,rm.roomno, 
 pt.birthday as DOB, pt.informname as INFORMNAME,"<",pt.informrelation,">",pt.informaddr as INFORMADDR,
pty.name as PTTYPENAME,wd.name as WARD_NAME,sp.name as SPCLTY_NAME ,pt.informtel,ov.*,hc.hosptype," ",hc.name,
hc2.hosptype," ",hc2.name ,ip.dchstts ,ip.dchdate ,ds.name as dchstts_name ,i.admdate ,ip.dchtype , dt.name as dchtype_name,
ip.drg ,ip.rw ,ip.wtlos ,ip.ot,dd1.name as "incharge_doctor",dd1.licenseno,  ip.gravidity,ip.parity,ip.living_children ,
ip.bw,nt.name as NT_NAME,YEAR(current_date)-YEAR(pt.birthday) as fullage ,v.pttype_begin,v.pttype_expire,pt.birthday ,dd.name as doctorname,
i.pdx,icd.name as namepdx ,v.age_y," »Õ ",v.age_m," à´×Í¹ ",v.age_d," ÇÑ¹" 

 from an_stat i   
 left outer join ptcardno pcn on pcn.hn=i.hn and pcn.cardtype="01"     
 left outer join patient pt on pt.hn=i.hn      
 left outer join sex sx on sx.code=pt.sex  
 left outer join roomno rm on rm.an=i.an     
 left outer join ovst ov on ov.vn=i.vn       
 left outer join icd101 icd on icd.code=i.pdx
 left outer join hospcode hc on hc.hospcode = ov.hospmain   
 left outer join hospcode hc2 on hc2.hospcode = ov.hospsub     
 left outer join nationality nt on pt.nationality=nt.nationality     
 left outer join marrystatus mr on mr.code=pt.marrystatus         
 left outer join occupation oc on oc.occupation=pt.occupation   
 left outer join pttype pty on pty.pttype=i.pttype       
 left outer join ward wd on wd.ward=i.ward             
 left outer join spclty sp on sp.spclty=i.spclty            
 left outer join thaiaddress t1 on t1.codetype="1" and t1.chwpart=pt.chwpart  
 left outer join thaiaddress t2 on t2.codetype="2" and t2.chwpart=pt.chwpart and t2.amppart=pt.amppart
 left outer join thaiaddress t3 on t3.codetype="3" and t3.chwpart=pt.chwpart and t3.amppart=pt.amppart and t3.tmbpart=pt.tmbpart      
 left outer join ipt ip on ip.an=i.an   
 left outer join vn_stat v on v.vn=ip.vn 
 left outer join dchstts ds on ds.dchstts=ip.dchstts  
 left outer join dchtype dt on dt.dchtype=ip.dchtype   
 left outer join doctor dd on dd.code=ip.dch_doctor  
 left outer join doctor dd1 on dd1.code=ip.incharge_doctor  
 where i.an="'.$_REQUEST['an'].'"';
            $query=mysqli_query($conn,$sql);
            $result=mysqli_fetch_array($query);
            $conn2 = new mysqli("192.168.5.1", "dbhosx", "Ic2rTc47p68H30","kphis");
            mysqli_query($conn2, "SET character_set_results=utf8");
            mysqli_query($conn2, "SET character_set_client=utf8");
            mysqli_query($conn2, "SET character_set_connection=utf8");
            mysqli_set_charset($conn2, "utf8");
            $sql2 = "select * from clinicalsummary where an ='".$_REQUEST['an']."'";
            $query2=mysqli_query($conn2,$sql2);
            $result2=mysqli_fetch_array($query2);
            $this->Cell(190,8,iconv('UTF-8','TIS-620','(                                             )                    '),0,1,"R");
            $this->Cell(190,8,iconv('UTF-8','TIS-620',''.$result2["doctorname"].'            แพทย์ผู้สรุป'),"LR",1,"R");
            $this->Cell(0,6,iconv('UTF-8','TIS-620',''),"LBR",1,"L");
            $this->Cell(75,8,iconv('UTF-8','TIS-620','Name : '.$result["pname"] . '' . $result["fname"] . ' ' . $result["lname"].''),"LTBR",0,"L");
            $this->Cell(60,8,iconv('UTF-8','TIS-620','Age : '.$result["age_y"].' ปี '.$result["age_m"].' เดือน '.$result["age_d"].' วัน'.''),"LTBR",0,"L");
            $this->Cell(0,8,iconv('UTF-8','TIS-620','HN : '.$_REQUEST['hn'].''),"LTBR",1,"L");
            $this->Cell(75,8,iconv('UTF-8','TIS-620','Department of service : '.$result['SPCLTY_NAME'].''),"LTBR",0,"L");
            $this->Cell(60,8,iconv('UTF-8','TIS-620','Ward : '.$result['WARD_NAME'].''),"LTBR",0,"L");
            $this->Cell(0,8,iconv('UTF-8','TIS-620','AN : '.$_REQUEST['an'].''),"LTBR",1,"L");
        }
    }
    $pdf = new PDF();
    define('FPDF_FONTPATH','font/');
    $pdf->SetTitle('Clinical Summary');
    $pdf->AddFont('THSarabun','','THSarabun.php');
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true,50);
    $pdf->SetMargins(10,10,10);
    $pdf->SetFont('THSarabun','',18);
    $pdf->Cell(0,8,iconv('UTF-8','TIS-620','วัน เดือน ปี ที่จำหน่าย : '.datethainotime($result2['c_date']).''),"LBR",1,"L");
    $pdf->SetFont('THSarabun','',14);
    $pdf->Cell(190,8,iconv('UTF-8','TIS-620',''),0,1,"R");
    $pdf->MultiCell(0,5,iconv('UTF-8','TIS-620','  '.$result2['c_input1']),0,1);
    $pdf->Output();
?>