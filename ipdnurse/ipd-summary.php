<?php   
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
  Session::checkLoginSessionAndShowMessage(); //เช็ค session    
  Session::checkPermissionAndShowMessage('IPD_DISCHARGE_SUMMARY','VIEW');
  
  
        
         
          require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
          require_once '../mains/ipd-show-patient-sticky.php';
          require_once '../include/DbUtils.php';
          require_once '../include/KphisQueryUtils.php';
  
          Session::insertSystemAccessLog(json_encode(array(
              'form'=>'IPD-SUMMARY',
              'an'=>$an,
          ),JSON_UNESCAPED_UNICODE));
  
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
                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-auto p-1 font-weight-bold">
                IN-PATIENT-SUMMARY
            </div>
        </div><hr>
        <div class="row mb-2">
            <label class="col-auto text-right font-weight-bold">Discharge วันที่</label>
            <div class="col-auto">
                <input type="date" class="form-control form-control-sm CheckPer_1" id="summary_plan_date" name="summary_plan_date" value="<?php echo date('Y-m-d');?>">
            </div>
            <label class="col-auto text-right font-weight-bold">เวลา</label>
            <div class="col-auto">
                <input type="time" class="form-control form-control-sm CheckPer_1" id="summary_plan_time" name="summary_plan_time" value="<?php echo date('H:i');?>">
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
                <div class="row">
                    <label class="col-sm-12 font-weight-bold">(4) OTHER DIAGNOSIS</label>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_1" name="other_diagnosis" id="other_diagnosis" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12">
                <div class="row">
                    <label class="col-sm-12 font-weight-bold">(5) EXTERNAL CAUSE (S) OF INJURY</label>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_2" name="external_cause" id="external_cause" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div><hr>
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
                                    <input type="text" class="form-control form-control-sm CheckPer_2" id="hospital_refer" name="hospital_refer">
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
                if((Session::checkPermission('IPD_DISCHARGE_SUMMARY','ADD') && ($summary_id == null)) || (Session::checkPermission('IPD_DISCHARGE_SUMMARY','EDIT')&& ($summary_id != null))){?>
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
                    window.location.reload(true);
                })
                .fail(function(){
                    alert("บันทึกข้อมูลไม่สำเร็จ");
                    $("#btn_summary").removeAttr("disabled");
                });
            }else{
                $.post(url_update,$("#ipd_summary_form").serialize(),function(data_update){
                    $("#data_summary_update").html(data_update);
                    window.location.reload(true);
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