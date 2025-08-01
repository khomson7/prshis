<head>
    <title>KPHIS</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

</head>

<?php

require_once './include/Session.php';
//Session::checkLoginSessionAndShowMessage(); //เช็ค session
// if(!(
//     SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
//     )){
//     return;
// }

Session::checkLoginSessionAndShowMessage(); //เช็ค session
Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE', 'VIEW');
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once './header.php';
require_once './include/DbUtils.php';
require_once './include/KphisQueryUtils.php';
require_once './include/ReportQueryUtils.php';
require_once './include/ExternalDocumentTracker.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
//
//require_once 'main.php';
//$an = $_REQUEST['an'];//รับค่า an
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$doctor0 = empty($_SESSION['doctorcode']) ? null : $_SESSION['doctorcode'];
$hn = KphisQueryUtils::getHnByAn($an); // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$vn = KphisQueryUtils::getVnByAn($an);
$an_parameters = ['an' => $an];
$doctor = ['doctor' => $doctor0];
// รวม array
$params = array_merge($an_parameters, $doctor);
$getDocumentSummary = KphisQueryUtils::getDocumentSummary($an);

//echo $_SESSION['doctorcode'];

// $an2 = "'".$an."'";

//$getDocumentNihssScore = ReportQueryUtils::getDocumentPrsER($vn);

//echo $getDocumentNihssScore;

$loginname = $_SESSION['loginname'];

// $getsess = json_encode(KphisQueryUtils::getDocumentSummary($an));

// echo $getsess;

// use Endroid\QrCode\QrCode;

// Data to be encoded in the QR code
//$data = $an; // Replace this with your data

//$data = $an;

// Create a QR code instance
//$qrCode = new QrCode($data);

// Save the QR code as a file (optional)
//$qrCode->writeFile('./include/images/an_qr/'.$data.'.png');

$sql = "SELECT *
                FROM `prs_signature`
                WHERE an = :an and doctor = :doctor";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
if ($row  = $stmt->fetch()) {
    $admission_note_id = $row['id'];
} else {
    $admission_note_id = null;
}



$sql_item = "SELECT dr_adm_item.id,
                    dr_adm_item.doctor,
                    concat(doctor.name,' ',ifnull(licenseno,'')) AS admission_note_doctorname
                    FROM " . DbConstant::KPHIS_DBNAME . ".prs_signature dr_adm_item
                    LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor ON doctor.code = dr_adm_item.doctor
                    WHERE an=:an
                    ORDER BY dr_adm_item.id ASC";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute($an_parameters);
$pre_note_count = 0;
while ($row_item = $stmt_item->fetch()) {
    $id_pre_note[] = $row_item['id'];
    $doctor[] = $row_item['doctor'];
    $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
    //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
    $pre_note_count++;
}

//nurse_signatur
$sql_item2 = "SELECT dr_adm_item.id,
                    dr_adm_item.doctor,
                    concat(doctor.name,' ',ifnull(licenseno,'')) AS admission_nurse_doctorname
                    FROM " . DbConstant::KPHIS_DBNAME . ".prs_nurse_signature dr_adm_item
                    LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor ON doctor.code = dr_adm_item.doctor
                    WHERE an=:an
                    ORDER BY dr_adm_item.id ASC";
$stmt_item = $conn->prepare($sql_item2);
$stmt_item->execute($an_parameters);
$pre_nurse_count = 0;
while ($row_item = $stmt_item->fetch()) {
    $id_pre_note[] = $row_item['id'];
    $nurse[] = $row_item['doctor'];
    $admission_nurse_doctorname[] = $row_item['admission_nurse_doctorname'];
    $pre_nurse_count++;
}

//print_r($admission_note_id);

?>



<div class="container-fluid"><br>

    <form id="my_form">
        <div class="form-group text-center">
            <div id="show_check_save"></div>
            <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
            <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
            <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
            <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">

        </div>

        <div class="form-row">
            <div class="col-md-4">
                <div class="form-group text-right">
                    <label for="action-person-dr-admission">พยาบาล Audit</label>
                    <button type="button" class="btn btn-secondary btn-sm mb-2" onclick="AddNurseSignature()"><i class="fas fa-plus"></i> ลงชื่อ</button>
                    <div id="nurse-admission-group-input-div">
                        <template id="template_nurse_admission_input_div">
                            <div class="nurse_admission_input_div">
                                <div class="input-group mb-2">
                                    <input type="hidden" class="form-control form-control" name="nurse[]">
                                    <input type="text" class="form-control form-control" name="nurse_name[]" readonly>

                                </div>
                            </div>



                            <?php
                            if (/*(
        Session::checkPermission('PRS_PRE_NURSENOTE','ADD')
    ) && */(ReportQueryUtils::checkReadOnly($an))) { ?>
                                <button type="button" class="btn btn-primary" id="btn_save_report" onclick="form_nurse_save()"><i class="fas fa-save"></i> บันทึก</button>
                            <?php } ?>

                        </template>
                        <?php $start_count = 0;
                        while ($start_count < $pre_nurse_count) { ?>
                            <div class="nurse_admission_input_div">
                                <div class="input-group mb-2">
                                    <input type="hidden" class="form-control form-control" name="nurse[]" value="<?= $nurse[$start_count] ?>">
                                    <input type="text" class="form-control form-control" name="nurse_name[]" value="<?= $admission_nurse_doctorname[$start_count] ?>" readonly>

                                </div>
                            </div>
                        <?php $start_count++;
                        } ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group text-right">
                    <label for="action-person-dr-admission">แพทย์ Audit</label>
                    <button type="button" class="btn btn-secondary btn-sm mb-2" onclick="AddDoctorSignature()"><i class="fas fa-plus"></i> ลงชื่อ</button>
                    <div id="dr-admission-group-input-div">
                        <template id="template_dr_admission_input_div">
                            <div class="dr_admission_input_div">
                                <div class="input-group mb-2">
                                    <input type="hidden" class="form-control form-control" name="doctor[]">
                                    <input type="text" class="form-control form-control" name="doc_name[]" readonly>

                                </div>
                            </div>
                            <?php
                            if ((
                                Session::checkPermission('PRS_PRE_NURSENOTE','ADD')
    ) ) { ?>
                                <button type="button" class="btn btn-primary" id="btn_save_report" onclick="form_save()"><i class="fas fa-save"></i> บันทึก</button>
                            <?php } ?>


                        </template>
                        <?php $start_count = 0;
                        while ($start_count < $pre_note_count) { ?>
                            <div class="dr_admission_input_div">
                                <div class="input-group mb-2">
                                    <input type="hidden" class="form-control form-control" name="doctor[]" value="<?= $doctor[$start_count] ?>">
                                    <input type="text" class="form-control form-control" name="doc_name[]" value="<?= $admission_note_doctorname[$start_count] ?>" readonly>

                                </div>
                            </div>
                        <?php $start_count++;
                        } ?>
                    </div>
                </div>
            </div>





        </div>

        <script>
            function form_save() {

                var url_update = "signature-update2.php";
                var url_save = "signature-save.php";
                var id = $("#id").val();
                var my_form = $("#my_form").serialize();

                if (id == "") {
                    $.post(url_save, my_form, function(data) {
                            $("#show_check_save").html(data);
                            //console.log(doctorcode_check)
                            // alert("บันทึกข้อมูลสำเร็จ");
                            // self.close();
                            window.location.reload(true);
                        })
                        .fail(function() {
                            alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                        });
                } else {
                    $.post(url_update, my_form, function(data) {
                            $("#show_check_save").html(data);


                        })
                        .fail(function() {
                            alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                            //NotificationMessage('บันทึกข้อมูลไม่สำเร็จ', 'danger');
                        });
                }

            }

            function form_nurse_save() {

                var url_update = "signature-update.php";
                var url_save = "signature-nurse-save.php";
                var id = $("#id").val();
                var my_form = $("#my_form").serialize();




                if (id == "") {
                    $.post(url_save, my_form, function(data) {
                            $("#show_check_save").html(data);
                            //console.log(doctorcode_check)
                            // alert("บันทึกข้อมูลสำเร็จ");
                            // self.close();
                            window.location.reload(true);
                        })
                        .fail(function() {
                            alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                        });
                } else {
                    $.post(url_update, my_form, function(data) {
                            $("#show_check_save").html(data);


                        })
                        .fail(function() {
                            alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                            //NotificationMessage('บันทึกข้อมูลไม่สำเร็จ', 'danger');
                        });
                }


            }
        </script>


        <hr>

        <div class="row">
           
            <div class="col-sm-7">
                <h4>
                    <p class="text-right"><B><i class="fas fa-file-alt"></i> เอกสารที่อยู่ในระบบคอมพิวเตอร์</B></p>
                </h4>
            </div>



            <div class="col-md-2 text-right">
                <a href="ipd-document-main-pdf.php?an=<?= $an ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-print"></i> พิมพ์เอกสารใบปะหน้า</a>
            </div>

            <div class="col-md-2 text-right">
                <a href="allpdfprint/all_pdf.php?an=<?= $an ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-print"></i> พิมพ์เอกสารรวม</a>
            </div>


        </div>

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <ul class="list-group">


                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_ER"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label><b>เอกสารแรกรับ(ER/OPD)</b></label>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_NihssScore"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>HOSPITAL NIHSS SCORE SHEET</label>
                                <span id="show_text_NihssScore_prhis"></span>
                                <a id="NihssScore_pdf"></a>
                            </div>
                        </div>
                    </li>

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_AdmEr"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>แบบบันทึกประวัติและตรวจร่างกายผู้ป่วยแรกรับ(ER Form)</label>
                                <span id="show_text_AdmEr_prhis"></span>
                                <a id="AdmEr_pdf"></a>
                            </div>
                        </div>
                    </li>



                    <hr>


                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_1"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Discharge Summary</label>
                                <span id="show_text_DischargeSummary_kphis"></span>
                                <a id="DischargeSummary_pdf"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div class="text-secondary fas fa-square"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Referring Letter Sheet</label>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div class="text-secondary fas fa-square"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Informed Consent</label>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_AddmissionDoctor"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>แบบประเมินแรกรับใหม่ผู้ป่วยใน</label>
                                <span id="show_text_AddmissionDoctor_kphis"></span>
                                <a id="AddmissionDoctor_pdf"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_AddmissionNurse"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>ใบการประเมินสภาพผู้ป่วยแรกรับและแผนสุขภาพ</label>
                                <span id="show_text_AddmissionNurse_kphis"></span>
                                <a id="AddmissionNurse_pdf"></a>
                                <a id="AddmissionNurse_pdf1"></a>
                                <a id="AddmissionNurse_pdf2"></a>
                                <a id="AddmissionNurse_pdf3"></a>
                                <a id="AddmissionNurse_pdf_icu"></a>

                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_Order_ProgressNote"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Progress Note, Order</label>
                                <span id="show_text_Order_ProgressNote_kphis"></span>
                                <a id="Order_ProgressNote_pdf"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_Med_Reconciliation"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Med Reconciliation</label>
                                <span id="show_text_MedReconciliation_kphis"></span>
                                <span id="show_MedReconciliationHOSXP_hosxp"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_Consult"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Consulation Report</label>
                                <span id="show_text_Consult_kphis"></span>
                                <a id="Consult_pdf"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_Anesthetic"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Anesthetic Record</label>
                                <span id="show_text_Scan_kphis"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_Operative"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Operative Report</label>
                                <span id="show_text_Operative_hosxp"></span>
                                <span id="show_text_Operative_scan"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div class="text-secondary fas fa-square"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Labour Record</label>
                            </div>
                        </div>
                    </li>

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_PathologyLabXray"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Pathology Report/ Laboratory Report/ X-rays Report</label>
                            </div>
                        </div>
                    </li>

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_Lab"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>Laboratory Report</label> <span id="show_text_Lab_hosxp"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_Xray"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>X-rays Report</label> <span id="show_text_Xray_hosxp"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_CTscan"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>CT scan</label> <span id="show_text_CTscan_hosxp"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_MRI"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>MRI</label> <span id="show_text_MRI_hosxp"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div class="text-secondary fas fa-circle"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>Blood transfusion Report(ใบของห้องเลือด)</label>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right text-secondary">
                                <h5><i class="fas fa-circle"></i></h5>
                            </div>
                            <div class="col-md-10">
                                <label>Electrocardiogram Report</label>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div class="text-secondary fas fa-circle"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>Other Special Clinical Report</label>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div class="text-secondary fas fa-square"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Physiotherapy Sheet (กายภาพบำบัด)</label>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_NursingSection"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Nursing Section</label>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_FocusList"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>Focus List</label>
                                <span id="show_text_FocusList_kphis"></span>
                                <a id="FocusList_pdf"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_FocusNote"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>Nurses' Notes</label>
                                <span id="show_text_FocusNote_kphis"></span>
                                <a id="FocusNote_pdf"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_VitalSign"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>Graphic Record</label>
                                <span id="show_text_VitalSign_kphis"></span>
                                <a id="VitalSign_pdf"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_IO"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>Fluid Balance Summary</label>
                                <span id="show_text_IO_kphis"></span>
                                <a id="IO_pdf"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div id="check_countRowData_Index"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>Index (Nurse Planning)</label>
                                <span id="show_text_Index_kphis"></span>
                                <a id="Index_pdf"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-2 text-right">
                                <h5>
                                    <div class="text-secondary fas fa-circle"></div>
                                </h5>
                            </div>
                            <div class="col-md-10">
                                <label>บันทึกอื่นๆ ที่เกี่ยวกับพยาบาล</label>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div class="text-secondary fas fa-square"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Medication Administration Records </label>
                            </div>
                        </div>
                    </li>

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_NursingSection0"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>Nursing Section</label>
                            </div>
                        </div>
                    </li>


                 


                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_Other"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>เอกสารอื่นๆ</label>
                                <span id="show_text_Other_prhis"></span>
                                <a id="mentalHealth1_pdf"></a>
                                <a id="mentalHealth2_pdf"></a>
                                <a id="mentalHealth3_pdf"></a>

                            </div>
                        </div>
                    </li>


                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1 text-right">
                                <h5>
                                    <div id="check_countRowData_ER"></div>
                                </h5>
                            </div>
                            <div class="col-md-11">
                                <label>เอกสาร ER </label> <span id="show_text_ER_kphis"></span>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>
        </div><br>
</div>





<script>
    check_document_countRowData();

    function check_document_countRowData() {

        let an = <?= json_encode($an) ?>;
        const IPD_DOCUMENT_PRINT = <?= json_encode(Session::checkPermission('IPD_DOCUMENT', 'PRINT')) ?>;


        const getDocumentPrsER = <?= json_encode(ReportQueryUtils::getDocumentPrsER($vn)) ?>;

        if ((getDocumentPrsER)) {
            $("#check_countRowData_AdmEr").attr("class", "text-success fas fa-check-circle");
            $("#show_text_AdmEr_prhis").attr("class", "text-light font-weight-bold badge badge-primary").text(" PRHIS ");
            if (IPD_DOCUMENT_PRINT) {
                //  $("#AdmEr_pdf").attr({"class":"badge badge-secondary","href":"opddr/hospital-nihss-score-pdf.php?an="+an,"target":"_blank"}).html("<i class='fas fa-print'></i> PDF").css({"cursor":"pointer"});
            }
        } else {
            $("#check_countRowData_AdmEr").attr("class", "text-secondary fas fa-circle");
        }



        const getDocumentSummary = <?= json_encode(KphisQueryUtils::getDocumentSummary($an)) ?>;


        if ((getDocumentSummary == true)) {
            $('#check_1').attr("class", "text-success fas fa-check-square");
            $("#show_text_DischargeSummary_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            if (IPD_DOCUMENT_PRINT) {
                $("#DischargeSummary_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-summary-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });
            }
        } else {
            $("#check_1").attr("class", "text-secondary fas fa-square");
        }

        const getDocumentAddmissionDoctor = <?= json_encode(KphisQueryUtils::getDocumentAddmissionDoctor($an)) ?>;
        const getAddmissionDoctor1 = <?= json_encode(KphisQueryUtils::getDocumentAddmissionDoctor1($an)) ?>;
        const getAddmissionDoctor2 = <?= json_encode(KphisQueryUtils::getDocumentAddmissionDoctor2($an)) ?>;

        if ((getDocumentAddmissionDoctor == true)) {
            $("#check_countRowData_AddmissionDoctor").attr("class", "text-success fas fa-check-square");
            $("#show_text_AddmissionDoctor_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            if (IPD_DOCUMENT_PRINT && getAddmissionDoctor1 == true) {
                $("#AddmissionDoctor_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-dr-newborn-admission-note-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });

            } else if (IPD_DOCUMENT_PRINT && getAddmissionDoctor2 == true) {
                $("#AddmissionDoctor_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-dr-admission-note-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });

            }
        } else {
            $("#check_countRowData_AddmissionDoctor").attr("class", "text-secondary fas fa-square");
        }

        const getDocumentAddmissionNurse = <?= json_encode(KphisQueryUtils::getDocumentAddmissionNurse($an)) ?>;

        const getDocumentAddmissionNurse0 = <?= json_encode(ReportQueryUtils::getDocumentAddmissionNurse0($an)) ?>;

        const getDocumentAddmissionNurse1 = <?= json_encode(ReportQueryUtils::getDocumentAddmissionNurse1($an)) ?>;

        const getDocumentAddmissionNurse2 = <?= json_encode(ReportQueryUtils::getDocumentAddmissionNurse2($an)) ?>;

        const getDocumentAddmissionNurse3 = <?= json_encode(ReportQueryUtils::getDocumentAddmissionNurse3($an)) ?>;

        const getDocumentAddmissionNurseIcu = <?= json_encode(ReportQueryUtils::getDocumentAddmissionNurseIcu($an)) ?>;

        //  console.log(getDocumentAddmissionNurse3);

        if ((getDocumentAddmissionNurse == true)) {
            $("#check_countRowData_AddmissionNurse").attr("class", "text-success fas fa-check-square");
            $("#show_text_AddmissionNurse_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");

            if (IPD_DOCUMENT_PRINT && getDocumentAddmissionNurse == true) {
                $("#check_countRowData_AddmissionNurse").attr("class", "text-success fas fa-check-square");
                $("#AddmissionNurse_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-nurse-admission-note-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF(1 ปี ขึ้นไป)").css({
                    "cursor": "pointer"
                });
            }

        } else {
            $("#check_countRowData_AddmissionNurse").attr("class", "text-secondary fas fa-square");
        }

        if ((getDocumentAddmissionNurse0 == true || getDocumentAddmissionNurse1 == true || getDocumentAddmissionNurse2 == true || getDocumentAddmissionNurse3 == true || getDocumentAddmissionNurseIcu == true)) {
            $("#check_countRowData_AddmissionNurse").attr("class", "text-success fas fa-check-square");
            $("#show_text_AddmissionNurse_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");

            if (IPD_DOCUMENT_PRINT && getDocumentAddmissionNurse1 == true) {
                $("#AddmissionNurse_pdf1").attr({
                    "class": "badge badge-secondary",
                    "href": "lr-report1/lr-report1-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF(แรกเกิด)").css({
                    "cursor": "pointer"
                });
            }
            if (IPD_DOCUMENT_PRINT && getDocumentAddmissionNurse2 == true) {
                $("#AddmissionNurse_pdf2").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse2/prs-pre-nursenote-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF(ทั่วไป)").css({
                    "cursor": "pointer"
                });
            }

            if (IPD_DOCUMENT_PRINT && getDocumentAddmissionNurse3 == true) {
                $("#AddmissionNurse_pdf3").attr({
                    "class": "badge badge-secondary",
                    "href": "lr-report1/lr-report2-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF(เฉพาะผู้มาคลอด)").css({
                    "cursor": "pointer"
                });
            }

            if (IPD_DOCUMENT_PRINT && getDocumentAddmissionNurseIcu == true) {
                $("#AddmissionNurse_pdf_icu").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse2/prs-icu1-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF(ICU)").css({
                    "cursor": "pointer"
                });
            }

        } else {
            $("#check_countRowData_AddmissionNurse").attr("class", "text-secondary fas fa-square");
        }

        const getDocumentOrder = <?= json_encode(KphisQueryUtils::getDocumentOrder($an)) ?>;
        const getDocumentOrderProgressNote = <?= json_encode(KphisQueryUtils::getDocumentOrderProgressNote($an)) ?>;
        if (((getDocumentOrder == true)) || ((getDocumentOrderProgressNote == true))) {
            $("#check_countRowData_Order_ProgressNote").attr("class", "text-success fas fa-check-square");
            $("#show_text_Order_ProgressNote_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            if (IPD_DOCUMENT_PRINT) {
                // $("#AddmissionNurse_pdf").attr({"class":"badge badge-secondary","href":"ipdnurse/ipd-nurse-admission-note-pdf.php?an="+an,"target":"_blank"}).html("<i class='fas fa-print'></i> PDF").css({"cursor":"pointer"});
                $("#Consult_pdf1").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-dr-consult-pdf.php?an_consult=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });
                // $("#Order_ProgressNote_pdf").attr({"class":"badge badge-secondary","onclick":"onclickOrder_ProgressNote_pdf()"}).html("<i class='fas fa-print'></i> PDF").css({"cursor":"pointer"}).tab('show');
            }
        } else {
            $("#check_countRowData_Order_ProgressNote").attr("class", "text-secondary fas fa-square");
        }

        const getDocumentConsult = <?= json_encode(KphisQueryUtils::getDocumentConsult($an)) ?>;

        if ((getDocumentConsult == true)) {
            $("#check_countRowData_Consult").attr("class", "text-success fas fa-check-square");
            $("#show_text_Consult_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            if (IPD_DOCUMENT_PRINT) {
                $("#Consult_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-dr-consult-pdf.php?an_consult=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });
            }
        } else {
            $("#check_countRowData_Consult").attr("class", "text-secondary fas fa-square");
        }


        const getDocumentLab = <?= json_encode(KphisQueryUtils::getDocumentLab($an)) ?>;
        if ((getDocumentLab)) {
            $("#check_countRowData_Lab").attr("class", "text-success fas fa-check-circle");
            $("#show_text_Lab_hosxp").attr("class", "text-light font-weight-bold badge badge-dark").text(" HOSxP ");
        } else {
            $("#check_countRowData_Lab").attr("class", "text-secondary fas fa-circle");
        }
        const getDocumentXray = <?= json_encode(KphisQueryUtils::getDocumentXray($an)) ?>;
        if ((getDocumentXray)) {
            $("#check_countRowData_Xray").attr("class", "text-success fas fa-check-circle");
            $("#show_text_Xray_hosxp").attr("class", "text-light font-weight-bold badge badge-dark").text(" HOSxP ");
        } else {
            $("#check_countRowData_Xray").attr("class", "text-secondary fas fa-circle");
        }
        const getDocumentCTscan = <?= json_encode(KphisQueryUtils::getDocumentCTscan($an)) ?>;
        if ((getDocumentCTscan)) {
            $("#check_countRowData_CTscan").attr("class", "text-success fas fa-check-circle");
            $("#show_text_CTscan_hosxp").attr("class", "text-light font-weight-bold badge badge-dark").text(" HOSxP ");
        } else {
            $("#check_countRowData_CTscan").attr("class", "text-secondary fas fa-circle");
        }
        const getDocumentMRI = <?= json_encode(KphisQueryUtils::getDocumentMRI($an)) ?>;
        if ((getDocumentMRI)) {
            $("#check_countRowData_MRI").attr("class", "text-success fas fa-check-circle");
            $("#show_text_MRI_hosxp").attr("class", "text-light font-weight-bold badge badge-dark").text(" HOSxP ");
        } else {
            $("#check_countRowData_MRI").attr("class", "text-secondary fas fa-circle");
        }
        if ((getDocumentLab) || (getDocumentXray) || (getDocumentCTscan) || (getDocumentMRI)) {
            $("#check_countRowData_PathologyLabXray").attr("class", "text-success fas fa-check-square");
        } else {
            $("#check_countRowData_PathologyLabXray").attr("class", "text-secondary fas fa-square");
        }


        const getDocumentFocusList = <?= json_encode(KphisQueryUtils::getDocumentFocusList($an)) ?>;
        if ((getDocumentFocusList)) {
            $("#check_countRowData_FocusList").attr("class", "text-success fas fa-check-circle");
            $("#show_text_FocusList_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            if (IPD_DOCUMENT_PRINT) {
                $("#FocusList_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-nurse-focus-list-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });
            }
        } else {
            $("#check_countRowData_FocusList").attr("class", "text-secondary fas fa-circle");
        }



        const getDocumentNihssScore = <?= json_encode(ReportQueryUtils::getDocumentNihssScore($an)) ?>;

        if ((getDocumentNihssScore)) {
            $("#check_countRowData_NihssScore").attr("class", "text-success fas fa-check-circle");
            $("#show_text_NihssScore_prhis").attr("class", "text-light font-weight-bold badge badge-primary").text(" PRHIS ");
            if (IPD_DOCUMENT_PRINT) {
                $("#NihssScore_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "opddr/hospital-nihss-score-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });
            }
        } else {
            $("#check_countRowData_NihssScore").attr("class", "text-secondary fas fa-circle");
        }


        const getDocumentFocusNote = <?= json_encode(KphisQueryUtils::getDocumentFocusNote($an)) ?>;
        if ((getDocumentFocusNote)) {
            $("#check_countRowData_FocusNote").attr("class", "text-success fas fa-check-circle");
            $("#show_text_FocusNote_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            if (IPD_DOCUMENT_PRINT) {
                $("#FocusNote_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-nurse-focus-note-pdf.php?an_fcnote_pdf=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });
            }
        } else {
            $("#check_countRowData_FocusNote").attr("class", "text-secondary fas fa-circle");
        }
        const getDocumentVitalSign = <?= json_encode(KphisQueryUtils::getDocumentVitalSign($an)) ?>;
        if ((getDocumentVitalSign)) {
            $("#check_countRowData_VitalSign").attr("class", "text-success fas fa-check-circle");
            $("#show_text_VitalSign_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            if (IPD_DOCUMENT_PRINT) {
                $("#VitalSign_pdf").attr({
                    "class": "badge badge-secondary",
                    "onclick": "onclick_VitalSign_pdf()"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                }).tab('show');
            }
        } else {
            $("#check_countRowData_VitalSign").attr("class", "text-secondary fas fa-circle");
        }
        const getDocumentIO = <?= json_encode(KphisQueryUtils::getDocumentIO($an)) ?>;
        if ((getDocumentIO)) {
            $("#check_countRowData_IO").attr("class", "text-success fas fa-check-circle");
            $("#show_text_IO_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            if (IPD_DOCUMENT_PRINT) {
                $("#IO_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-vital-sign-io-pdf.php?an_io_pdf=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });
            }
        } else {
            $("#check_countRowData_IO").attr("class", "text-secondary fas fa-circle");
        }
        const getDocumentIndex = <?= json_encode(KphisQueryUtils::getDocumentIndex($an)) ?>;
        if ((getDocumentIndex)) {
            $("#check_countRowData_Index").attr("class", "text-success fas fa-check-circle");
            $("#show_text_Index_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            if (IPD_DOCUMENT_PRINT) {
                $("#Index_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "ipdnurse/ipd-nurse-index-plan-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF").css({
                    "cursor": "pointer"
                });
            }
        } else {
            $("#check_countRowData_Index").attr("class", "text-secondary fas fa-circle");
        }
        if ((getDocumentFocusList) || (getDocumentFocusNote) || (getDocumentVitalSign) || (getDocumentIO)) {
            $("#check_countRowData_NursingSection").attr("class", "text-success fas fa-check-square");
        } else {
            $("#check_countRowData_NursingSection").attr("class", "text-secondary fas fa-square");
        }

        const getDocumentMedReconciliation = <?= json_encode(KphisQueryUtils::getDocumentMedReconciliation($an)) ?>;
        const getDocumentMedReconciliationHOSXP = <?= json_encode(KphisQueryUtils::getDocumentMedReconciliationHOSXP($an)) ?>;
        if ((getDocumentMedReconciliation) || (getDocumentMedReconciliationHOSXP)) {
            $("#check_countRowData_Med_Reconciliation").attr("class", "text-success fas fa-check-square");
            if (getDocumentMedReconciliation) {
                $("#show_text_MedReconciliation_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
            }
            if (getDocumentMedReconciliationHOSXP) {
                $("#show_MedReconciliationHOSXP_hosxp").attr("class", "text-light font-weight-bold badge badge-dark").text(" HOSxP ");
            }
        } else {
            $("#check_countRowData_Med_Reconciliation").attr("class", "text-secondary fas fa-square");
        }
        const getDocumentERFromOpdErMasterId = <?= json_encode(KphisQueryUtils::getDocumentERFromOpdErMasterId($vn)) ?>;
        if ((getDocumentERFromOpdErMasterId)) {
            $("#check_countRowData_ER").attr("class", "text-success fas fa-check-square");
            $("#show_text_ER_kphis").attr("class", "text-light font-weight-bold badge badge-primary").text(" KPHIS ");
        } else {
            $("#check_countRowData_ER").attr("class", "text-secondary fas fa-square");
        }

        const getDocumentMentalHealth1 = <?= json_encode(ReportQueryUtils::getDocumentMentalHealth1($an)) ?>;
        const getDocumentMentalHealth2 = <?= json_encode(ReportQueryUtils::getDocumentMentalHealth2($an)) ?>;
        const getDocumentMentalHealth3 = <?= json_encode(ReportQueryUtils::getDocumentMentalHealth3($an)) ?>;


        console.log(getDocumentMentalHealth2);



        if ((getDocumentMentalHealth1 == true || getDocumentMentalHealth2 == true || getDocumentMentalHealth3 == true || getDocumentAddmissionNurse2 == true || getDocumentAddmissionNurse3 == true || getDocumentAddmissionNurseIcu == true)) {
            $("#check_countRowData_Other").attr("class", "text-success fas fa-check-square");
            $("#show_text_Other_prhis").attr("class", "text-light font-weight-bold badge badge-primary").text(" PRHIS ");

            if (IPD_DOCUMENT_PRINT && getDocumentMentalHealth1 == true) {
                $("#mentalHealth1_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "pdffile/mental-health1-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF(แบบประเมินสุขภาพจิต)").css({
                    "cursor": "pointer"
                });
            }
            if (IPD_DOCUMENT_PRINT && getDocumentMentalHealth2 == true) {
                $("#mentalHealth2_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "pdffile/mental-health2-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF(BPRS)").css({
                    "cursor": "pointer"
                });
            }

            if (IPD_DOCUMENT_PRINT && getDocumentMentalHealth3 == true) {
                $("#mentalHealth3_pdf").attr({
                    "class": "badge badge-secondary",
                    "href": "pdffile/mental-health3-pdf.php?an=" + an,
                    "target": "_blank"
                }).html("<i class='fas fa-print'></i> PDF(SAVE)").css({
                    "cursor": "pointer"
                });
            }


        } else {
            $("#check_countRowData_Other").attr("class", "text-secondary fas fa-square");
        }




    }
</script>


<script>
    check_document_countRowData1();

    function check_document_countRowData1() {



        const get_document_neodms_anes = <?= json_encode(ExternalDocumentTracker::get_document_neodms_anes(null, $an)) ?>;
        if ((get_document_neodms_anes)) {
            $("#check_countRowData_Anesthetic").attr("class", "text-success fas fa-check-square");
            $("#show_text_Scan_kphis").attr("class", "text-dark font-weight-bold badge badge-info").text(" SCAN ");
        } else {
            $("#check_countRowData_Anesthetic").attr("class", "text-secondary fas fa-square");
        }

        /*    const get_document_neodms_anes = <?= json_encode(ExternalDocumentTracker::get_document_neodms_anes(null, $an)) ?>;
            if((get_document_neodms_anes)){
                $("#check_countRowData_Anesthetic").attr("class","text-success fas fa-check-square");
                $("#show_text_Scan_kphis").attr("class","text-dark font-weight-bold badge badge-info").text(" SCAN ");
            }else{
                $("#check_countRowData_Anesthetic").attr("class","text-secondary fas fa-square");
            }
           */
        const getDocumentOperative = <?= json_encode(KphisQueryUtils::getDocumentOperative($an)) ?>;
        const get_document_neodms_operative = <?= json_encode(ExternalDocumentTracker::get_document_neodms_operative($an)) ?>;
        if ((getDocumentOperative) || (get_document_neodms_operative)) {
            $("#check_countRowData_Operative").attr("class", "text-success fas fa-check-square");
            if (getDocumentOperative) {
                $("#show_text_Operative_hosxp").attr("class", "text-light font-weight-bold badge badge-dark").text(" HOSxP ");
            }
            if (get_document_neodms_operative) {
                $("#show_text_Operative_scan").attr("class", "text-dark font-weight-bold badge badge-info").text(" SCAN ");
            }
        } else {
            $("#check_countRowData_Operative").attr("class", "text-secondary fas fa-square");
        }
    }


    function onclickOrder_ProgressNote_pdf() {
        event.preventDefault();
        $('#pills-tab a[href="#pills-order"]').one('shown.bs.tab', function(e) {
            onclickPrintAllOrderButton(event);
        });
        $('#pills-tab a[href="#pills-order"]').tab('show');
    }

    function onclick_VitalSign_pdf() {
        event.preventDefault();
        $('#pills-tab a[href="#pills-tab3"]').one('shown.bs.tab', function(e) {
            onclickLastXDays(event, -1);
            //window.print();
        });
        $('#pills-tab a[href="#pills-tab3"]').tab('show');
    }
</script>

<script>
    function AddDoctorSignature() {
        const doc_name = <?= json_encode($_SESSION['name']) ?>;
        const doctorcode = <?= json_encode($_SESSION['doctorcode']) ?>;
        const clone_template_dr_admission_input_div = document.querySelector('#template_dr_admission_input_div').content.cloneNode(true);

        if (CheckDoctorSignature()) {
            $('#dr-admission-group-input-div').append(clone_template_dr_admission_input_div);
            $('[name="doctor[]"].last-focus-input').removeClass('last-focus-input');
            $('[name="doc_name[]"].last-focus-input').removeClass('last-focus-input');

            $('[name="doctor[]"]').last().addClass('last-focus-input').val(doctorcode);
            $('[name="doc_name[]"]').last().addClass('last-focus-input').val(doc_name);


        }

        console.log(doctorcode)


    }

    function CheckDoctorSignature() {
        const doctorcode_check = <?= json_encode($_SESSION['doctorcode']) ?>;
        let return_checkdoctorSignature = true;
        $.each($("input:hidden[name='doctor[]']"), function(index, value) {
            //console.log({index,value})
            if (doctorcode_check == $(this).val()) {
                alert("คุณได้ลงชื่อบันทึกข้อมูลไว้แล้ว");
                return_checkdoctorSignature = false;
                return false;
            }
        });
        return return_checkdoctorSignature;


    }


    function AddNurseSignature() {
        const nurse_name = <?= json_encode($_SESSION['name']) ?>;
        const nursecode = <?= json_encode($_SESSION['doctorcode']) ?>;
        const clone_template_nurse_admission_input_div = document.querySelector('#template_nurse_admission_input_div').content.cloneNode(true);

        if (CheckNurseSignature()) {
            $('#nurse-admission-group-input-div').append(clone_template_nurse_admission_input_div);
            $('[name="nurse[]"].last-focus-input').removeClass('last-focus-input');
            $('[name="nurse_name[]"].last-focus-input').removeClass('last-focus-input');

            $('[name="nurse[]"]').last().addClass('last-focus-input').val(nursecode);
            $('[name="nurse_name[]"]').last().addClass('last-focus-input').val(nurse_name);


        }

        console.log(nursecode)


    }


    function CheckNurseSignature() {
        const nursecode_check = <?= json_encode($_SESSION['doctorcode']) ?>;
        let return_checknurseSignature = true;
        $.each($("input:hidden[name='nurse[]']"), function(index, value) {
            //console.log({index,value})
            if (nursecode_check == $(this).val()) {
                alert("คุณได้ลงชื่อบันทึกข้อมูลไว้แล้ว");
                return_checknurseSignature = false;
                return false;
            }
        });
        return return_checknurseSignature;


    }

    /*function signature_save() { 

        alert('OK');
        var url_update = "prs-pre-nursenote-update.php";
        var url_save = "prs-pre-nursenote-save.php";
    } */
</script>