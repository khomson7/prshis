<?php
include("connect_db.php");
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
 where i.an="'.$_GET['an'].'"';
$query = mysqli_query($conn,$sql);
$result = mysqli_fetch_assoc($query);
