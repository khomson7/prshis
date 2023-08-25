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
        

          
          
        $sql = "SELECT count(*) AS count_row, summary_id,create_user FROM ".DbConstant::KPHIS_DBNAME.".ipd_summary_ordit WHERE an = :an ";
        $summary_id  = null;
        $create_  = null;
        $parameters['an'] = $an;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();
        if($row['count_row'] > 0){
            $summary_id = $row['summary_id'];
            $create_ = $row['create_user'];
        }
        //----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

        $sql_ = "SELECT  summary_id,summary_plan_date,summary_plan_time,create_user,principal_diagnosis,pre_admission_comorbidity 
        ,post_admission_comorbidity,other_diagnosis,external_cause,additional_code,morphology_code,operating_room
        FROM ".DbConstant::KPHIS_DBNAME.".ipd_summary WHERE an = :an ";
$stmt_ = $conn->prepare($sql_);
$stmt_->execute($parameters);
while ($row_ = $stmt_->fetch()){



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
            <label><?=htmlspecialchars($row_['summary_plan_date']);?></label>
            </div>
            <label class="col-auto text-right font-weight-bold">เวลา</label>
            <div class="col-auto">
            <label><?=htmlspecialchars($row_['summary_plan_time']);?></label>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12">
                <div class="row">
                    <label class="col-sm-12 font-weight-bold">(1) PRINCIPAL DIAGNOSIS บันทึกได้เพียงโรคเดียวเท่านั้น</label>
                </div>
                <div class="patient-info-container alert alert-secondary" role="alert">
                <div class="p-1 flex">
                <label><?=htmlspecialchars($row_['principal_diagnosis']);?></label>
                </div>
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
                
                <div class="p-1 flex">
                
                <textarea readonly class="form-control"  rows="2" ><?=(isset($row_['pre_admission_comorbidity']) ? htmlspecialchars($row_['pre_admission_comorbidity']): htmlspecialchars($row_['pre_admission_comorbidity']))?></textarea>
                

                </div>

                <div id="kids">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <textarea class="form-control CheckPer_1" name="pre_admission_comorbidity" id="pre_admission_comorbidity" rows="3"
                            placeholder="รูปแบบ
1). xxxxxxxxxxxxxxxxxx
2). xxxxxxxxxxxxxxxxxx"></textarea>
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

                <div class="p-1 flex">
                <textarea readonly class="form-control"  rows="2" ><?=(isset($row_['post_admission_comorbidity']) ? htmlspecialchars($row_['post_admission_comorbidity']): htmlspecialchars($row_['post_admission_comorbidity']))?></textarea>
                </div>

                <div class="row mb-2">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_1" name="post_admission_comorbidity" id="post_admission_comorbidity" rows="3"
                        placeholder="รูปแบบ
1). xxxxxxxxxxxxxxxxxx
2). xxxxxxxxxxxxxxxxxx"></textarea>
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
                    <textarea readonly class="form-control"  rows="2" ><?=(isset($row_['other_diagnosis']) ? htmlspecialchars($row_['other_diagnosis']): htmlspecialchars($row_['other_diagnosis']))?></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_1" name="other_diagnosis" id="other_diagnosis" rows="2"></textarea>
                    </div>
                </div>
              <br>
                <div class="row">
                    <label class="col-sm-12 font-weight-bold">(5) EXTERNAL CAUSE (S) OF INJURY</label>
                </div>
                <div class="row">
                    <div class="col-md-12">
                    <textarea readonly class="form-control"  rows="2" ><?=(isset($row_['external_cause']) ? htmlspecialchars($row_['external_cause']): htmlspecialchars($row_['external_cause']))?></textarea>
                    </div>
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
                    <textarea readonly class="form-control"  rows="2" ><?=(isset($row_['additional_code']) ? htmlspecialchars($row_['additional_code']): htmlspecialchars($row_['additional_code']))?></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control CheckPer_1" name="additional_code" id="additional_code" rows="2"></textarea>
                    </div>
                </div>
              <br>
                <div class="row">
                    <label class="col-sm-12 font-weight-bold">(7) Morphology Code</label>
                </div>
                <div class="row">
                    <div class="col-md-12">
                    <textarea readonly class="form-control"  rows="2" ><?=(isset($row_['morphology_code']) ? htmlspecialchars($row_['morphology_code']): htmlspecialchars($row_['morphology_code']))?></textarea>
                    </div>
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
                
            </div>
            <div class="col">
                <label class="small ml-3">OPERATING ROOM PROCEDURES [ICD-9]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (DATE TIME IN) - (DATE TIME OUT)</label>
            </div>
            <div class="col-md-12">    
                <textarea readonly class="form-control"  rows="3" ><?=(isset($row_['operating_room']) ? htmlspecialchars($row_['operating_room']): htmlspecialchars($row_['operating_room']))?></textarea>
                </div>
            <div class="col-md-12">
                <textarea class="form-control CheckPer_1" name="operating_room" id="operating_room" rows="4"></textarea>
            </div>
        </div><hr>
        
        

    <br>


   
           
    
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

            <?php } ?>  


            <div class="col-md-12 text-right">
                <?php
                if((Session::checkPermission('IPD_DISCHARGE_SUMMARY','VIEW') && !$summary_id) || $create_ == $loginname){?>

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

   /* function import_DataOR_Hosxp(an){
        url_DtaOR = "ipd-summary-import-DataOR-Hosxp.php";
        $.post(url_DtaOR,{an},function(DataOR){
            $("#operating_room").val(DataOR);
        });
    }
    */
</script>

<script src="../include/js/accordion.js"></script>

