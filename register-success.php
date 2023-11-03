<?php
 require_once './include/Session.php';
 Session::checkLoginSessionAndShowMessage();
require_once './header.php';
?>
<br />

<div class=" h-100 d-flex justify-content-center align-items-center">

<div class="alert alert-success" role="alert">
<h2>ลงทะเบียนสำเร็จ !</h2>
</div>

</div>

<br>
<div class=" h-100 d-flex justify-content-center align-items-center">
	
<a href= "http://192.168.3.2/nutrition" target="_blank"><div style="font-size:23px;">คลิก เข้าสู่ระบบประเมินภาวะโภชนาการ</div></a><br />
<br>
  
</div>


<?php
$dataHTML=<<<BOF
<div class=" h-100 d-flex justify-content-center align-items-center">
<div class="iTopicQUE_R">
<!--<a href= "http://192.168.3.2/nutrition" target="_blank"><h2>เข้าสู่ระบบประเมินภาวะโภชนาการ</h2></a><br />
<a href= "https://www.prasathsp.com" target="_blank">เว็บโรงพยาบาลปราสาท</a><br /> -->

</div>
</div>
BOF;
//echo "<strong>ตัวอย่าง ส่วนของเนื้อหาที่ยังไม่ได้จัดรูปแบบ ของ ลิ้งค์</strong><br/>";
echo $dataHTML;
echo "<hr>";
//echo "<strong>ตัวอย่าง ส่วนของเนื้อหาที่ จัดรูปแบบ ของ ลิ้งค์ แล้ว</strong><br/>";
function adjustLink($matches){
    $linkMody="https://www.prasathsp.com/?"; // รูปแบบลิ้งค์ที่นำไปปรับเพิ่ม
    $siteDomain="www.prasathsp.com"; // domain เว็บที่ไม่ต้องกำหนดรูปแบบ ลิ้งค์ใหม่
    $matchesData=strtolower($matches[0]);
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($matchesData."</a>"); 
    libxml_use_internal_errors(false);
    $xpath = new DOMXPath($dom);    
    $LinkTag = $xpath->query('//a[@href]');  
    foreach ($LinkTag as $val) {        
        $matchesData=trim($val->getAttribute('href'));
    }   
    if(preg_match("/^(mailto:)|^(#)|^(\?)/",$matchesData)){
        return "<a href=\"$matchesData\">";
    }
    if(!preg_match("@^(https?://)@",$matchesData)){
        if(preg_match("/$siteDomain/i",$matchesData)){
            return "<a href=\"http://".$matchesData."\">";
        }else{
            return "<a href=\"".$linkMody."http://".$matchesData."\">";
        }       
    }else{
        if(preg_match("/$siteDomain/i",$matchesData)){
            return "<a href=\"$matchesData\">";
        }else{
            return "<a href=\"".$linkMody.$matchesData."\">";
        }
         
    }
}
?>

