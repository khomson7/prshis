<?php
require_once '../include/Session.php';
Session::checkLoginSessionAndShowMessage();
Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE', 'VIEW');

require_once '../mains/main-report.php';
require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once 'pre-ane-assess-helpers.php';

$an = isset($_REQUEST['an']) ? $_REQUEST['an'] : '';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$loginname = $_SESSION['loginname'];

$hn = KphisQueryUtils::getHnByAn($an);

// Fetch Wards
$conn = DbUtils::get_hosxp_connection();
$stmt_ward = $conn->prepare("SELECT ward, name FROM " . DbConstant::HOSXP_DBNAME . ".ward WHERE ward_active = 'Y' ORDER BY name");
$stmt_ward->execute();
$wards = $stmt_ward->fetchAll(PDO::FETCH_KEY_PAIR);
?>
    <style>
        body { background-color: #f8f9fa; font-size: 0.85rem; }
        .form-section { background: white; padding: 15px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .section-title { font-weight: bold; border-bottom: 2px solid #2c5282; padding-bottom: 5px; margin-bottom: 15px; color: #2c5282; }
        .form-control-sm { height: calc(1.5em + 0.5rem + 2px); padding: 0.25rem 0.5rem; font-size: 0.875rem; line-height: 1.5; border-radius: 0.2rem; }
        .patient-info { background: #e2e8f0; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container-fluid py-3">
    <h4 class="text-center mb-4">แบบบันทึกทางการพยาบาลการเตรียมผู้ป่วยก่อนให้ยาระงับความรู้สึก</h4>

    <form id="pre_ane_form" onsubmit="return false;">
        <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <input type="hidden" name="version" value="1">

        <!-- Header -->
        <div class="form-section">
            <div class="row mb-2">
                <div class="col-md-4">
                    <strong>Visit Type:</strong><br>
                    <?= paa_radio('visit_type', 'Elective', 'Elective') ?>
                    <?= paa_radio('visit_type', 'OPD', 'OPD') ?>
                    <?= paa_radio('visit_type', 'Set เพิ่ม', 'Set เพิ่ม') ?>
                    <?= paa_radio('visit_type', 'Emergency', 'Emergency') ?>
                </div>
                <div class="col-md-5">
                    <strong>เยี่ยมที่:</strong><br>
                    <?= paa_radio('visit_place', 'OR', 'OR', 'visit_place_or') ?>
                    <?= paa_radio('visit_place', 'Ward', 'Ward', 'visit_place_ward') ?>
                    <div style="display:inline-block; width: 200px; vertical-align: middle;">
                        <?= paa_select('ward', $wards, 'form-control-sm select2') ?>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    ผ่าตัดวันที่: <?= paa_date('operation_date', 'form-control-sm ml-2') ?>
                </div>
            </div>
        </div>

        <!-- 1. การซักประวัติ -->
        <div class="form-section">
            <h5 class="section-title">1. การซักประวัติ</h5>
            <div class="row mb-2">
                <div class="col-md-4">
                    <strong>1.1 Activity</strong><br>
                    <?= paa_radio('activity', 'ทำได้ปกติ', 'ทำได้ปกติ') ?>
                    <?= paa_radio('activity', 'มีข้อจำกัด', 'มีข้อจำกัด') ?>
                    <?= paa_radio('activity', 'ต้องนอนบนเตียง', 'ต้องนอนบนเตียง') ?>
                </div>
                <div class="col-md-8 d-flex align-items-center">
                    FC: <?= paa_text('fc', '', 'form-control-sm ml-2') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <strong>1.2 โรคประจำตัว</strong><br>
                    <?= paa_cb('underlying_dm', 'DM') ?> ปี Tx <?= paa_text('underlying_dm_tx', '', 'form-control-sm d-inline-block w-auto') ?>
                    <?= paa_cb('underlying_ht', 'HT') ?> ปี Tx <?= paa_text('underlying_ht_tx', '', 'form-control-sm d-inline-block w-auto') ?>
                    <?= paa_cb('underlying_dlp', 'DLP') ?>
                    <br>
                    <?= paa_cb('underlying_asthma', 'Asthma/COPD') ?> ปี Tx <?= paa_text('underlying_asthma_tx', '', 'form-control-sm d-inline-block w-auto') ?>
                    <?= paa_cb('underlying_heart', 'Heart dz.') ?> ปี Tx <?= paa_text('underlying_heart_tx', '', 'form-control-sm d-inline-block w-auto') ?>
                    <?= paa_cb('underlying_other', 'อื่นๆ') ?> <?= paa_text('underlying_other_text', 'ระบุ', 'form-control-sm d-inline-block w-auto') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <strong>1.3 การดื่มสุรา</strong><br>
                    <?= paa_radio('alcohol', 'ไม่ดื่ม', 'ไม่ดื่ม') ?>
                    <?= paa_radio('alcohol', 'เคย,เลิกมา', 'เคย,เลิกมา') ?> ปี <?= paa_text('alcohol_year', '', 'form-control-sm d-inline-block', '60px') ?>
                    <?= paa_radio('alcohol', 'ดื่มเล็กน้อย', 'ดื่มเล็กน้อย') ?>
                    <?= paa_radio('alcohol', 'ดื่มทุกวัน', 'ดื่มทุกวัน') ?>
                </div>
                <div class="col-md-6">
                    <strong>1.4 การสูบบุหรี่</strong><br>
                    <?= paa_radio('smoking', 'ไม่สูบ', 'ไม่สูบ') ?>
                    <?= paa_radio('smoking', 'เคย,เลิกมา', 'เคย,เลิกมา') ?> ปี <?= paa_text('smoking_year', '', 'form-control-sm d-inline-block', '60px') ?>
                    <?= paa_radio('smoking', 'สูบ', 'สูบ') ?> <?= paa_text('smoking_per_day', '', 'form-control-sm d-inline-block', '60px') ?> มวน/วัน
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <strong>1.5 การแพ้ยา อาหาร สารเคมี</strong><br>
                    <?= paa_radio('allergy', 'ไม่มี', 'ไม่มี') ?>
                    <?= paa_radio('allergy', 'มี', 'มี') ?> <?= paa_text('allergy_detail', 'ระบุ', 'form-control-sm d-inline-block w-auto') ?>
                </div>
                <div class="col-md-6">
                    <strong>1.6 ประวัติการผ่าตัด, การดมยา</strong><br>
                    <?= paa_radio('prev_surgery', 'ไม่เคย', 'ไม่เคย') ?>
                    <?= paa_radio('prev_surgery', 'เคย', 'เคย') ?>
                    <?= paa_cb('prev_surgery_ga', 'GA') ?> <?= paa_cb('prev_surgery_ra', 'RA') ?>
                    เมื่อ <?= paa_date('prev_surgery_date', 'form-control-sm d-inline-block', '150px') ?>
                    <?= paa_cb('prev_complication', 'Complication') ?> <?= paa_text('prev_complication_text', '', 'form-control-sm d-inline-block w-auto') ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <strong>1.7 ประวัติยา</strong>
                    <?= paa_ta('medication_history', 2) ?>
                </div>
            </div>
        </div>

        <!-- 2. การตรวจร่างกาย -->
        <div class="form-section">
            <h5 class="section-title">2. การตรวจร่างกาย</h5>
            <div class="row mb-2">
                <div class="col-md-12 form-inline">
                    BW <?= paa_text('pe_bw', 'Kgs', 'form-control-sm mx-1', '60px') ?>
                    Ht <?= paa_text('pe_ht', 'Cms', 'form-control-sm mx-1', '60px') ?>
                    BMI <?= paa_text('pe_bmi', 'Kgs/m2', 'form-control-sm mx-1', '80px') ?>
                    T <?= paa_text('pe_temp', 'C', 'form-control-sm mx-1', '60px') ?>
                    PR <?= paa_text('pe_pr', '/min', 'form-control-sm mx-1', '60px') ?>
                    RR <?= paa_text('pe_rr', '/min', 'form-control-sm mx-1', '60px') ?>
                    BP <?= paa_text('pe_bp', 'mmHg', 'form-control-sm mx-1', '80px') ?>
                    SpO2 <?= paa_text('pe_spo2', '%', 'form-control-sm mx-1', '60px') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <strong>2.1 ตรวจร่างกายทั่วไป</strong>:
                    <?= paa_cb('general_normal', 'ปกติ') ?>
                    <?= paa_cb('general_pale', 'ซีด') ?>
                    <?= paa_cb('general_jaundice', 'เหลือง') ?>
                    <?= paa_cb('general_dry_mouth', 'ปากแห้ง') ?>
                    <?= paa_cb('general_fatigue', 'อ่อนเพลีย') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <strong>2.2 Consciousness</strong>:
                    <?= paa_radio('consciousness', 'Alert', 'Alert') ?>
                    <?= paa_radio('consciousness', 'Drowsiness', 'Drowsiness') ?>
                    <?= paa_radio('consciousness', 'Stupor', 'Stupor') ?>
                    <?= paa_radio('consciousness', 'Coma', 'Coma') ?>
                </div>
                <div class="col-md-6">
                    <strong>2.3 Airway อ้าปาก & ก้มเงย</strong>:
                    <?= paa_radio('airway_mouth', 'ปกติ', 'ปกติ') ?>
                    <?= paa_radio('airway_mouth', 'ผิดปกติ', 'ผิดปกติ') ?> <?= paa_text('airway_mouth_detail', '', 'form-control-sm d-inline-block w-auto') ?>
                    <?= paa_cb('airway_unable', 'ไม่สามารถประเมินได้') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <strong>Mallampati Class</strong>:
                    <?= paa_radio('mallampati', '1', '1') ?>
                    <?= paa_radio('mallampati', '2', '2') ?>
                    <?= paa_radio('mallampati', '3', '3') ?>
                    <?= paa_radio('mallampati', '4', '4') ?>
                </div>
                <div class="col-md-6">
                    On ET/TT tube No. <?= paa_text('on_ett_no', '', 'form-control-sm d-inline-block', '100px') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <strong>Thyromental distance</strong>:
                    <?= paa_radio('thyromental', '>= 3FB, 6 cm', '>= 3FB, 6 cm') ?>
                    <?= paa_radio('thyromental', '< 3FB', '< 3FB') ?>
                </div>
                <div class="col-md-6">
                    <strong>2.4 ฟันปลอม</strong>:
                    <?= paa_radio('denture', 'ไม่มี', 'ไม่มี') ?>
                    <?= paa_radio('denture', 'มี', 'มี') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <strong>2.5 Heart & Lungs</strong>:
                    <?= paa_radio('heart_lungs', 'Normal', 'Normal') ?>
                    <?= paa_radio('heart_lungs', 'Abnormal', 'Abnormal') ?> <?= paa_text('heart_lungs_detail', 'ระบุ', 'form-control-sm d-inline-block w-auto') ?>
                </div>
                <div class="col-md-6">
                    <strong>2.6 Motor Power</strong>:
                    <?= paa_radio('motor_power', 'Normal', 'Normal') ?>
                    <?= paa_radio('motor_power', 'Abnormal', 'Abnormal') ?> <?= paa_text('motor_power_detail', 'ระบุ', 'form-control-sm d-inline-block w-auto') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <strong>2.7 Other</strong>:
                    <?= paa_radio('other_exam', 'Normal', 'Normal') ?>
                    <?= paa_radio('other_exam', 'Abnormal', 'Abnormal') ?> <?= paa_text('other_exam_detail', 'ระบุ', 'form-control-sm d-inline-block w-auto') ?>
                </div>
            </div>
        </div>

        <!-- 3. Lab -->
        <div class="form-section">
            <h5 class="section-title">3. Lab</h5>
            <div class="row mb-2">
                <div class="col-md-12">
                    <?= paa_radio('lab_status', 'ได้แล้ว', 'ได้แล้ว') ?>
                    <?= paa_radio('lab_status', 'ยังไม่ได้ผล Lab', 'ยังไม่ได้ผล Lab') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12 form-inline">
                    <strong>3.1 CBC</strong>: 
                    Hct <?= paa_text('lab_hct', 'Vol%', 'form-control-sm mx-1', '60px') ?>
                    Hb <?= paa_text('lab_hb', 'Gm%', 'form-control-sm mx-1', '60px') ?>
                    Plt <?= paa_text('lab_plt', '', 'form-control-sm mx-1', '80px') ?>
                    Other <?= paa_text('lab_other_cbc', '', 'form-control-sm mx-1', '100px') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12 form-inline">
                    <strong>3.2</strong>:
                    FBS <?= paa_text('lab_fbs', 'mg%', 'form-control-sm mx-1', '60px') ?>
                    BUN <?= paa_text('lab_bun', '', 'form-control-sm mx-1', '60px') ?>
                    Cr <?= paa_text('lab_cr', '', 'form-control-sm mx-1', '60px') ?>
                    PT <?= paa_text('lab_pt', '', 'form-control-sm mx-1', '60px') ?>
                    PTT <?= paa_text('lab_ptt', '', 'form-control-sm mx-1', '60px') ?>
                    INR <?= paa_text('lab_inr', '', 'form-control-sm mx-1', '60px') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12 form-inline">
                    <strong>3.3 Electrolyte</strong>:
                    <?= paa_radio('lab_electrolyte_check', 'ไม่ตรวจ', 'ไม่ตรวจ') ?>
                    <?= paa_radio('lab_electrolyte_check', 'ตรวจ', 'ตรวจ') ?>
                    Na <?= paa_text('lab_na', '', 'form-control-sm mx-1', '50px') ?>
                    K <?= paa_text('lab_k', '', 'form-control-sm mx-1', '50px') ?>
                    Cl <?= paa_text('lab_cl', '', 'form-control-sm mx-1', '50px') ?>
                    HCO3 <?= paa_text('lab_hco3', '', 'form-control-sm mx-1', '50px') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12 form-inline">
                    <strong>3.4 UA</strong>:
                    <?= paa_radio('lab_ua_check', 'ไม่ตรวจ', 'ไม่ตรวจ') ?>
                    <?= paa_radio('lab_ua_check', 'ตรวจ', 'ตรวจ') ?>
                    Sp.gr <?= paa_text('lab_sp_gr', '', 'form-control-sm mx-1', '60px') ?>
                    Prot. <?= paa_text('lab_prot', '', 'form-control-sm mx-1', '60px') ?>
                    Sugar <?= paa_text('lab_sugar', '', 'form-control-sm mx-1', '60px') ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4">
                    <strong>3.5 CXR</strong>:
                    <?= paa_radio('lab_cxr_check', 'ไม่ตรวจ', 'ไม่ตรวจ') ?>
                    <?= paa_radio('lab_cxr_check', 'ตรวจ', 'ตรวจ') ?>
                    <br>
                    <?= paa_radio('lab_cxr_result', 'ไม่มีผลอ่าน', 'ไม่มีผลอ่าน') ?>
                    <?= paa_radio('lab_cxr_result', 'ปกติ', 'ปกติ') ?>
                    <?= paa_radio('lab_cxr_result', 'ผิดปกติ', 'ผิดปกติ') ?>
                    <?= paa_text('lab_cxr_detail', 'ระบุ', 'form-control-sm mt-1 w-100') ?>
                </div>
                <div class="col-md-4">
                    <strong>3.6 EKG</strong>:
                    <?= paa_radio('lab_ekg_check', 'ไม่ตรวจ', 'ไม่ตรวจ') ?>
                    <?= paa_radio('lab_ekg_check', 'ตรวจ', 'ตรวจ') ?>
                    <br>
                    <?= paa_radio('lab_ekg_result', 'ไม่มีผลอ่าน', 'ไม่มีผลอ่าน') ?>
                    <?= paa_radio('lab_ekg_result', 'ปกติ', 'ปกติ') ?>
                    <?= paa_radio('lab_ekg_result', 'ผิดปกติ', 'ผิดปกติ') ?>
                    <?= paa_text('lab_ekg_detail', 'ระบุ', 'form-control-sm mt-1 w-100') ?>
                </div>
                <div class="col-md-4">
                    <strong>3.7 Other</strong>:
                    <?= paa_cb('lab_other_check', 'ตรวจ') ?>
                    <?= paa_text('lab_other_detail', 'ระบุ', 'form-control-sm mt-1 w-100') ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <!-- 4. จองเลือด -->
                <div class="form-section">
                    <h5 class="section-title">4. จองเลือด</h5>
                    <?= paa_radio('blood_reserve', 'ไม่จอง', 'ไม่จอง') ?>
                    <?= paa_radio('blood_reserve', 'จอง', 'จอง') ?>
                    <div class="form-inline mt-2">
                        WB <?= paa_text('blood_wb', 'U', 'form-control-sm mx-1', '50px') ?>
                        PRC <?= paa_text('blood_prc', 'U', 'form-control-sm mx-1', '50px') ?>
                        FFP <?= paa_text('blood_ffp', 'U', 'form-control-sm mx-1', '50px') ?>
                        Plt <?= paa_text('blood_plt_unit', 'U', 'form-control-sm mx-1', '50px') ?>
                        Cryo <?= paa_text('blood_cryo', 'U', 'form-control-sm mx-1', '50px') ?>
                        Others <?= paa_text('blood_others', '', 'form-control-sm mx-1', '80px') ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- 5. เซ็นยินยอม & 6. ASA -->
                <div class="form-section">
                    <h5 class="section-title">5. เซ็นยินยอม</h5>
                    <?= paa_radio('consent_signed', 'มี', 'มี') ?>
                    <?= paa_radio('consent_signed', 'ไม่มี', 'ไม่มี') ?>
                    
                    <h5 class="section-title mt-3">6. ASA Physical Status</h5>
                    <?= paa_radio('asa_class', '1', '1') ?>
                    <?= paa_radio('asa_class', '2', '2') ?>
                    <?= paa_radio('asa_class', '3', '3') ?>
                    <?= paa_radio('asa_class', '4', '4') ?>
                    <?= paa_radio('asa_class', '5', '5') ?>
                    <?= paa_radio('asa_class', '6', '6') ?>
                    <?= paa_radio('asa_class', 'E', 'E') ?>
                </div>
            </div>
        </div>

        <!-- 7-9. Planning -->
        <div class="form-section">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="section-title">7. Problem List</h5>
                    <?= paa_radio('problem_list_status', 'ไม่มี', 'ไม่มี') ?>
                    <?= paa_radio('problem_list_status', 'มี ระบุ', 'มี ระบุ') ?>
                    <ol class="pl-3 mt-2">
                        <li><?= paa_text('problem_1', '', 'form-control-sm mb-1 w-100') ?></li>
                        <li><?= paa_text('problem_2', '', 'form-control-sm mb-1 w-100') ?></li>
                        <li><?= paa_text('problem_3', '', 'form-control-sm mb-1 w-100') ?></li>
                        <li><?= paa_text('problem_4', '', 'form-control-sm mb-1 w-100') ?></li>
                    </ol>
                </div>
                <div class="col-md-4">
                    <h5 class="section-title">8. การแก้ปัญหา/รักษา</h5>
                    <div class="mb-1"><?= paa_text('treatment_1', '1.', 'form-control-sm w-100') ?></div>
                    <div class="mb-1"><?= paa_text('treatment_2', '2.', 'form-control-sm w-100') ?></div>
                    <div class="mb-1"><?= paa_text('treatment_3', '3.', 'form-control-sm w-100') ?></div>
                    <div class="mb-1"><?= paa_text('treatment_4', '4.', 'form-control-sm w-100') ?></div>
                    
                    <strong>Consult</strong>: 
                    <?= paa_radio('consult_status', 'ไม่มี', 'ไม่มี') ?>
                    <?= paa_radio('consult_status', 'มี', 'มี') ?>
                    <?= paa_cb('consult_med', 'Med') ?>
                    <?= paa_cb('consult_anesth', 'Anesth') ?>

                    <strong class="d-block mt-2">Premedication</strong>
                    <div class="mb-1"><?= paa_text('premed_1', '1.', 'form-control-sm w-100') ?></div>
                    <div class="mb-1"><?= paa_text('premed_2', '2.', 'form-control-sm w-100') ?></div>
                    <div class="mb-1"><?= paa_text('premed_3', '3.', 'form-control-sm w-100') ?></div>
                </div>
                <div class="col-md-4">
                    <h5 class="section-title">9. Planning</h5>
                    <?= paa_cb('plan_ga', 'GA') ?><br>
                    <?= paa_cb('plan_ra', 'RA') ?><br>
                    <?= paa_cb('plan_ra_ga', 'RA+GA') ?><br>
                    <?= paa_cb('plan_mac', 'MAC') ?><br>
                    <?= paa_cb('plan_tiva', 'TIVA') ?><br>
                    <?= paa_cb('plan_icu', 'จอง ICU') ?>

                    <strong class="d-block mt-2">Spec. Technique</strong>
                    <?= paa_cb('spec_a_line', 'A-Line') ?>
                    <?= paa_cb('spec_cvp', 'CVP') ?>
                    <?= paa_cb('spec_pca', 'PCA') ?>
                    <?= paa_cb('spec_dlt', 'DLT') ?>
                    <?= paa_cb('spec_fiberoptic', 'Fiberoptic') ?>
                    <div class="mt-1">
                        <?= paa_cb('spec_other', 'อื่นๆ') ?> <?= paa_text('spec_other_text', 'ระบุ', 'form-control-sm d-inline-block w-auto') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 10. กิจกรรม การพยาบาลก่อนให้ยาระงับความรู้สึก -->
        <div class="form-section">
            <h5 class="section-title">10. กิจกรรม การพยาบาลก่อนให้ยาระงับความรู้สึก</h5>
            <div class="row">
                <div class="col-md-6">
                    <?= paa_cb('act_check_identity', 'ตรวจสอบความถูกต้องของผู้ป่วย&เวชระเบียน') ?><br>
                    <?= paa_cb('act_assess_asa', 'ประเมินสภาพผู้ป่วยตาม ASA class') ?><br>
                    <?= paa_cb('act_explain_anes', 'อธิบายถึงวิธีการให้ยาระงับความรู้สึกและความเสี่ยงที่อาจเกิดขึ้น') ?><br>
                    <?= paa_cb('act_plan_anes', 'วางแผนเลือกวิธีให้ยาระงับความรู้สึกที่เหมาะสม') ?><br>
                    <?= paa_cb('act_check_consent', 'ตรวจสอบการลงนามยินยอมการรับบริการทางวิสัญญี') ?><br>
                    <?= paa_cb('act_pain_advice', 'ให้คำแนะนำเรื่องการระงับปวด') ?>
                </div>
                <div class="col-md-6">
                    <div class="d-flex mb-1">
                        <span class="mr-2">ให้คำแนะนำในเรื่อง:</span> <?= paa_ta('act_advice', 2) ?>
                    </div>
                    <div class="form-inline mb-1">
                        1. NPO เวลา <?= paa_text('act_npo_time', 'น.', 'form-control-sm mx-1', '60px') ?> 
                        ถอดฟันปลอมของมีค่า <?= paa_text('act_npo_denture', '', 'form-control-sm mx-1', '120px') ?>
                    </div>
                    <?= paa_cb('act_teach_breathe', '2. สอนการหายใจ ไอ อย่างมีประสิทธิภาพ') ?><br>
                    <?= paa_cb('act_prepare_body', '3. เตรียมร่างกาย จิตใจ การพักผ่อน') ?><br>
                    4. อื่นๆ โปรดระบุ <?= paa_text('act_other_5', '', 'form-control-sm d-inline-block w-auto') ?>
                </div>
            </div>
        </div>

        <!-- 11. กิจกรรมการพยาบาล ห้อง Pre-op -->
        <div class="form-section">
            <h5 class="section-title">11. กิจกรรมการพยาบาล ห้อง Pre-op</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-inline mb-2">
                        ผู้ป่วยมาถึงห้อง Pre-op เวลา <?= paa_text('preop_arrival_time', 'น.', 'form-control-sm mx-2', '80px') ?>
                    </div>
                    <?= paa_cb('preop_talk', 'พูดคุย อธิบายถึงขั้นตอนการรับบริการทางวิสัญญี') ?><br>
                    <?= paa_cb('preop_check_identity', 'ตรวจสอบความถูกต้องของเวชระเบียนตรงกับผู้ป่วย') ?><br>
                    <div class="d-flex align-items-center mb-1">
                        <span class="mr-2">ชนิดการผ่าตัด ถูกคน ถูกข้าง:</span> <?= paa_text('preop_check_surgery', '', 'form-control-sm w-50') ?>
                    </div>
                    <?= paa_cb('preop_check_fluid', 'ตรวจสอบความถูกต้องของสารน้ำและบริเวณที่ให้') ?>
                </div>
                <div class="col-md-6">
                    <?= paa_cb('preop_no_infiltrate', 'สารน้ำไม่มีภาวะแทรกซ้อน') ?><br>
                    <?= paa_cb('preop_comfort', 'ดูแลความสุขสบายผู้ป่วย') ?><br>
                    <?= paa_cb('preop_o2_mask', 'ดูแลให้ O2 Mask') ?><br>
                    <?= paa_cb('preop_3way', 'ต่อ 3 way & extension') ?><br>
                    <?= paa_cb('preop_other', 'อื่นๆ') ?> <?= paa_text('preop_other_text', 'ระบุ', 'form-control-sm d-inline-block w-auto') ?>
                    <div class="mt-2">
                        ผู้ประเมิน: <?= paa_text('preop_assess_by', '', 'form-control-sm d-inline-block w-auto') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 12. สรุปประเมินผล -->
        <div class="form-section">
            <h5 class="section-title">12. สรุปประเมินผล</h5>
            <div class="row">
                <div class="col-md-12">
                    <?= paa_cb('result_got_surgery', 'ได้รับการผ่าตัด') ?><br>
                    <?= paa_cb('result_postpone', 'งดเลื่อน เนื่องจาก') ?><br>
                    <div class="pl-4">
                        <?= paa_cb('result_postpone_reason1', '1. แพทย์ผ่าตัด') ?>
                        <?= paa_cb('result_postpone_reason2', '2. ผู้ป่วยปฏิเสธการผ่าตัด') ?>
                        <?= paa_cb('result_postpone_reason3', '3. ไม่มีเลือด') ?><br>
                        <?= paa_cb('result_postpone_reason4', '4. มีปัญหาอายุรกรรมยังไม่ได้แก้ไขจากอายุรแพทย์') ?><br>
                        <?= paa_cb('result_postpone_reason5', '5. ไม่ได้เตรียมเตียง ICU') ?><br>
                        6. อื่นๆ โปรดระบุ <?= paa_text('result_postpone_reason6', '', 'form-control-sm d-inline-block w-50') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="form-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2"><strong>DIAGNOSIS:</strong> <?= paa_ta('diagnosis', 2) ?></div>
                    <div class="mb-2"><strong>OPERATION PLAN:</strong> <?= paa_ta('operation_plan', 2) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="form-inline mb-2">
                        <strong>Visit Date:</strong> <?= paa_date('visit_date', 'form-control-sm ml-2') ?>
                    </div>
                    <div class="form-inline mb-2">
                        <strong>ผู้เยี่ยม:</strong> 
                        <div class="input-group input-group-sm ml-2">
                            <input type="text" class="form-control form-control-sm" name="visitor_name" id="visitor_name" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success" onclick="signVisitor()"><i class="fas fa-pencil-alt"></i> ลงชื่อ</button>
                                <button type="button" class="btn btn-outline-danger" onclick="clearVisitorSign()"><i class="fas fa-eraser"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="form-inline mb-2">
                        <strong>ผู้เตรียมอุปกรณ์+Machine:</strong> 
                        <div class="input-group input-group-sm ml-2">
                            <input type="text" class="form-control form-control-sm" name="equip_staff" id="equip_staff" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success" onclick="signEquip()"><i class="fas fa-pencil-alt"></i> ลงชื่อ</button>
                                <button type="button" class="btn btn-outline-danger" onclick="clearEquipSign()"><i class="fas fa-eraser"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="form-inline mb-2">
                        <strong>Attending physician:</strong> 
                        <div class="input-group input-group-sm ml-2">
                            <input type="text" class="form-control form-control-sm" name="attending_physician" id="attending_physician" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success" onclick="signAttending()"><i class="fas fa-pencil-alt"></i> ลงชื่อ</button>
                                <button type="button" class="btn btn-outline-danger" onclick="clearAttendingSign()"><i class="fas fa-eraser"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-5 mt-4">
            <?php if (ReportQueryUtils::checkReadOnly($an)): ?>
            <button type="button" class="btn btn-primary px-5 shadow" id="btn_save" onclick="saveData()"><i class="fas fa-save"></i> บันทึกข้อมูล</button>
            <?php endif; ?>
            <?php if ($id): ?>
            <button type="button" class="btn btn-info px-4 shadow ml-2" onclick="printPDF()"><i class="fas fa-print"></i> พิมพ์ PDF</button>
            <?php endif; ?>
            <button type="button" class="btn btn-secondary px-4 shadow ml-2" onclick="window.close()"><i class="fas fa-times"></i> ปิด</button>
        </div>
    </form>
</div>

<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>

<?php if ($id): ?>
<div id="edit_script_container"></div>
<script>
    // Load edit script
    $.get('pre-ane-assess-edit.php?an=<?=urlencode($an)?>&id=<?=urlencode($id)?>', function(res){
        $('#edit_script_container').html(res);
    });
</script>
<?php endif; ?>

<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    $('input[name=visit_place]').on('change', function() {
        if (this.value === 'OR') {
            $('#ward').val('').trigger('change');
            $('#ward').prop('disabled', true);
        } else {
            $('#ward').prop('disabled', false);
        }
    });
});

var _sessionName = <?= json_encode($_SESSION['name']) ?>;

function signVisitor() {
    var nameEl = document.getElementById('visitor_name');
    if (!nameEl.value.trim()) nameEl.value = _sessionName;
    Swal.fire({
        toast: true, position: 'top-end', icon: 'success',
        title: 'ลงชื่อผู้เยี่ยมแล้ว — ' + nameEl.value,
        showConfirmButton: false, timer: 2000
    });
}

function clearVisitorSign() {
    Swal.fire({
        title: 'ล้างลายเซ็นผู้เยี่ยม?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'ล้าง',
        cancelButtonText: 'ยกเลิก'
    }).then(function(r) {
        if (r.isConfirmed) {
            document.getElementById('visitor_name').value = '';
        }
    });
}

function signEquip() {
    var nameEl = document.getElementById('equip_staff');
    Swal.fire({
        title: 'ลงชื่อผู้เตรียมอุปกรณ์+Machine',
        input: 'text',
        inputValue: nameEl.value || _sessionName,
        showCancelButton: true,
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก'
    }).then(function(result) {
        if (result.isConfirmed && result.value.trim() !== '') {
            nameEl.value = result.value.trim();
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'ลงชื่อสำเร็จ: ' + nameEl.value,
                showConfirmButton: false, timer: 2000
            });
        }
    });
}

function clearEquipSign() {
    Swal.fire({
        title: 'ล้างลายเซ็นผู้เตรียมอุปกรณ์?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'ล้าง',
        cancelButtonText: 'ยกเลิก'
    }).then(function(r) {
        if (r.isConfirmed) {
            document.getElementById('equip_staff').value = '';
        }
    });
}

function signAttending() {
    var nameEl = document.getElementById('attending_physician');
    Swal.fire({
        title: 'ลงชื่อ Attending physician',
        input: 'text',
        inputValue: nameEl.value,
        showCancelButton: true,
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก',
        inputPlaceholder: 'ระบุชื่อแพทย์...'
    }).then(function(result) {
        if (result.isConfirmed && result.value.trim() !== '') {
            nameEl.value = result.value.trim();
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'ลงชื่อสำเร็จ: ' + nameEl.value,
                showConfirmButton: false, timer: 2000
            });
        }
    });
}

function clearAttendingSign() {
    Swal.fire({
        title: 'ล้างลายเซ็น Attending physician?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'ล้าง',
        cancelButtonText: 'ยกเลิก'
    }).then(function(r) {
        if (r.isConfirmed) {
            document.getElementById('attending_physician').value = '';
        }
    });
}

function printPDF() {
    var an = $("input[name=an]").val();
    var id = $("input[name=id]").val();
    var loginname = <?= json_encode($loginname) ?>;
    if (id) {
        window.open('pre-ane-assess-pdf.php?an=' + encodeURIComponent(an) + '&id=' + encodeURIComponent(id) + '&loginname=' + encodeURIComponent(loginname), '_blank');
    } else {
        Swal.fire('ข้อความ', 'กรุณาบันทึกข้อมูลก่อนพิมพ์ PDF', 'warning');
    }
}

function saveData() {
    var id = $("input[name=id]").val();
    var url = id ? 'pre-ane-assess-update.php' : 'pre-ane-assess-save.php';
    
    $("#btn_save").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');

    $.post(url, $("#pre_ane_form").serialize(), function(resp){
        try {
            var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
            if (data.status === 'success') {
                Swal.fire({
                    title: 'บันทึกสำเร็จ',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(function(){
                    if (window.opener && !window.opener.closed) {
                        window.opener.location.reload(true);
                    }
                    window.close();
                });
            } else {
                Swal.fire('ข้อผิดพลาด', data.message || 'ไม่สามารถบันทึกได้', 'error');
                $("#btn_save").prop('disabled', false).html('<i class="fas fa-save"></i> บันทึกข้อมูล');
            }
        } catch(e) {
            Swal.fire('ข้อผิดพลาด', 'รูปแบบการตอบกลับไม่ถูกต้อง', 'error');
            $("#btn_save").prop('disabled', false).html('<i class="fas fa-save"></i> บันทึกข้อมูล');
        }
    }).fail(function(){
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
        $("#btn_save").prop('disabled', false).html('<i class="fas fa-save"></i> บันทึกข้อมูล');
    });
}
</script>
</body>
</html>
