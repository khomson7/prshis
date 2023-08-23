<?php  // require_once './project/function/Session.php';
       // Session::checkPermissionAndShowMessage('IPD_DISCHARGE_SUMMARY','VIEW');
       require_once '../include/Session.php';
//ตรวจสอบว่า session login ตรงกันหรือไม่
       $login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
        $loginname = $_SESSION['loginname'];
        $values =['loginname'=>$loginname];

        //หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
        if($login != $loginname){
            session_start();
            session_destroy();
            
				
          }
          

     
//ส่วนหัวหน้า
        require_once '../mains/main-report.php';
//check session and permission  
//Session::checkLoginSessionAndShowMessage(); //เช็ค session      
Session::checkPermissionAndShowMessage('IPD_DISCHARGE_SUMMARY','VIEW');


      
       
        require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
        require_once '../mains/ipd-show-patient-sticky.php';
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = $_REQUEST['an'];//รับค่า an
        $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        //----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่
        

          
          
        $sql = "SELECT count(*) AS count_row, summary_id FROM ".DbConstant::KPHIS_DBNAME.".ipd_summary WHERE an = :an ";
        $summary_id  = null;
        $parameters['an'] = $an;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();
        if($row['count_row'] > 0){
            $summary_id = $row['summary_id'];
        }
        //----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

        date_default_timezone_set('asia/bangkok');

       
?>

<form id="ipd_summary_form">
    <div class="container-fluid">
        <div class="row">
            <div class="col-auto">
                <button type="button" class="btn btn-danger btn-block" onclick="self.close()"><i class="fa fa-window-close"></i> ปิด</button> 
            </div>
            <div class="col-auto p-1 font-weight-bold">
                IN-PATIENT-SUMMARY
            </div>
        </div>
        <link rel="stylesheet" href="../include/css/accordion.css">
        <hr>
        <div class="row mb-2">
            <label class="col-auto text-right font-weight-bold">Discharge วันที่</label>
            <div class="col-auto">
                <input type="date" class="form-control form-control-sm CheckPer_1" id="summary_plan_date" name="summary_plan_date" value="">
            </div>
            <label class="col-auto text-right font-weight-bold">เวลา</label>
            <div class="col-auto">
                <input type="time" class="form-control form-control-sm CheckPer_1" id="summary_plan_time" name="summary_plan_time" value="">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12">
                <div class="row">
                    <label class="col-sm-12 font-weight-bold">(1) PRINCIPAL DIAGNOSIS บันทึกได้เพียงโรคเดียวเท่านั้น</label>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_1" name="principal_diagnosis" id="principal_diagnosis" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <label class="font-weight-bold">(2) PRE ADMISSION COMORBIDITY (S)</label>
                    </div>
                </div>
                <div id="kids">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <textarea class="form-control CheckPer_1" name="pre_admission_comorbidity" id="pre_admission_comorbidity" rows="5"
                            placeholder="รูปแบบ
1). xxxxxxxxxxxxxxxxxx
2). xxxxxxxxxxxxxxxxxx
3). xxxxxxxxxxxxxxxxxx"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12">
                <div class="row mb-2">
                    <div class="col-md-12">
                        <label class="font-weight-bold">(3) COMPLICATION (S) (POST ADMISSION COMORBIDITY)</label>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_1" name="post_admission_comorbidity" id="post_admission_comorbidity" rows="5"
                        placeholder="รูปแบบ
1). xxxxxxxxxxxxxxxxxx
2). xxxxxxxxxxxxxxxxxx
3). xxxxxxxxxxxxxxxxxx"></textarea>
                    </div>
                </div>
            </div>
        </div>
  


        <div class="row mb-2">
            <div class="col-md-12">
                <div class="card-group">
                    <div class="card">
                        <div class="card-body">
                        <div class="row">
                    <label class="col-sm-12 font-weight-bold">(4) OTHER DIAGNOSIS</label>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_1" name="other_diagnosis" id="other_diagnosis" rows="2"></textarea>
                    </div>
                </div>
              
                <div class="row">
                    <label class="col-sm-12 font-weight-bold">(5) EXTERNAL CAUSE (S) OF INJURY</label>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_2" name="external_cause" id="external_cause" rows="2"></textarea>
                    </div>
                </div>

                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                          
                        <div class="row">
                    <label class="col-sm-12 font-weight-bold">(6) Additional Code</label>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_1" name="additional_code" id="additional_code" rows="2"></textarea>
                    </div>
                </div>
              
                <div class="row">
                    <label class="col-sm-12 font-weight-bold">(7) Morphology Code</label>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_2" name="morphology_code" id="morphology_code" rows="2"></textarea>
                    </div>
                </div>
                            
                            
                            
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <hr>
        <div class="row mb-2">
            <div class="col-md-12">
                <label class="font-weight-bold">OPERATING ROOM PROCEDURES</label>
                <button type="button" class="btn btn-info btn-sm CheckPer_1" onclick="import_DataOR_Hosxp('<?=$an?>')"><i class="fas fa-file-import"></i> นำเข้าข้อมูลการผ่าตัดจาก HosXP</button>
            </div>
            <div class="col">
                <label class="small ml-3">OPERATING ROOM PROCEDURES [ICD-9]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (DATE TIME IN) - (DATE TIME OUT)</label>
            </div>
            <div class="col-md-12">
                <textarea class="form-control CheckPer_1" name="operating_room" id="operating_room" rows="4"></textarea>
            </div>
        </div><hr>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="row">
                    <label class="col-sm-12 font-weight-bold">NON OPERATING ROOM PROCEDURES</label>
                </div>
                <div class="row">
                    <div class="custom-control custom-checkbox col-sm-3 offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="tracheostomy" id="tracheostomy"  value="Y">
                        <label class="custom-control-label" for="tracheostomy">TRACHEOSTOMY</label>
                    </div>
                </div>
                <div class="row">
                    <div class="custom-control custom-checkbox col-sm-3 offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="mechanical_ventilation" id="mechanical_ventilation" value="Y">
                        <label class="custom-control-label" for="mechanical_ventilation">MECHANICAL VENTILATION</label>
                    </div>
                    <div class="custom-control custom-checkbox col-sm-2">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="mechanical_ventilation1" id="mechanical_ventilation1" value="Y">
                        <label class="custom-control-label" for="mechanical_ventilation1">มากกว่า 96 ชม.</label>
                    </div>
                    <div class="custom-control custom-checkbox col-sm-3">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="mechanical_ventilation2" id="mechanical_ventilation2" value="Y">
                        <label class="custom-control-label" for="mechanical_ventilation2">น้อยกว่า 96 ชม.</label>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="custom-control custom-checkbox col-sm-3 offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="packed_redcells"  id="packed_redcells" value="Y">
                        <label class="custom-control-label" for="packed_redcells">PACKED RED CELLS</label>
                    </div>
                    <div class="custom-control custom-checkbox col-sm-2">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="fresh_frozen_plasma" id="fresh_frozen_plasma" value="Y">
                        <label class="custom-control-label" for="fresh_frozen_plasma">FRESH FROZEN PLASMA</label>
                    </div>
                    <div class="custom-control custom-checkbox col-sm-2">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="platelets" id="platelets" value="Y">
                        <label class="custom-control-label" for="platelets">PLATELETS</label>
                    </div>
                    <div class="custom-control custom-checkbox col-sm-2">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="cryoprecipitate" id="cryoprecipitate" value="Y">
                        <label class="custom-control-label" for="cryoprecipitate">CRYOPRECIPITATE</label>
                    </div>
                    <div class="custom-control custom-checkbox col-sm-2">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="whole_blood" id="whole_blood" value="Y">
                        <label class="custom-control-label" for="whole_blood">Whole Blood</label>
                    </div>
                </div>
                <div class="row">
                    <div class="custom-control custom-checkbox col-sm-2 offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" for="computer_tomography">Computer Tomography</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm CheckPer_2" name="computer_tomography_text" id="computer_tomography_text">
                    </div>
                </div>
                <div class="row">
                    <div class="custom-control custom-checkbox col-sm-3 offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="chemotherapy" id="chemotherapy" value="Y">
                        <label class="custom-control-label" for="chemotherapy">CHEMOTHERAPY</label>
                    </div>
                </div>
                <div class="row">
                    <div class="custom-control custom-checkbox col-sm-3 offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="mri" id="mri" value="Y">
                        <label class="custom-control-label" for="mri">MRI</label>
                    </div>
                </div>
                <div class="row">
                    <div class="custom-control custom-checkbox col-sm-3 offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="hemodialysis" id="hemodialysis" value="Y">
                        <label class="custom-control-label" for="hemodialysis">Hemodialysis</label>
                    </div>
                </div>
                <div class="row">
                    <div class="custom-control custom-checkbox col-sm-2 offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="non_or_other" id="non_or_other" value="Y">
                        <label class="custom-control-label" for="non_or_other">อื่นๆ</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm CheckPer_2" name="non_or_other_text" id="non_or_other_text">
                    </div>
                </div>
            </div>
        </div>


        <div class="row mb-2">
            <div class="col-md-12">
                <div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <label class="col-sm-12 font-weight-bold">DISCHARGE STATUS</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_status" id="discharge_status01" value="01">
                                <label class="custom-control-label" for="discharge_status01">COMPLETE RECOVERED</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_status" id="discharge_status02" value="02">
                                <label class="custom-control-label" for="discharge_status02">IMPROVED</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_status" id="discharge_status03" value="03">
                                <label class="custom-control-label" for="discharge_status03">NOT IMPROVED</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_status" id="discharge_status04" value="04">
                                <label class="custom-control-label" for="discharge_status04">DELIVERED</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_status" id="discharge_status05" value="05">
                                <label class="custom-control-label" for="discharge_status05">UNDELIVERED</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_status" id="discharge_status06" value="06">
                                <label class="custom-control-label" for="discharge_status06">NORMAL CHILD DISCHARGE WITH MOTHER</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_status" id="discharge_status07" value="07">
                                <label class="custom-control-label" for="discharge_status07">NORMAL CHILD DISCHARGE SEPARATELY</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_status" id="discharge_status09" value="09">
                                <label class="custom-control-label" for="discharge_status09">DEAD</label>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <label class="col-sm-12 font-weight-bold">DISCHARGE TYPE</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_type" id="discharge_type01" value="01">
                                <label class="custom-control-label" for="discharge_type01">WITH APPROVAL</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_type" id="discharge_type02" value="02">
                                <label class="custom-control-label" for="discharge_type02">AGAINST ADVICE</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_type" id="discharge_type03" value="03">
                                <label class="custom-control-label" for="discharge_type03">ESCAPE</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_type" id="discharge_type04" value="04">
                                <label class="custom-control-label" for="discharge_type04">BY TRANSFER</label>
                            </div>
                            <div class="row">
                                <label class="col-sm-4 offset-md-1">ชื่อสถานพยาบาลที่ส่งต่อ</label>
                                <div class="col-md-7">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" id="hospital_refer" name="hospital_refer"  placeholder="ไม่เกิน 50 ตัวอักษร">
                                </div>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_type" id="discharge_type05" value="05">
                                <label class="custom-control-label" for="discharge_type05">OTHER</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_type" id="discharge_type08" value="08">
                                <label class="custom-control-label" for="discharge_type08">DEAD, AUTOPSY</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-11 offset-md-1  mb-2">
                                <input type="radio" class="custom-control-input CheckPer_2" name="discharge_type" id="discharge_type09" value="09">
                                <label class="custom-control-label" for="discharge_type09">DEAD, NO AUTOPSY</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="accordion">

        <div class="accordion-item">
            <div class="accordion-header"> <label class="col-sm-12 font-weight-bold">CERTIFICATION OF CAUSE OF DEATH</label></div>
            <div class="accordion-content">

            <!-- Content for Section begin -->
            <div class="card">
                        <div class="card-body">
            <div class="row">
                    <label class="col-sm-12">CAUSE OF DEATH</label>
    </div>

                <div class="row">
                        <label>(a)</label>
                <div class="col-md-8">
                       <input type="text" class="form-control form-control-sm CheckPer_2" name="cause_of_death_a" id="cause_of_death_a">
                    </div>
                    </div>

                    <br>

                    <div class="row">
                        <label>(b)</label>
                <div class="col-md-8">
                       <input type="text" class="form-control form-control-sm CheckPer_2" name="cause_of_death_b" id="cause_of_death_b">
                    </div>
                    </div>
<br>
                    <div class="row">
                        <label>(c)</label>
                <div class="col-md-8">
                       <input type="text" class="form-control form-control-sm CheckPer_2" name="cause_of_death_c" id="cause_of_death_c">
                    </div>
                    </div>
                    <br>
                    <label class="col-sm-12">Interval between onset and death</label>
                    <div class="row">
                <div class="col-md-10">
                       <input type="text" class="form-control form-control-sm CheckPer_2" name="onset_and_death" id="onset_and_death">
               </div>
    </div>
    </div>

                <!-- Content for Section end -->
            </div>
        </div>
    </div>

        <div class="accordion-item">
            <div class="accordion-header"> <label class="col-sm-12 font-weight-bold">CERTIFICATION OF CAUSE OF PERINATAL DEATH</label></div>
            <div class="accordion-content">
                <!-- Content for Section begin -->

                <label style ="font-size:20px;">สำหรับทารกตายคลอด (STILLBIRTHS) และทารกที่ตายในระยะ 1 สัปดาห์หลังคลอด</label>
                

                <div class="d-flex">

                <div class="p-1 flex">

                <label class="col-sm-12" style ="font-size:20px;"><B>Identifying particulars</B></label>

      
    
    </div>
    </div>


    <div class="row">

    <div class="custom-control custom-checkbox  offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">this child was born live on </label>
                    </div>
                                <div class="col-sm-1"></div>
                                
                                <div class="col-auto">
                                <input type="date" class="form-control form-control-sm CheckPer_1" id="was_born_live_date" name="was_born_live_date" value="">
                                </div>
                                <label>at</label>
                                <div class="col-auto">
                                    <input type="number" class="form-control form-control-sm" id="was_born_live_hours" name="was_born_live_hours" min = "1">
                                </div>
                                <label>hours</label>

                                

    </div>
    <br>

    <div class="row">

    <div class="offset-md-1">
  
    
                    </div>
                    
                                <div class="col-sm-1"></div>
                                <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;and died on</label>
                                <div class="col-auto">
                                <input type="date" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>
                                <label>at</label>
                                <div class="col-auto">
                                    <input type="number" class="form-control form-control-sm" id="labor_time" name="labor_time" min = "1">
                                </div>
                                <label>hours</label>                    

    </div>
    <br>
    <div class="row">
    <div class="custom-control custom-checkbox  offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">this child was stilborn on</label>
                    </div>
                                <div class="col-sm-1"></div>
                                
                                <div class="col-auto">
                                <input type="date" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>
                                <label>at</label>
                                <div class="col-auto">
                                    <input type="number" class="form-control form-control-sm" id="labor_time" name="labor_time" min = "1">
                                </div>
                                <label>hours</label>

                                

    </div>

    <br>
    <div class="row">
    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">and died before labour</label>
                    </div>
                    <div class="custom-control custom-checkbox  offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">during labour</label>
                    </div>
                    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">not know</label>
                    </div>
                                
                                

    </div>

                <!-- Content for Section end -->

            </div>
        </div>



        <div class="accordion-item">
            <div class="accordion-header"> <label class="col-sm-12 font-weight-bold">Mother</label></div>
            <div class="accordion-content">

            <!-- Content for Section begin -->
            <div class="card">
                        <div class="card-body">
                            

                       <div class = "row">     
                       <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;and died on</label>
                       <div class="col-auto">
                                <input type="date" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>

                                <div class="row">
                                <label class="col-sm-12">or, if unknow, age(year)</label>
                            </div>
                            <div class="col-auto">
                                    <input type="number" class="form-control form-control-sm" id="labor_time" name="labor_time" min = "1">
                                    <div style="color:red !important;">*only number</div>
                                </div>
                           
                                
                           </div>

                           <div class="row">
                           <div class="row">
                                <label class="col-sm-12">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1st day of last menstrual period </label>
                            </div>

                            <div class="col-auto">
                                <input type="date" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>
                           <label class="col-sm-3">or,if unknow, estimated duration of pregnacy</label>
                           <div class="col-sm-1">
                           <input type="number" class="form-control form-control-sm" id="labor_time" name="labor_time" min = "1">
                                    <div style="color:red !important;">*only number</div>
    </div>
                            </div>

                            <div class="row">

                            <div class="col-sm-3">
                                <label class="col-sm-12">number of previous pregnancies :</label>
                            </div> 
                            

                            <div class="col-sm-6">
                            <label class="col-sm-12">Antenatal care, two or more visits:</label>
                            <div class="row">
    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Yes</label>
                    </div>
                    <div class="custom-control custom-checkbox  offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">No</label>
                    </div>
                    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">not know</label>
                    </div>
                                
                                

    </div>
                            </div>  


                            </div> 
                            
       <div class ="row">    
       <div>
     <label class="col-sm-12">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Live Births </label>
      </div>
       <div class="col-sm-1">
                           <input type="number" class="form-control form-control-sm" id="labor_time" name="labor_time" min = "1">
                                    <div style="color:red !important;">*only number</div>
                            </div> 
        
    </div>

    <div class ="row">    
       <div>
     <label class="col-sm-12">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Stillbirths </label>
      </div>
       <div class="col-sm-1">
                           <input type="number" class="form-control form-control-sm" id="labor_time" name="labor_time" min = "1">
                                    <div style="color:red !important;">*only number</div>
                            </div> 
        
    </div>

    <div class ="row">    
       <div>
     <label class="col-sm-12">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Abortions </label>
      </div>
       <div class="col-sm-1">
                           <input type="number" class="form-control form-control-sm" id="labor_time" name="labor_time" min = "1">
                                    <div style="color:red !important;">*only number</div>
                            </div> 
        
    </div>
<br>
    <div class="row">

    <div class="col-sm-6">
                            <label class="col-sm-12">Outcome of last previous pregnancy</label>
                            <div class="row">
    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Live Births</label>
                    </div>
                    <div class="custom-control custom-checkbox  offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Stillbirths</label>
                    </div>
                    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Abortions</label>
                    </div>

                    

    </div>
         <br>
    <div class="col-sm-3">
                                <input type="date" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>

    </div>


    <div class="col-sm-6">
                            <label class="col-sm-12">Delivery:</label>
                            <div class="row">
    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Normal spontaneous vertex</label>
                    </div>
                    <div class="custom-control custom-checkbox  offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Other (specify)</label>
                    </div>
                   
    </div>
         <br>
    <div class="col-sm-6">
                                <input type="text" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>

    </div>
    </div>
    </div>

                <!-- Content for Section end -->
            </div>
        </div>
    </div>

     <div class="accordion-item">
            <div class="accordion-header"> <label class="col-sm-12 font-weight-bold">Child</label></div>
            <div class="accordion-content">

            <!-- Content for Section begin -->
            <div class="card">
                        <div class="card-body">
                            
                        <div class = "row"> 
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Birthweight</label>
                        <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>

                                <label class="text-left">&nbsp;grams</label>
                                <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp; Sex:&nbsp;&nbsp;&nbsp;</label>
                                <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Boy&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Girl&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Indeterminate</label>
                    </div>
    </div>

<br>
    <div class="row">
    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Single birth</label>
                    </div>
                    <div class="custom-control custom-checkbox  offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">First twin</label>
                    </div>
                    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Second twin</label>
                    </div>
                    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Other multiple</label>
                    </div>

    </div>
<br>
    <label class="text-left">&nbsp;&nbsp;Attendant at birth</label>
    <div class="row">
    

    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Single birth</label>
                    </div>
                    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">พยาบาลและผดุงครรภ์</label>
                    </div>
                    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">อื่นๆ โปรดระบุ</label>
                    </div>

                    <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>
    </div>
    </div>   
                <!-- Content for Section end -->
            </div>
        </div>

    </div>

        <div class="accordion-item">
            <div class="accordion-header"> <label class="col-sm-12 font-weight-bold">Causes of death</label></div>
            <div class="accordion-content">
            <div class="card">
                        <div class="card-body">
                            
                        <div class = "row"> 
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Main disease or condition in fetus or infant</label>
                        </div>
                         <div class = "row">
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(โรคหรือภาวะหลักเพียงภาวะเดียวของลูกในท้องหรือทารก)</label>
                        </div>
                        <div class="col-sm-6">
                                <input type="text" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>

                                <div class = "row"> 
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b.Other diseases or conditions in fetus or infant</label>
                        </div>
                         <div class = "row">
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(โรคหรือภาวะอื่นๆ ของลูกในท้องหรือทารก)</label>
                        </div>
                        <div class="col-sm-6">
                                <input type="text" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>

                                <div class = "row"> 
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c. Main maternal diseases or condition aftecting fetus or infant</label>
                        </div>
                         <div class = "row">
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(โรคหรือภาวะหลักเพียงภาวะเดียวของมารดาที่มีผลต่อลูกในท้องหรือทารก)</label>
                        </div>
                        <div class="col-sm-6">
                                <input type="text" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>

                                <div class = "row"> 
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d. Other meternal disease or coditions affecting fetus or infant</label>
                        </div>
                         <div class = "row">
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(โรคภาวะอื่นๆ ของมารดาที่มีผลต่อลูกในท้องหรือทารก)</label>
                        </div>
                        <div class="col-sm-6">
                                <input type="text" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>

                                <div class = "row"> 
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;e. Other relevant circumtances</label>
                        </div>
                         <div class = "row">
                        <label class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(โรคภาวะอื่นๆ ของมารดาที่มีผลต่อลูกในท้องหรือทารก)</label>
                        </div>
                        <div class="col-sm-6">
                                <input type="text" class="form-control form-control-sm CheckPer_1" id="" name="" value="">
                                </div>
                            
                                <hr>

                                <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">The certified cause of death has been confirmed by autopsy</label>
                    </div>
                    <div class="custom-control custom-checkbox  offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Autopsy information may be available laters</label>
                    </div>
                    <div class="custom-control custom-checkbox offset-md-1">
                        <input type="checkbox" class="custom-control-input CheckPer_2" name="computer_tomography" id="computer_tomography" value="Y">
                        <label class="custom-control-label" style ="font-size:15px;" for="computer_tomography">Autopsy not being held</label>
                    </div>
                    

    
    </div>


    </div>
    </div>
    </div>
        

        <!-- Add more sections as needed -->
    </div>
              
    

    <br>


    <div class="form-row ">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="action-person-dr-admission">&nbsp;&nbsp;&nbsp;&nbsp;ลงนามแพทย์ผู้รักษา</label>
                    <button type="button"  class="btn btn-secondary btn-sm mb-2t" onclick="AddDoctorSignature()"><i class="fas fa-plus"></i> ลงชื่อ</button>
                    <div id="dr-admission-group-input-div">
                    <template id="template_dr_admission_input_div">
                        <div class="dr_admission_input_div">
                            <div class="input-group mb-2">
                                <input type="hidden" class="form-control form-control" name="admission_note_doctor[]">
                                <input type="text" class="form-control form-control" name="doc_name[]" readonly>
                                <!-- <input type="text" class="form-control form-control" name="doc_pos[]" readonly> -->
                            </div>
                        </div>
                    </template>
                        <?php $start_count = 0; while ($start_count < $admission_note_count) {?>
                        <div class="dr_admission_input_div">
                            <div class="input-group mb-2">
                                <input type="hidden" class="form-control form-control" name="admission_note_doctor[]" value="<?=$admission_note_doctor[$start_count]?>">
                                <input type="text" class="form-control form-control" name="doc_name[]" value="<?=$admission_note_doctorname[$start_count]?>" readonly>
                                <!-- <input type="text" class="form-control form-control" name="doc_pos[]"  value="<?=$admission_note_doctorentryposition[$start_count]?>" readonly> -->
                            </div>
                        </div>
                        <?php $start_count++;} ?>
                    </div>
                </div>
            </div>

            </div>    
        

           
    
        <!-- <div class="row">
            <label class="col-sm-1 text-right font-weight-bold">ATTENDING</label>
            <div class="col-sm-3">
                <button type="button" class="btn btn-primary" onclick="onclick_Attending_doctor()">แพทย์ลงชื่อ</button>
            </div>
        </div> -->
        <div class="row">
            <input type="hidden" id="summary_an" name="summary_an" value="<?=$an?>"><!-- ฟิลด์ hidden  "an"  -->
            <input type="hidden" id="summary_id" name="summary_id" value="<?=$summary_id?>"><!-- ฟิลด์ hidden "summary_id"  -->
            <input type="hidden" id="summary_version" name="summary_version"><!-- ฟิลด์ hidden "version"  -->

            <div class="col-md-9">
                <div id="data_summary_save"></div><!-- แสดงข้อความการบันมึก >> บันทึกข้อมูลสำเร็จ, EORROR -->
                <div id="data_summary_edit"></div>
                <div id="data_summary_update"></div>
            </div>

         

            <div class="col-md-12 text-right">
                <?php
                if(Session::checkPermission('IPD_DISCHARGE_SUMMARY','VIEW')){?>
                    <button type="button" class="btn btn-primary" id="btn_summary" onclick="summary_save()"><i class="fas fa-save"></i> บันทึก</button>
                <?php } ?>
                <a href="ipd-summary-pdf.php?an=<?php echo $an;?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
            </div>
        </div><br>
    </div>
</form>
<script>
    $( document ).ready(function() {
        var summary_id =  <?=json_encode($summary_id)?>;
        if(summary_id != null && summary_id != ""){
            summary_edit(<?=json_encode($summary_id)?>,<?=json_encode($an)?>);
        }else{
            import_DataOR_Hosxp(<?=json_encode($an)?>);
        }
        //summary_CheckPer();
    });

    // function summary_CheckPer(){
    //     const IPD_SUMMARY_LEVEL1 = json_encode(SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY', 'LEVEL1'));
    //     const IPD_SUMMARY_LEVEL2 = json_encode(SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY', 'LEVEL2'));
    //     if(IPD_SUMMARY_LEVEL1){
    //         $(".CheckPer_1").attr('disabled', 'disabled');
    //     }
    // }

    // function onclick_Attending_doctor(){
    //     if (confirm("คุณต้องการลงชื่อ ATTENDING ใช่หรือไม่")) {
    //         const session_loginname = json_encode(KphisQueryUtils::getShowDataDoctor($_SESSION['loginname']));
    //         var array = $.map(session_loginname[0], function(value, index){
    //             return [value];
    //         });
    //         if(array[0] == "" || array[0] == null){
    //             alert("คุณไม่สามารถลงชื่อได้");
    //         }else{
    //             alert(array[1]);
    //         }
    //     }
    // }

    function summary_save(){
        var summary_plan_date = $("#summary_plan_date").val();
        var summary_plan_time = $("#summary_plan_time").val();
        var was_born_live_date = $("#was_born_live_date").val();
       // var summary_plan_time = $("#summary_plan_time").val();
        //var principal_diagnosis = $("#principal_diagnosis").val();
        var summary_id = $("#summary_id").val();

        var url_save = 'ipd-summary-save.php';
        var url_update = 'ipd-summary-update.php';

        if(summary_plan_date == ""){
            alert("กรอกวันที่");
            $("#summary_plan_date").focus();
        }else if (summary_plan_time == ""){
            alert("กรอกเวลา");
            $("#summary_plan_time").focus();
        }else{
            $("#btn_summary").attr('disabled', 'disabled');
            if(summary_id == ""){
                $.post(url_save,$("#ipd_summary_form").serialize(),function(data_save){
                    $("#data_summary_save").html(data_save);
                   // window.location.reload(true);
                   alert("บันทึกข้อมูลสำเร็จ");
                    self.close();
                })
                .fail(function(){
                    alert("บันทึกข้อมูลไม่สำเร็จ");
                    $("#btn_summary").removeAttr("disabled");
                });
            }else{
                $.post(url_update,$("#ipd_summary_form").serialize(),function(data_update){
                    $("#data_summary_update").html(data_update);
                   // window.location.reload(true);
                   alert("ปรับปรุงข้อมูลสำเร็จ");
                    self.close();
                })
                .fail(function(){
                    alert("บันทึกข้อมูลไม่สำเร็จ");
                    $("#btn_summary").removeAttr("disabled");
                });
            }
        }
    }

    function summary_edit(summary_id,an){
        var url="ipd-summary-edit.php";
        $.post(url,{summary_id,an},function(data_edit){
            $("#data_summary_edit").html(data_edit);
            //console.log(data_edit);
        });
    }

    function import_DataOR_Hosxp(an){
        url_DtaOR = "ipd-summary-import-DataOR-Hosxp.php";
        $.post(url_DtaOR,{an},function(DataOR){
            $("#operating_room").val(DataOR);
        });
    }
</script>

<script src="../include/js/accordion.js"></script>

