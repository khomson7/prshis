<?php
require_once '../include/Session.php';
// Simplified session check - ensure session is active via Session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;
// If no session, redirect or handle error (optional, keeping it simple for now)



require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('FORM_ALCOHOL', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);



require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

try {
    $conn = DbUtils::get_hosxp_connection();
    $an = $_REQUEST['an'];
    $hn = KphisQueryUtils::getHnByAn($an);
    $vn = KphisQueryUtils::getVnByAn($an);
    $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;

    // If no ID is provided, try to find an existing record for this AN
    if (!$ids) {
        $sql_check = "SELECT id FROM `prs_alcohol` WHERE an = :an ORDER BY id DESC LIMIT 1";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute(['an' => $an]);
        $res_check = $stmt_check->fetch();
        if ($res_check) {
            $ids = $res_check['id'];
        }
    }

    Session::insertSystemAccessLog(json_encode(array(
        'form' => 'ALCOHOL-FORM',
        'an' => $an,
    ), JSON_UNESCAPED_UNICODE));

    $audit_row = null;
    $audit_items = array();
    if ($ids) {
        $sql = "SELECT * FROM `prs_alcohol` WHERE an = :an AND id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['an' => $an, 'id' => $ids]);
        $audit_row = $stmt->fetch();

        if ($audit_row) {
            $sql_item = "SELECT * FROM `prs_alcohol_item` WHERE alcohol_id = :alcohol_id ORDER BY content_index ASC";
            $stmt_item = $conn->prepare($sql_item);
            $stmt_item->execute(['alcohol_id' => $audit_row['id']]);
            while ($item_row = $stmt_item->fetch()) {
                $audit_items[$item_row['content_index']] = $item_row;
            }
        }
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger" style="margin:20px;">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    // If table doesn't exist, we might want to continue with empty data if it's the first time
    // but usually we should stop and let the user fix the DB.
}

// Patient info for header
$sql_ipt = "SELECT p.pname, p.fname, p.lname, i.regdate, i.regtime, i.dchdate, i.dchtime
            FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
            LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
            WHERE i.an = :an";
$stmt_ipt = $conn->prepare($sql_ipt);
$stmt_ipt->execute(['an' => $an]);
$row_ipt = $stmt_ipt->fetch();

// AUDIT Questions (10 questions)
$audit_questions = [
    1 => [
        'text'    => '1. คุณดื่มสุราบ่อยเพียงไร',
        'type'    => 'standard', // options 0-4
        'options' => [
            0 => 'ไม่เคยเลย',
            1 => 'เดือนละครั้ง<br>หรือน้อยกว่า',
            2 => '2-4 ครั้ง<br>ต่อเดือน',
            3 => '2-3 ครั้ง<br>ต่อสัปดาห์',
            4 => '4 ครั้งขึ้นไป<br>ต่อสัปดาห์',
        ],
    ],
    2 => [
        'text'    => '2. เวลาที่คุณดื่มสุรา โดยทั่วไปแล้วคุณดื่มประมาณเท่าไรต่อวัน<br><small class="text-muted">(เลือกตอบเพียงข้อเดียว: ดื่มมาตรฐาน / เบียร์ / เหล้า)</small>',
        'type'    => 'q2', // special row with sub-options
        'options_std'  => [
            0 => '1-2<br>ดื่มมาตรฐาน',
            1 => '3-4<br>ดื่มมาตรฐาน',
            2 => '5-6<br>ดื่มมาตรฐาน',
            3 => '7-9<br>ดื่มมาตรฐาน',
            4 => 'ตั้งแต่ 10 ดื่ม<br>มาตรฐานขึ้นไป',
        ],
        'options_beer' => [
            0 => '1-1.5 กระป๋อง<br>หรือ 1/2-3/4 ขวด',
            1 => '2-3 กระป๋อง<br>หรือ 1-1.5 ขวด',
            2 => '3.5-4 กระป๋อง<br>หรือ 2 ขวด',
            3 => '4.5-7 กระป๋อง<br>หรือ 3-4 ขวด',
            4 => '7 กระป๋อง<br>หรือ 4 ขวดขึ้นไป',
        ],
        'options_liq'  => [
            0 => '2-3 ฝา',
            1 => '1/4 แบน',
            2 => '1/2 แบน',
            3 => '3/4 แบน',
            4 => '1 แบนขึ้นไป',
        ],
    ],
    3 => [
        'text'    => '3. บ่อยครั้งเพียงไรที่คุณดื่มตั้งแต่ 6 ดื่มมาตรฐานขึ้นไป หรือเบียร์ 4 กระป๋องหรือ 2 ขวดใหญ่ขึ้นไป หรือเหล้าวิสกี้ 3 เป๊กขึ้นไป',
        'type'    => 'standard',
        'options' => [
            0 => 'ไม่เคยเลย',
            1 => 'น้อยกว่า<br>เดือนละครั้ง',
            2 => 'เดือนละครั้ง',
            3 => 'สัปดาห์ละครั้ง',
            4 => 'ทุกวัน หรือ<br>เกือบทุกวัน',
        ],
    ],
    4 => [
        'text'    => '4. ในช่วงหนึ่งปีที่แล้ว มีบ่อยครั้งเพียงไรที่คุณพบว่าคุณไม่สามารถหยุดดื่มได้ หากคุณได้เริ่มดื่มไปแล้ว',
        'type'    => 'standard',
        'options' => [
            0 => 'ไม่เคยเลย',
            1 => 'น้อยกว่า<br>เดือนละครั้ง',
            2 => 'เดือนละครั้ง',
            3 => 'สัปดาห์ละครั้ง',
            4 => 'ทุกวัน หรือ<br>เกือบทุกวัน',
        ],
    ],
    5 => [
        'text'    => '5. ในช่วงหนึ่งปีที่แล้ว มีบ่อยเพียงไรที่คุณไม่ได้ทำสิ่งที่คุณควรจะทำตามปกติ เพราะคุณมัวแต่ไปดื่มสุราเสีย',
        'type'    => 'standard',
        'options' => [
            0 => 'ไม่เคยเลย',
            1 => 'น้อยกว่า<br>เดือนละครั้ง',
            2 => 'เดือนละครั้ง',
            3 => 'สัปดาห์ละครั้ง',
            4 => 'ทุกวัน หรือ<br>เกือบทุกวัน',
        ],
    ],
    6 => [
        'text'    => '6. ในช่วงหนึ่งปีที่แล้ว มีบ่อยเพียงไรที่คุณต้องรีบดื่มสุราทันทีในตอนเช้า เพื่อจะได้ดำเนินชีวิตตามปกติ หรือถอนอาการเมาค้างจากการดื่มหนักในคืนที่ผ่านมา',
        'type'    => 'standard',
        'options' => [
            0 => 'ไม่เคยเลย',
            1 => 'น้อยกว่า<br>เดือนละครั้ง',
            2 => 'เดือนละครั้ง',
            3 => 'สัปดาห์ละครั้ง',
            4 => 'ทุกวัน หรือ<br>เกือบทุกวัน',
        ],
    ],
    7 => [
        'text'    => '7. ในช่วงหนึ่งปีที่แล้ว มีบ่อยเพียงไรที่คุณรู้สึกไม่ดี โกรธหรือเสียใจ เนื่องจากคุณได้ทำบางสิ่งบางอย่างลงไปขณะที่คุณดื่มสุราเข้าไป',
        'type'    => 'standard',
        'options' => [
            0 => 'ไม่เคยเลย',
            1 => 'น้อยกว่า<br>เดือนละครั้ง',
            2 => 'เดือนละครั้ง',
            3 => 'สัปดาห์ละครั้ง',
            4 => 'ทุกวัน หรือ<br>เกือบทุกวัน',
        ],
    ],
    8 => [
        'text'    => '8. ในช่วงหนึ่งปีที่แล้ว มีบ่อยเพียงไรที่คุณไม่สามารถจำได้ว่าเกิดอะไรขึ้นในคืนที่ผ่านมา เพราะว่าคุณได้ดื่มสุราเข้าไป',
        'type'    => 'standard',
        'options' => [
            0 => 'ไม่เคยเลย',
            1 => 'น้อยกว่า<br>เดือนละครั้ง',
            2 => 'เดือนละครั้ง',
            3 => 'สัปดาห์ละครั้ง',
            4 => 'ทุกวัน หรือ<br>เกือบทุกวัน',
        ],
    ],
    9 => [
        'text'    => '9. ตัวคุณเองหรือคนอื่น เคยได้รับบาดเจ็บซึ่งเป็นผลจากการดื่มสุราของคุณหรือไม่',
        'type'    => 'q9q10', // only 0, 2, 4
        'options' => [
            0 => 'ไม่เคยเลย',
            2 => 'เคย แต่ไม่ได้<br>เกิดขึ้นในปีที่แล้ว',
            4 => 'เคยเกิดขึ้น<br>ในช่วงหนึ่งปีที่แล้ว',
        ],
    ],
    10 => [
        'text'    => '10. เคยมีแพทย์ หรือบุคลากรทางการแพทย์หรือเพื่อนฝูงหรือญาติพี่น้องแสดงความเป็นห่วงเป็นใยต่อการดื่มสุราของคุณหรือไม่',
        'type'    => 'q9q10',
        'options' => [
            0 => 'ไม่เคยเลย',
            2 => 'เคย แต่ไม่ได้<br>เกิดขึ้นในปีที่แล้ว',
            4 => 'เคยเกิดขึ้น<br>ในช่วงหนึ่งปีที่แล้ว',
        ],
    ],
];

$check_ = ReportQueryUtils::getProduction(26);
?>

<style>
    .audit-table th,
    .audit-table td {
        border: 1px solid #aaa;
        padding: 6px 4px;
        vertical-align: middle;
    }

    .audit-table thead th {
        background-color: var(--bright-blue);
        color: #fff;
        text-align: center;
        font-weight: bold;
    }

    .audit-table tbody tr:nth-child(even) {
        background-color: var(--bright-blue-light);
    }

    .audit-table tbody tr:hover {
        background-color: #B3E5FC;
    }

    .text-center { text-align: center; }
    .font-weight-bold { font-weight: bold; }

    .score-cell {
        text-align: center;
        min-width: 80px;
    }

    .score-cell label {
        display: block;
        font-size: 0.78rem;
        color: #333;
        margin-top: 4px;
        cursor: pointer;
        line-height: 1.3;
    }

    .score-cell input[type="radio"] {
        cursor: pointer;
        width: 18px;
        height: 18px;
        accent-color: var(--bright-blue);
    }

    .score-header {
        background-color: var(--bright-blue) !important;
        color: #fff;
        font-size: 1rem;
        min-width: 80px;
    }

    .result-card {
        border: 2px solid;
        border-radius: 8px;
        padding: 14px 20px;
    }

    .result-low    { border-color: #28a745; background: #f0fff4; color: #155724; }
    .result-hazard { border-color: #ffc107; background: #fffbea; color: #856404; }
    .result-harm   { border-color: #fd7e14; background: #fff4e6; color: #7d3900; }
    .result-dep    { border-color: #dc3545; background: #fff0f0; color: #721c24; }

    .total-score-box {
        font-size: 2rem;
        font-weight: bold;
        color: var(--bright-blue);
        border: 3px solid var(--bright-blue);
        border-radius: 50%;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
</style>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<div id="formContainer">
    <form id="audit_form">
        <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($ids) ?>">

        <div class="container-fluid">
            <div class="row align-items-center mb-3">
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()"><i
                            class="fas fa-times"></i> ปิดหน้านี้</button>
                </div>
                <div class="col">
                    <h5 class="mb-0"><b>แบบประเมินปัญหาการดื่มสุรา AUDIT : Alcohol Use Disorders Identification Test</b>
                    </h5>
                </div>
                <div class="col-auto">
                    <?php if ($ids): ?>
                        <a href="../pdffile/alcohol-pdf.php?an=<?= htmlspecialchars($an) ?>&id=<?= htmlspecialchars($ids) ?>&loginname=<?= htmlspecialchars($loginname) ?>"
                            target="_blank" class="btn btn-sm btn-info px-4 shadow-sm"><i class="fas fa-file-pdf"></i> พิมพ์
                            PDF</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-3">
                            <label><b>วันที่ประเมิน</b></label>
                            <input type="date" name="audit_date"
                                class="form-control form-control-sm d-inline-block w-auto"
                                value="<?= isset($audit_row['audit_date']) ? $audit_row['audit_date'] : date('Y-m-d') ?>">
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-info py-2 mb-1">
                        <b>คำชี้แจง:</b> คำถามแต่ละข้อต่อไปนี้จะถามถึงประสบการณ์การดื่มสุราในรอบ 1 ปีที่ผ่านมา
                        โดยสุรา หมายถึงเครื่องดื่มที่มีแอลกอฮอล์ทุกชนิด ได้แก่ เบียร์ เหล้า สาโท กระแช่ วิสกี้ สปายไวน์ เป็นต้น
                        ขอให้ตอบตามความเป็นจริง
                    </div>
                </div>





            </div>

            <div class="table-responsive">
                <table class="audit-table w-100" style="font-size: 0.88rem;">
                    <thead>
                        <tr>
                            <th style="width: 35%; text-align:left; padding: 8px 10px;">ข้อคำถาม</th>
                            <th class="score-header" style="width: 12%;">0</th>
                            <th class="score-header" style="width: 12%;">1</th>
                            <th class="score-header" style="width: 12%;">2</th>
                            <th class="score-header" style="width: 12%;">3</th>
                            <th class="score-header" style="width: 12%;">4</th>
                            <th class="score-header" style="width: 5%;">คะแนน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit_questions as $qn => $q):
                            // Get saved score for this question
                            $saved_score = null;
                            if (isset($audit_items[$qn])) {
                                $saved_score = (string)$audit_items[$qn]['total_score'];
                            }
                            // Q2 also saves drink_type
                            $saved_drink_type = isset($audit_items[$qn]['remark']) ? $audit_items[$qn]['remark'] : '';
                        ?>

                        <?php if ($q['type'] === 'q2'): ?>
                        <!-- Question 2: special multi-sub-row -->
                        <tr>
                            <td rowspan="3" style="padding: 8px 10px;">
                                <b><?= $q['text'] ?></b>
                            </td>
                            <?php foreach ([0,1,2,3,4] as $score): ?>
                            <td class="score-cell" rowspan="3">
                                <input type="radio"
                                    name="q[<?= $qn ?>][score]"
                                    value="<?= $score ?>"
                                    id="q<?= $qn ?>s<?= $score ?>"
                                    <?= ($saved_score === (string)$score) ? 'checked' : '' ?>
                                    onchange="updateTotal()">
                            </td>
                            <?php endforeach; ?>
                            <td class="text-center font-weight-bold" rowspan="3">
                                <span id="score_display_<?= $qn ?>" class="bg-theme-blue" style="font-size:1rem; padding:6px 10px; border-radius: 4px;">
                                    <?= ($saved_score !== null) ? $saved_score : '-' ?>
                                </span>
                                <input type="hidden" name="q[<?= $qn ?>][computed]" id="score_val_<?= $qn ?>"
                                    value="<?= ($saved_score !== null) ? $saved_score : '' ?>">
                            </td>
                        </tr>
                        <!-- Sub-labels row: Standard drinks -->
                        <tr>
                            <?php foreach ($q['options_std'] as $score => $label): ?>
                            <td class="score-cell" style="display:none;"></td>
                            <?php endforeach; ?>
                        </tr>
                        <!-- Show all three sub-row labels inside the cells above via overlay — use visible rows instead -->
                        <?php
                            // Actually render as a single row with stacked labels per score column
                            // We already output the radio in rowspan=3 above. Now show sub-labels in separate rows.
                        ?>
                        <tr>
                            <?php foreach ([0,1,2,3,4] as $score): ?>
                            <td class="score-cell" style="display:none;"></td>
                            <?php endforeach; ?>
                        </tr>
                        <!-- Visible sub-label rows (separate rows with no radio, just labels) -->
                        <tr style="background: #f8f8f8;">
                            <td style="padding: 4px 10px; font-size:0.8rem; color:#444;">
                                <i class="fas fa-circle" style="color:var(--bright-blue); font-size:0.6rem;"></i>
                                <b>ดื่มมาตรฐาน:</b>
                            </td>
                            <?php foreach ($q['options_std'] as $score => $label): ?>
                            <td class="score-cell" style="font-size:0.78rem; color:#555;"><?= $label ?></td>
                            <?php endforeach; ?>
                            <td></td>
                        </tr>
                        <tr style="background: var(--bright-blue-light);">
                            <td style="padding: 4px 10px; font-size:0.8rem; color:#444;">
                                <i class="fas fa-circle" style="color:var(--bright-blue); font-size:0.6rem;"></i>
                                <b>เบียร์ (กระป๋อง/ขวด):</b>
                            </td>
                            <?php foreach ($q['options_beer'] as $score => $label): ?>
                            <td class="score-cell" style="font-size:0.78rem; color:#555;"><?= $label ?></td>
                            <?php endforeach; ?>
                            <td></td>
                        </tr>
                        <tr style="background: #f8f8f8;">
                            <td style="padding: 4px 10px; font-size:0.8rem; color:#444;">
                                <i class="fas fa-circle" style="color:var(--bright-blue); font-size:0.6rem;"></i>
                                <b>เหล้า (เหล้าขาว 40°):</b>
                            </td>
                            <?php foreach ($q['options_liq'] as $score => $label): ?>
                            <td class="score-cell" style="font-size:0.78rem; color:#555;"><?= $label ?></td>
                            <?php endforeach; ?>
                            <td></td>
                        </tr>

                        <?php elseif ($q['type'] === 'q9q10'): ?>
                        <!-- Q9 & Q10: only 0, 2, 4 -->
                        <tr>
                            <td style="padding: 8px 10px;"><b><?= $q['text'] ?></b></td>
                            <!-- col 0 -->
                            <td class="score-cell">
                                <input type="radio" name="q[<?= $qn ?>][score]" value="0"
                                    id="q<?= $qn ?>s0"
                                    <?= ($saved_score === '0') ? 'checked' : '' ?>
                                    onchange="updateTotal()">
                                <label for="q<?= $qn ?>s0"><?= $q['options'][0] ?></label>
                            </td>
                            <!-- col 1 — empty (no score 1 for Q9/Q10) -->
                            <td class="score-cell" style="background:#f5f5f5; color:#bbb; font-size:0.75rem;">—</td>
                            <!-- col 2 -->
                            <td class="score-cell">
                                <input type="radio" name="q[<?= $qn ?>][score]" value="2"
                                    id="q<?= $qn ?>s2"
                                    <?= ($saved_score === '2') ? 'checked' : '' ?>
                                    onchange="updateTotal()">
                                <label for="q<?= $qn ?>s2"><?= $q['options'][2] ?></label>
                            </td>
                            <!-- col 3 — empty -->
                            <td class="score-cell" style="background:#f5f5f5; color:#bbb; font-size:0.75rem;">—</td>
                            <!-- col 4 -->
                            <td class="score-cell">
                                <input type="radio" name="q[<?= $qn ?>][score]" value="4"
                                    id="q<?= $qn ?>s4"
                                    <?= ($saved_score === '4') ? 'checked' : '' ?>
                                    onchange="updateTotal()">
                                <label for="q<?= $qn ?>s4"><?= $q['options'][4] ?></label>
                            </td>
                            <td class="text-center font-weight-bold">
                                <span id="score_display_<?= $qn ?>" class="bg-theme-blue" style="font-size:1rem; padding:6px 10px; border-radius: 4px;">
                                    <?= ($saved_score !== null) ? $saved_score : '-' ?>
                                </span>
                                <input type="hidden" name="q[<?= $qn ?>][computed]" id="score_val_<?= $qn ?>"
                                    value="<?= ($saved_score !== null) ? $saved_score : '' ?>">
                            </td>
                        </tr>

                        <?php else: ?>
                        <!-- Standard questions Q1, Q3-Q8 -->
                        <tr>
                            <td style="padding: 8px 10px;"><b><?= $q['text'] ?></b></td>
                            <?php foreach ($q['options'] as $score => $label): ?>
                            <td class="score-cell">
                                <input type="radio"
                                    name="q[<?= $qn ?>][score]"
                                    value="<?= $score ?>"
                                    id="q<?= $qn ?>s<?= $score ?>"
                                    <?= ($saved_score === (string)$score) ? 'checked' : '' ?>
                                    onchange="updateTotal()">
                                <label for="q<?= $qn ?>s<?= $score ?>"><?= $label ?></label>
                            </td>
                            <?php endforeach; ?>
                            <td class="text-center font-weight-bold">
                                <span id="score_display_<?= $qn ?>" class="bg-theme-blue" style="font-size:1rem; padding:6px 10px; border-radius: 4px;">
                                    <?= ($saved_score !== null) ? $saved_score : '-' ?>
                                </span>
                                <input type="hidden" name="q[<?= $qn ?>][computed]" id="score_val_<?= $qn ?>"
                                    value="<?= ($saved_score !== null) ? $saved_score : '' ?>">
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#E1F5FE;">
                            <td colspan="6" class="text-right font-weight-bold" style="padding: 10px;">
                                <span style="font-size:1rem;">คะแนนรวม AUDIT (0-40):</span>
                            </td>
                            <td class="text-center">
                                <div class="total-score-box" id="total_score_display">
                                    <?= isset($audit_row['sum_score']) ? $audit_row['sum_score'] : '0' ?>
                                </div>
                                <input type="hidden" name="sum_score" id="sum_score"
                                    value="<?= isset($audit_row['sum_score']) ? $audit_row['sum_score'] : 0 ?>">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Score Interpretation -->
            <div class="card mt-3" id="score_result_card">
                <div class="card-header font-weight-bold" style="background:var(--bright-blue); color:#fff;">
                    <i class="fas fa-chart-bar"></i> การแปลผลคะแนน AUDIT
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <div class="result-card result-low <?= (!isset($audit_row['sum_score']) || $audit_row['sum_score'] <= 7) ? 'border-theme-blue' : '' ?>">
                                <b>0-7 คะแนน</b><br>
                                <span>ผู้ดื่มแบบเสี่ยงต่ำ</span><br>
                                <small><i>Low risk drinker</i></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="result-card result-hazard">
                                <b>8-15 คะแนน</b><br>
                                <span>ผู้ดื่มแบบเสี่ยง</span><br>
                                <small><i>Hazardous drinker</i></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="result-card result-harm">
                                <b>16-19 คะแนน</b><br>
                                <span>ผู้ดื่มแบบอันตราย</span><br>
                                <small><i>Harmful use</i></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="result-card result-dep">
                                <b>≥20 คะแนน</b><br>
                                <span>ผู้ดื่มแบบติด</span><br>
                                <small><i>Alcohol dependence</i></small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3" id="result_text_box" style="font-size:1rem; font-weight:bold; text-align:center;"></div>
                </div>
            </div>

            <div class="row mt-4 mb-5">
                <div class="col text-center">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow"><i class="fas fa-save"></i>
                        บันทึกข้อมูล</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // AUDIT score questions (1-10)
    var auditQuestions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    function updateTotal() {
        var total = 0;
        auditQuestions.forEach(function (qn) {
            var selected = document.querySelector('input[name="q[' + qn + '][score]"]:checked');
            var scoreVal = selected ? parseInt(selected.value) : null;

            // Update per-question score display
            var display = document.getElementById('score_display_' + qn);
            var hidden  = document.getElementById('score_val_' + qn);
            if (display) {
                display.textContent = (scoreVal !== null) ? scoreVal : '-';
                display.className = 'bg-theme-blue';
                display.style.borderRadius = '4px';
            }
            if (hidden) {
                hidden.value = (scoreVal !== null) ? scoreVal : '';
            }

            if (scoreVal !== null) total += scoreVal;
        });

        // Update total display
        var totalDisplay = document.getElementById('total_score_display');
        var totalHidden  = document.getElementById('sum_score');
        if (totalDisplay) totalDisplay.textContent = total;
        if (totalHidden)  totalHidden.value = total;

        // Update result interpretation
        showResult(total);
    }

    function showResult(score) {
        var box = document.getElementById('result_text_box');
        if (!box) return;

        var resultCards = document.querySelectorAll('.result-card');
        resultCards.forEach(function (c) {
            c.style.opacity = '0.4';
            c.style.transform = 'scale(0.97)';
        });

        var msg = '', cls = '', cardIdx = -1;
        if (score <= 7)         { msg = '0-7 คะแนน — ผู้ดื่มแบบเสี่ยงต่ำ (Low risk drinker)';   cls = 'text-success'; cardIdx = 0; }
        else if (score <= 15)   { msg = '8-15 คะแนน — ผู้ดื่มแบบเสี่ยง (Hazardous drinker)';     cls = 'text-warning'; cardIdx = 1; }
        else if (score <= 20)   { msg = '16-19 คะแนน — ผู้ดื่มแบบอันตราย (Harmful use)';         cls = 'text-orange';  cardIdx = 2; }
        else                    { msg = '>20 คะแนน — ผู้ดื่มแบบติด (Alcohol dependence)';         cls = 'text-danger';  cardIdx = 3; }

        box.innerHTML = '<span class="' + cls + '">' + msg + '</span>';

        if (cardIdx >= 0 && resultCards[cardIdx]) {
            resultCards[cardIdx].style.opacity = '1';
            resultCards[cardIdx].style.transform = 'scale(1.03)';
            resultCards[cardIdx].style.boxShadow = '0 0 8px rgba(0,0,0,0.2)';
        }
    }

    $("#audit_form").on("submit", function (e) {
        e.preventDefault();

        // Check all questions answered
        var unanswered = [];
        auditQuestions.forEach(function (qn) {
            var selected = document.querySelector('input[name="q[' + qn + '][score]"]:checked');
            if (!selected) unanswered.push(qn);
        });

        if (unanswered.length > 0) {
            Swal.fire('กรุณาตอบคำถามให้ครบ', 'ยังไม่ได้ตอบข้อที่: ' + unanswered.join(', '), 'warning');
            return;
        }

        var formData = $(this).serialize();
        var id  = $("input[name='id']").val();
        var url = id ? "form-alcohol-update.php" : "form-alcohol-save.php";

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function (resp) {
                var data = JSON.parse(resp);
                if (data.status === "success") {
                    // อัปเดต hidden id เพื่อให้ครั้งต่อไป submit จะใช้ update แทน save
                    if (data.id) {
                        $("input[name='id']").val(data.id);
                    }
                    Swal.fire("สำเร็จ", "บันทึกข้อมูลเรียบร้อยแล้ว", "success").then(function () {

                        //if (window.opener) window.opener.location.reload();
                        //window.close();
                        window.location.reload(true);
                    });
                } else {
                    Swal.fire("ข้อผิดพลาด", data.message, "error");
                }
            },
            error: function () {
                Swal.fire("ข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            }
        });
    });

    // On page load: calculate score from saved values
    window.onload = function () {
        updateTotal();
    };
</script>