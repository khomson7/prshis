<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck     = Session::checkPermissionAndShowMessage('OPNOTE', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

$conn = DbUtils::get_hosxp_connection();
$an   = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';
$hn   = KphisQueryUtils::getHnByAn($an);

Session::insertSystemAccessLog(json_encode([
    'form' => 'OPERATIVE-NOTE-FORM',
    'an'   => $an,
], JSON_UNESCAPED_UNICODE));

$ids     = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
if (!$ids) $ids = null;   // 0 → new record

$rec = null;
$items = [];
$canEdit = true;
$created_by = '';

if ($ids) {
    $stmt_m = $conn->prepare("SELECT * FROM prs_operative_note WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt_m->execute(['id' => $ids, 'an' => $an]);
    $rec = $stmt_m->fetch(PDO::FETCH_ASSOC);
    if (!$rec) {
        $ids = null;
    } else {
        $created_by  = $rec['created_by'] ?? '';
        $canEdit = ($loginname === $created_by);
        
        $stmt_i = $conn->prepare("SELECT id, sort_order, image_type, original_name,
                                          canvas_w, canvas_h, svg_data, image_data
                                    FROM prs_operative_note_item
                                   WHERE annot_id = :annot_id
                                   ORDER BY sort_order ASC");
        $stmt_i->execute(['annot_id' => $ids]);
        while ($row_i = $stmt_i->fetch(PDO::FETCH_ASSOC)) {
            $bin = $row_i['image_data'];
            if (is_resource($bin)) $bin = stream_get_contents($bin);
            $mime = $row_i['image_type'] ?: 'image/png';
            $row_i['image_b64'] = $bin ? ('data:' . $mime . ';base64,' . base64_encode($bin)) : '';
            unset($row_i['image_data']);
            $items[] = $row_i;
        }
    }
}

$session_name = isset($_SESSION['name']) ? $_SESSION['name'] : $loginname;
?>
<script src="../include/fabric.js"></script>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
<style>
.form-label { font-weight: bold; margin-top: 10px; }
.card-header { background-color: #f8f9fa; }

/* ---- Canvas zone ---- */
#canvas-wrap {
    position: relative;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    background: #f8f9fa;
    overflow: hidden;
    min-height: 200px;
}
#canvas-wrap canvas { display: block; }
#canvas-placeholder {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    color: #adb5bd; font-size: 14px; pointer-events: none;
}

/* ---- Toolbar ---- */
#draw-toolbar {
    display: flex; gap: 6px; flex-wrap: wrap;
    align-items: center; margin-bottom: 8px;
}
.tb-btn {
    height: 34px; padding: 0 12px;
    border: 1px solid #ced4da; border-radius: 5px;
    background: #fff; cursor: pointer; font-size: 13px;
    display: flex; align-items: center; gap: 4px;
    transition: .15s;
}
.tb-btn:hover  { background: #e9ecef; }
.tb-btn.active { background: #1a6b3a; border-color: #1a6b3a; color: #fff; }
.tb-sep { width: 1px; height: 24px; background: #dee2e6; margin: 0 2px; }

/* ---- Thumbnail strip ---- */
#thumb-strip {
    display: flex; gap: 8px; flex-wrap: wrap;
    padding: 8px; background: #f1f3f5;
    border: 1px solid #dee2e6; border-radius: 6px;
    min-height: 72px; margin-bottom: 10px;
}
.thumb-item {
    position: relative; cursor: pointer;
    border: 3px solid transparent; border-radius: 5px;
    overflow: hidden; width: 80px; height: 60px;
    background: #dee2e6;
    transition: .15s;
}
.thumb-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.thumb-item.active { border-color: #1a6b3a; }
.thumb-item .thumb-del {
    position: absolute; top: 2px; right: 2px;
    background: rgba(220,53,69,.85); color: #fff;
    border: none; border-radius: 3px; width: 18px; height: 18px;
    font-size: 11px; cursor: pointer; line-height: 18px; text-align: center;
    display: none;
}
.thumb-item:hover .thumb-del { display: block; }
.thumb-num {
    position: absolute; bottom: 2px; left: 3px;
    font-size: 10px; font-weight: 700; color: #fff;
    background: rgba(0,0,0,.45); border-radius: 3px; padding: 0 3px;
}

/* ---- Drop zone ---- */
#drop-zone {
    border: 2px dashed #adb5bd; border-radius: 6px;
    padding: 16px; text-align: center; color: #6c757d;
    cursor: pointer; background: #fff; transition: .2s;
    font-size: 13px;
}
#drop-zone:hover, #drop-zone.drag-over {
    border-color: #1a6b3a; background: #f0faf4;
}
</style>

<div id="formContainer">
<form id="operative_form">
    <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
    <input type="hidden" name="hn" value="<?= htmlspecialchars($hn) ?>">
    <input type="hidden" name="id" id="rec_id" value="<?= htmlspecialchars($ids ?? '') ?>">

    <div class="container-fluid mb-5">

        <div class="row align-items-center mb-3">
            <div class="col-auto">
                <a href="form-operative-main.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> กลับหน้าหลัก
                </a>
            </div>
            <div class="col">
                <h5 class="mb-0">
                    <b>บันทึกข้อมูล Operative Note</b>
                    <?php if ($ids): ?>
                    <span class="badge badge-secondary ml-1" style="font-size:0.75rem;">#<?= $ids ?></span>
                    <?php else: ?>
                    <span class="badge badge-success ml-1" style="font-size:0.75rem;">ใหม่</span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="col-auto">
                <?php if ($ids): ?>
                <a href="../pdffile/operative-pdf.php?an=<?= urlencode($an) ?>&id=<?= $ids ?>&loginname=<?= urlencode($loginname) ?>"
                   target="_blank" class="btn btn-sm btn-info px-3 shadow-sm">
                    <i class="fas fa-file-pdf"></i> พิมพ์ PDF
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($ids && !$canEdit): ?>
        <div class="alert alert-warning d-flex align-items-center mb-3" style="font-size:14px">
            <i class="fas fa-lock mr-2 fa-lg"></i>
            <div>
                <b>ไม่สามารถแก้ไขได้</b> — บันทึกโดย <b><?= htmlspecialchars($created_by) ?></b>
                &nbsp;เฉพาะผู้บันทึกเท่านั้นที่สามารถแก้ไขรายการนี้ได้
            </div>
        </div>
        <?php endif; ?>



        <div class="card mb-3">
            <div class="card-header font-weight-bold py-2">
                <i class="fas fa-file-medical"></i> ข้อมูลการผ่าตัด
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Date of operation</label>
                        <input type="date" class="form-control" name="operation_date" 
                               value="<?= htmlspecialchars($rec['operation_date'] ?? date('Y-m-d')) ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Time started</label>
                        <input type="time" class="form-control" name="time_started" 
                               value="<?= htmlspecialchars($rec['time_started'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Time end</label>
                        <input type="time" class="form-control" name="time_ended" 
                               value="<?= htmlspecialchars($rec['time_ended'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Surgeon</label>
                        <div class="input-group">
                            <select class="form-control" name="surgeon[]" id="inp_surgeon" multiple="multiple" <?= !$canEdit ? 'disabled' : '' ?> style="width:100%">
                                <?php 
                                $surgeons = [];
                                if (!empty($rec['surgeon'])) {
                                    $surgeons = json_decode($rec['surgeon'], true);
                                    if (!is_array($surgeons)) {
                                        $surgeons = [$rec['surgeon']];
                                    }
                                }
                                foreach ($surgeons as $s) {
                                    echo '<option value="'.htmlspecialchars($s).'" selected>'.htmlspecialchars($s).'</option>';
                                }
                                ?>
                            </select>
                            <?php if (!$canEdit): ?>
                                <?php foreach ($surgeons as $s): ?>
                                    <input type="hidden" name="surgeon[]" value="<?= htmlspecialchars($s) ?>">
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($canEdit): ?>
                            <div class="input-group-append">
                                <button class="btn btn-outline-success" type="button" onclick="signField('inp_surgeon')" title="ลงชื่ออัตโนมัติ">
                                    <i class="fas fa-signature"></i> SIGN
                                </button>
                                <button class="btn btn-outline-secondary" type="button" onclick="clearField('inp_surgeon')" title="ล้าง">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First assistant</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="first_assistant" id="inp_first_assistant"
                                   value="<?= htmlspecialchars($rec['first_assistant'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                            <?php if ($canEdit): ?>
                            <div class="input-group-append">
                                <button class="btn btn-outline-success" type="button" onclick="signField('inp_first_assistant')" title="ลงชื่ออัตโนมัติ">
                                    <i class="fas fa-signature"></i> SIGN
                                </button>
                                <button class="btn btn-outline-secondary" type="button" onclick="clearField('inp_first_assistant')" title="ล้าง">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Second assistant</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="second_assistant" id="inp_second_assistant"
                                   value="<?= htmlspecialchars($rec['second_assistant'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                            <?php if ($canEdit): ?>
                            <div class="input-group-append">
                                <button class="btn btn-outline-success" type="button" onclick="signField('inp_second_assistant')" title="ลงชื่ออัตโนมัติ">
                                    <i class="fas fa-signature"></i> SIGN
                                </button>
                                <button class="btn btn-outline-secondary" type="button" onclick="clearField('inp_second_assistant')" title="ล้าง">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Surgical nurse</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="surgical_nurse" id="inp_surgical_nurse"
                                   value="<?= htmlspecialchars($rec['surgical_nurse'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                            <?php if ($canEdit): ?>
                            <div class="input-group-append">
                                <button class="btn btn-outline-success" type="button" onclick="signField('inp_surgical_nurse')" title="ลงชื่ออัตโนมัติ">
                                    <i class="fas fa-signature"></i> SIGN
                                </button>
                                <button class="btn btn-outline-secondary" type="button" onclick="clearField('inp_surgical_nurse')" title="ล้าง">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Clinical diagnosis</label>
                        <textarea class="form-control" name="clinical_diagnosis" rows="2" <?= !$canEdit ? 'readonly' : '' ?>><?= htmlspecialchars($rec['clinical_diagnosis'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Post operation diagnosis</label>
                        <textarea class="form-control" name="post_op_diagnosis" rows="2" <?= !$canEdit ? 'readonly' : '' ?>><?= htmlspecialchars($rec['post_op_diagnosis'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <label class="form-label mb-0">Operation</label>
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="openTemplateModal()">
                                <i class="fas fa-cog"></i> ตั้งค่า Template
                            </button>
                            <?php endif; ?>
                        </div>
                        <select class="form-control" name="operation_name" id="inp_operation_name" <?= !$canEdit ? 'disabled' : '' ?> style="width:100%">
                            <?php if (!empty($rec['operation_name'])): ?>
                                <option value="<?= htmlspecialchars($rec['operation_name']) ?>" selected><?= htmlspecialchars($rec['operation_name']) ?></option>
                            <?php endif; ?>
                        </select>
                        <?php if (!$canEdit): ?>
                            <input type="hidden" name="operation_name" value="<?= htmlspecialchars($rec['operation_name'] ?? '') ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Anesthetic techniques</label>
                        <input type="text" class="form-control" name="anesthetic_technique" 
                               value="<?= htmlspecialchars($rec['anesthetic_technique'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Anesthesiologist</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="anesthesiologist" id="inp_anesthesiologist"
                                   value="<?= htmlspecialchars($rec['anesthesiologist'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                            <?php if ($canEdit): ?>
                            <div class="input-group-append">
                                <button class="btn btn-outline-success" type="button" onclick="signField('inp_anesthesiologist')" title="ลงชื่ออัตโนมัติ">
                                    <i class="fas fa-signature"></i> SIGN
                                </button>
                                <button class="btn btn-outline-secondary" type="button" onclick="clearField('inp_anesthesiologist')" title="ล้าง">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header font-weight-bold py-2">
                <i class="fas fa-notes-medical"></i> DESCRIPTIVE OF OPERATION
            </div>
            <div class="card-body">

                <!-- ---- Upload zone ---- -->
                <div class="card mb-3" style="border: 1px dashed #ced4da; box-shadow: none;">
                    <div class="card-header bg-light font-weight-bold py-2">
                        <i class="fas fa-images"></i> ภาพการผ่าตัด
                    </div>
                    <div class="card-body">
                        <div id="thumb-strip">
                            <span id="thumb-empty" class="text-muted small" style="align-self:center">
                                ยังไม่มีภาพ — เลือกรายการผ่าตัดเพื่อดึง Template หรือเพิ่มภาพเอง
                            </span>
                        </div>
                        <?php if ($canEdit): ?>
                        <div id="drop-zone" onclick="document.getElementById('file-input').click()">
                            <i class="fas fa-camera fa-lg mr-1"></i>
                            คลิกหรือลากไฟล์ภาพมาวางที่นี่ (เพิ่มได้ครั้งละหลายภาพ)<br>
                            <small class="text-muted">PNG, JPG, WEBP | แต่ละภาพไม่เกิน 10 MB</small>
                        </div>
                        <input type="file" id="file-input" accept="image/*" multiple style="display:none">
                        <?php else: ?>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-eye mr-1"></i> โหมดดูอย่างเดียว (View only)
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ---- Canvas Editor ---- -->
                <div class="card mb-3" id="editor-card" style="display:none; border: 1px dashed #ced4da; box-shadow: none;">
                    <div class="card-header bg-light font-weight-bold py-2 d-flex align-items-center">
                        <i class="fas fa-paint-brush mr-2"></i>
                        <?= $canEdit ? 'วาด Annotation' : 'ดูภาพ' ?> — ภาพที่ <span id="editing-num">1</span>
                        <span id="editing-name" class="text-muted small ml-2"></span>
                    </div>
                    <div class="card-body">
                        <div id="draw-toolbar" <?= !$canEdit ? 'style="display:none"' : '' ?>>
                            <button type="button" class="tb-btn active" id="btn-pen" onclick="setTool('pen')" title="ปากกาวาดอิสระ">
                                <i class="fas fa-pen"></i> ปากกา
                            </button>
                            <button type="button" class="tb-btn" id="btn-line" onclick="setTool('line')" title="เส้นตรง">
                                <i class="fas fa-minus"></i> เส้น
                            </button>
                            <button type="button" class="tb-btn" id="btn-arrow" onclick="setTool('arrow')" title="ลูกศร">
                                &#10148; ลูกศร
                            </button>
                            <button type="button" class="tb-btn" id="btn-rect" onclick="setTool('rect')" title="สี่เหลี่ยม">
                                <i class="far fa-square"></i> กล่อง
                            </button>
                            <button type="button" class="tb-btn" id="btn-circle" onclick="setTool('circle')" title="วงรี">
                                <i class="far fa-circle"></i> วงกลม
                            </button>
                            <button type="button" class="tb-btn" id="btn-text" onclick="setTool('text')" title="พิมพ์ข้อความ (ดับเบิ้ลคลิก)">
                                <i class="fas fa-font"></i> ข้อความ
                            </button>
                            <div class="tb-sep"></div>
                            <label class="tb-btn" style="cursor:pointer" title="สี">
                                <i class="fas fa-palette"></i>
                                <input type="color" id="colorPicker" value="#e63946" style="width:24px;height:22px;border:none;padding:0;cursor:pointer">
                            </label>
                            <label class="tb-btn" title="ขนาดเส้น" style="gap:6px">
                                <i class="fas fa-sliders-h"></i>
                                <input type="range" id="strokeWidth" min="1" max="20" value="3" style="width:65px">
                                <span id="strokeVal">3</span>
                            </label>
                            <div class="tb-sep"></div>
                            <button type="button" class="tb-btn" onclick="undoCanvas()" title="Undo">
                                <i class="fas fa-undo"></i> Undo
                            </button>
                            <button type="button" class="tb-btn" onclick="clearCanvas()" title="ล้างการวาดทั้งหมด">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                        </div>
                        <div id="canvas-wrap">
                            <div id="canvas-placeholder">เลือกภาพจาก thumbnail ด้านบนเพื่อเริ่มวาด</div>
                            <canvas id="c"></canvas>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-info-circle"></i> เลือก tool "ข้อความ" แล้ว <b>ดับเบิ้ลคลิก</b> บนภาพเพื่อพิมพ์ข้อความ
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" name="op_position" 
                               value="<?= htmlspecialchars($rec['op_position'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Incision</label>
                        <input type="text" class="form-control" name="incision" 
                               value="<?= htmlspecialchars($rec['incision'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Finding</label>
                        <textarea class="form-control" name="finding" rows="3" <?= !$canEdit ? 'readonly' : '' ?>><?= htmlspecialchars($rec['finding'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Procedure</label>
                        <textarea class="form-control" name="procedure_detail" rows="6" <?= !$canEdit ? 'readonly' : '' ?>><?= htmlspecialchars($rec['procedure_detail'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header font-weight-bold py-2">
                <i class="fas fa-info-circle"></i> ข้อมูลเพิ่มเติม
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Estimate blood loss (ml)</label>
                        <input type="text" class="form-control" name="estimate_blood_loss" 
                               value="<?= htmlspecialchars($rec['estimate_blood_loss'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Urine output (ml)</label>
                        <input type="text" class="form-control" name="urine_output" 
                               value="<?= htmlspecialchars($rec['urine_output'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label d-block">ส่ง Patho</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="patho_status" id="patho_1" value="ส่ง Patho" <?= (isset($rec['patho_status']) && $rec['patho_status'] == 'ส่ง Patho') ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="patho_1">ส่ง Patho</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="patho_status" id="patho_2" value="ไม่ส่ง Patho" <?= (isset($rec['patho_status']) && $rec['patho_status'] == 'ไม่ส่ง Patho') ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="patho_2">ไม่ส่ง Patho</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">ประเภทบาดแผล</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="wound_type" id="wound_1" value="Clean wound" <?= (isset($rec['wound_type']) && $rec['wound_type'] == 'Clean wound') ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="wound_1">Clean wound</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="wound_type" id="wound_2" value="Clean contaminate wound" <?= (isset($rec['wound_type']) && $rec['wound_type'] == 'Clean contaminate wound') ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="wound_2">Clean contaminate wound</label>
                        </div>
                        <div class="form-check form-check-inline mt-2">
                            <input class="form-check-input" type="radio" name="wound_type" id="wound_3" value="Contaminate wound" <?= (isset($rec['wound_type']) && $rec['wound_type'] == 'Contaminate wound') ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="wound_3">Contaminate wound</label>
                        </div>
                        <div class="form-check form-check-inline mt-2">
                            <input class="form-check-input" type="radio" name="wound_type" id="wound_4" value="Dirty wound" <?= (isset($rec['wound_type']) && $rec['wound_type'] == 'Dirty wound') ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="wound_4">Dirty wound</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col text-center"> 
                <!--DEBUG BLOCK FOR USER TO SEE WHAT FAILS --> 
                <!--<div class="alert alert-warning text-left d-inline-block" style="font-size: 12px;">
                    <b>สถานะการตรวจสอบปุ่มลบ (Debug):</b><br>
                    1. เป็นเอกสารเก่าใช่หรือไม่ (มี ID): <b><?= !empty($rec['id']) ? 'ผ่าน (YES)' : 'ไม่ผ่าน (NO)' ?></b><br>
                    2. คนล็อกอินคือคนสร้าง หรือเป็น admin: <b><?= (!empty($rec) && ($rec['created_by'] === $loginname || strtolower($loginname) === 'admin')) ? 'ผ่าน (YES)' : 'ไม่ผ่าน (NO)' ?></b> (ผู้สร้าง: <?= htmlspecialchars($rec['created_by'] ?? 'ไม่มี') ?>, ล็อกอิน: <?= htmlspecialchars($loginname) ?>)<br>
                    3. มีสิทธิ์ OPNOTE -> REMOVE: <b><?= Session::checkPermission('OPNOTE', 'REMOVE') ? 'ผ่าน (YES)' : 'ไม่ผ่าน (NO)' ?></b><br>
                    4. คนไข้ยังไม่ถูกล็อก (Read-Only): <b><?= ReportQueryUtils::checkReadOnly($an) ? 'ผ่าน (YES)' : 'ไม่ผ่าน (NO - ล็อกแล้ว)' ?></b><br>
                    <i>* ปุ่มลบจะแสดงก็ต่อเมื่อทั้ง 4 ข้อขึ้นว่า "ผ่าน" ทั้งหมดครับ</i>
                </div>
                <br> -->
                
                <?php if ($canEdit && Session::checkPermission('OPNOTE', 'EDIT') && ReportQueryUtils::checkReadOnly($an)): ?>
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow" id="btn-save">
                    <i class="fas fa-save"></i> บันทึกข้อมูล
                </button>
                <?php endif; ?>
                
                <?php if (!empty($rec['id']) && ($rec['created_by'] === $loginname || strtolower($loginname) === 'admin') && Session::checkPermission('OPNOTE', 'REMOVE') && ReportQueryUtils::checkReadOnly($an)): ?>
                <button type="button" class="btn btn-danger btn-lg px-4 shadow ml-3" onclick="deleteOpNote(<?= $rec['id'] ?>)">
                    <i class="fas fa-trash"></i> ลบเอกสาร
                </button>
                <?php endif; ?>
            </div>
        </div> 

    </div>
</form>
    </div>
</form>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-cog"></i> ตั้งค่า Template ภาพผ่าตัด</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="templateForm">
                    <input type="hidden" id="tpl_id" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ชื่อรายการผ่าตัด</label>
                                <input type="text" class="form-control" id="tpl_operation_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>เลือกไฟล์รูปภาพ (Template)</label>
                                <input type="file" class="form-control-file" id="tpl_image" accept="image/*">
                                <small class="text-muted">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรูป</small>
                            </div>
                        </div>
                        
                        <div class="col-md-12"><hr class="my-2"><h6><b>ข้อมูลเริ่มต้นของ Template</b></h6></div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="mb-0 text-muted"><small>Clinical diagnosis</small></label>
                                <textarea class="form-control form-control-sm" id="tpl_clinical_diagnosis" rows="2"></textarea>
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-0 text-muted"><small>Post operation diagnosis</small></label>
                                <textarea class="form-control form-control-sm" id="tpl_post_op_diagnosis" rows="2"></textarea>
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-0 text-muted"><small>Anesthetic techniques</small></label>
                                <input type="text" class="form-control form-control-sm" id="tpl_anesthetic_technique">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="mb-0 text-muted"><small>Position</small></label>
                                <input type="text" class="form-control form-control-sm" id="tpl_op_position">
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-0 text-muted"><small>Incision</small></label>
                                <input type="text" class="form-control form-control-sm" id="tpl_incision">
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-0 text-muted"><small>Finding</small></label>
                                <textarea class="form-control form-control-sm" id="tpl_finding" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="mb-0 text-muted"><small>Procedure detail</small></label>
                                <textarea class="form-control form-control-sm" id="tpl_procedure_detail" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> บันทึก Template</button>
                </form>
                <hr>
                <h6>รายการ Template ของคุณ</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr><th>ชื่อการผ่าตัด</th><th>วันที่สร้าง</th><th width="220">จัดการ</th></tr>
                        </thead>
                        <tbody id="templateListTbody">
                            <!-- Data -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var _sessionName = <?= json_encode($session_name) ?>;
function signField(fieldId) {
    var el = $('#' + fieldId);
    if (el.is('select') && el.prop('multiple')) {
        var current = el.val() || [];
        if (!current.includes(_sessionName)) {
            var newOption = new Option(_sessionName, _sessionName, true, true);
            el.append(newOption).trigger('change');
        }
    } else {
        var domEl = document.getElementById(fieldId);
        if (domEl && !domEl.value.trim()) { domEl.value = _sessionName; }
    }
}
function clearField(fieldId) {
    var el = $('#' + fieldId);
    if (el.is('select') && el.prop('multiple')) {
        el.val(null).trigger('change');
    } else {
        var domEl = document.getElementById(fieldId);
        if (domEl) domEl.value = '';
    }
}

var imageList = [];
var currentIdx = -1;
var canvas;
var currentTool = 'pen';
var isDown = false;
var startX, startY, activeShape;
var undoStack = [];
var bgImageObj = null;
var _savingUndo = false;
var _canEdit = <?= $canEdit ? 'true' : 'false' ?>;

<?php if (!empty($items)): ?>
(function() {
    var serverItems = <?= json_encode(array_map(function($it) {
        return [
            'b64'    => $it['image_b64'],
            'svgData'=> $it['svg_data']     ?? '',
            'name'   => $it['original_name'] ?? '',
            'itemId' => $it['id'],
            'canvasW'=> (int)($it['canvas_w'] ?? 0),
            'canvasH'=> (int)($it['canvas_h'] ?? 0),
        ];
    }, $items)) ?>;
    serverItems.forEach(function(item) {
        imageList.push({
            b64:         item.b64,
            svgData:     item.svgData,
            name:        item.name,
            itemId:      item.itemId,
            canvasW:     item.canvasW || 0,
            canvasH:     item.canvasH || 0,
            annotatedB64: ''
        });
    });
    renderThumbs();
    if (imageList.length > 0) selectImage(0);
})();
<?php endif; ?>

var _fileInput = document.getElementById('file-input');
if (_fileInput) {
    _fileInput.addEventListener('change', function(e) {
        if (!_canEdit) return;
        handleFiles(Array.from(e.target.files));
        this.value = '';
    });
}
var dz = document.getElementById('drop-zone');
if (dz) {
    dz.addEventListener('dragover',  function(e){ if (!_canEdit) return; e.preventDefault(); dz.classList.add('drag-over'); });
    dz.addEventListener('dragleave', function()  { dz.classList.remove('drag-over'); });
    dz.addEventListener('drop', function(e) {
        if (!_canEdit) return;
        e.preventDefault(); dz.classList.remove('drag-over');
        handleFiles(Array.from(e.dataTransfer.files).filter(function(f){ return f.type.startsWith('image/'); }));
    });
}

function handleFiles(files) {
    files.forEach(function(file) {
        if (file.size > 10 * 1024 * 1024) {
            Swal.fire('ไฟล์ใหญ่เกินไป', file.name + ' ขนาดเกิน 10 MB', 'warning');
            return;
        }
        var r = new FileReader();
        r.onload = function(e) {
            imageList.push({ b64: e.target.result, svgData: '', name: file.name, itemId: null });
            renderThumbs();
            if (imageList.length === 1) selectImage(0);
        };
        r.readAsDataURL(file);
    });
}

function renderThumbs() {
    var strip = document.getElementById('thumb-strip');
    strip.innerHTML = '';
    if (imageList.length === 0) {
        strip.innerHTML = '<span id="thumb-empty" class="text-muted small" style="align-self:center">ยังไม่มีภาพ</span>';
        return;
    }
    imageList.forEach(function(img, idx) {
        var div  = document.createElement('div');
        div.className = 'thumb-item' + (idx === currentIdx ? ' active' : '');
        div.dataset.idx = idx;
        div.onclick = function() { selectImage(idx); };

        var im   = document.createElement('img');
        im.src   = img.b64;
        im.alt   = img.name;

        var num  = document.createElement('span');
        num.className = 'thumb-num';
        num.textContent = idx + 1;

        var del  = document.createElement('button');
        del.type = 'button';
        del.className = 'thumb-del';
        del.innerHTML = '&times;';
        if (_canEdit) {
            del.onclick = function(e) { e.stopPropagation(); removeImage(idx); };
        } else {
            del.style.display = 'none';
        }

        div.appendChild(im);
        div.appendChild(num);
        div.appendChild(del);
        strip.appendChild(div);
    });
}

function removeImage(idx) {
    if (currentIdx === idx) {
        saveSvgToCurrent();
        currentIdx = -1;
        if (canvas) { canvas.dispose(); canvas = null; }
        document.getElementById('editor-card').style.display = 'none';
        document.getElementById('canvas-placeholder').style.display = '';
    }
    imageList.splice(idx, 1);
    if (currentIdx > idx) currentIdx--;
    renderThumbs();
}

function selectImage(idx) {
    if (currentIdx >= 0 && currentIdx !== idx && canvas) { saveSvgToCurrent(); }
    currentIdx = idx;
    renderThumbs();

    var img = imageList[idx];
    document.getElementById('editor-card').style.display = '';
    document.getElementById('editing-num').textContent = idx + 1;
    document.getElementById('editing-name').textContent = img.name ? ('(' + img.name + ')') : '';

    fabric.Image.fromURL(img.b64, function(fimg) {
        var maxW  = document.getElementById('canvas-wrap').clientWidth || 700;
        var w, h, scale;
        if (img.canvasW && img.canvasH) {
            w = img.canvasW; h = img.canvasH; scale = w / fimg.width;
        } else {
            scale = Math.min(1, maxW / fimg.width);
            w = Math.round(fimg.width  * scale);
            h = Math.round(fimg.height * scale);
        }

        if (canvas) canvas.dispose();
        canvas = new fabric.Canvas('c', {
            width: w, height: h, isDrawingMode: _canEdit, selection: false, preserveObjectStacking: true
        });
        applyBrush();
        bindCanvasEvents();
        undoStack = [];

        fimg.set({ scaleX: scale, scaleY: scale, selectable: false, evented: false, excludeFromExport: true });
        bgImageObj = fimg;
        canvas.add(fimg);
        canvas.sendToBack(fimg);

        if (img.svgData && img.svgData.trim() !== '') {
            fabric.loadSVGFromString(img.svgData, function(objects) {
                if (objects && objects.length > 0) {
                    objects.forEach(function(obj) {
                        if (!obj || obj.type === 'image') return;
                        canvas.add(obj);
                    });
                }
                canvas.renderAll();
                saveUndo();
            });
        } else {
            canvas.renderAll();
            saveUndo();
        }

        document.getElementById('canvas-placeholder').style.display = 'none';
        setTool(currentTool);
    });
}

function saveSvgToCurrent() {
    if (currentIdx < 0 || !canvas) return;
    imageList[currentIdx].svgData = canvas.toSVG();
    imageList[currentIdx].canvasW = canvas.getWidth();
    imageList[currentIdx].canvasH = canvas.getHeight();
    try {
        imageList[currentIdx].annotatedB64 = canvas.toDataURL({ format: 'png', multiplier: 1 });
    } catch(e) {
        imageList[currentIdx].annotatedB64 = '';
    }
}

function setTool(tool) {
    currentTool = tool;
    document.querySelectorAll('.tb-btn[id^="btn-"]').forEach(function(b){ b.classList.remove('active'); });
    var btn = document.getElementById('btn-' + tool);
    if (btn) btn.classList.add('active');
    if (!canvas) return;
    canvas.isDrawingMode = (tool === 'pen');
    canvas.selection     = (tool === 'select');
    if (tool === 'pen') applyBrush();
}

function applyBrush() {
    if (!canvas) return;
    var color = document.getElementById('colorPicker').value;
    var width = parseInt(document.getElementById('strokeWidth').value);
    canvas.freeDrawingBrush.color = color;
    canvas.freeDrawingBrush.width = width;
}

document.getElementById('strokeWidth').addEventListener('input', function(){
    document.getElementById('strokeVal').textContent = this.value;
    applyBrush();
});
document.getElementById('colorPicker').addEventListener('input', applyBrush);

function bindCanvasEvents() {
    canvas.on('mouse:down', function(opt) {
        var tools = ['line','arrow','rect','circle'];
        if (tools.indexOf(currentTool) === -1) return;
        isDown = true;
        var ptr = canvas.getPointer(opt.e);
        startX = ptr.x; startY = ptr.y;
        var color = document.getElementById('colorPicker').value;
        var sw = parseInt(document.getElementById('strokeWidth').value);

        if (currentTool === 'line' || currentTool === 'arrow') {
            activeShape = new fabric.Line([startX,startY,startX,startY], { stroke: color, strokeWidth: sw, selectable: true });
        } else if (currentTool === 'rect') {
            activeShape = new fabric.Rect({ left:startX, top:startY, width:0, height:0, fill:'transparent', stroke:color, strokeWidth:sw, selectable:true });
        } else if (currentTool === 'circle') {
            activeShape = new fabric.Ellipse({ left:startX, top:startY, rx:0, ry:0, fill:'transparent', stroke:color, strokeWidth:sw, selectable:true });
        }
        if (activeShape) canvas.add(activeShape);
    });

    canvas.on('mouse:move', function(opt) {
        if (!isDown || !activeShape) return;
        var ptr = canvas.getPointer(opt.e);
        if (currentTool==='line'||currentTool==='arrow') {
            activeShape.set({ x2:ptr.x, y2:ptr.y });
        } else if (currentTool==='rect') {
            activeShape.set({ width:Math.abs(ptr.x-startX), height:Math.abs(ptr.y-startY), left:Math.min(ptr.x,startX), top:Math.min(ptr.y,startY) });
        } else if (currentTool==='circle') {
            activeShape.set({ rx:Math.abs(ptr.x-startX)/2, ry:Math.abs(ptr.y-startY)/2, left:Math.min(ptr.x,startX), top:Math.min(ptr.y,startY) });
        }
        canvas.renderAll();
    });

    canvas.on('mouse:up', function() { isDown=false; activeShape=null; saveUndo(); });

    canvas.on('mouse:dblclick', function(opt) {
        if (currentTool !== 'text') return;
        var ptr = canvas.getPointer(opt.e);
        var color = document.getElementById('colorPicker').value;
        var sw = parseInt(document.getElementById('strokeWidth').value);
        var txt = new fabric.IText('ข้อความ', { left:ptr.x, top:ptr.y, fontSize: 14+sw*2, fill:color, fontFamily:'Arial', selectable:true, editable:true });
        canvas.add(txt); canvas.setActiveObject(txt); txt.enterEditing(); canvas.renderAll();
    });

    canvas.on('object:added', function() { if (!_savingUndo) saveUndo(); });
    canvas.on('object:modified', function() { if (!_savingUndo) saveUndo(); });
}

function saveUndo() {
    if (!canvas || _savingUndo) return;
    _savingUndo = true;
    if (bgImageObj) canvas.remove(bgImageObj);
    var s = JSON.stringify(canvas.toJSON());
    if (bgImageObj) { canvas.add(bgImageObj); canvas.sendToBack(bgImageObj); }
    _savingUndo = false;
    if (undoStack[undoStack.length - 1] !== s) undoStack.push(s);
    if (undoStack.length > 50) undoStack.shift();
}

function undoCanvas() {
    if (!canvas || undoStack.length <= 1) return;
    undoStack.pop();
    _savingUndo = true;
    canvas.loadFromJSON(undoStack[undoStack.length - 1], function() {
        if (bgImageObj) { canvas.add(bgImageObj); canvas.sendToBack(bgImageObj); }
        _savingUndo = false; canvas.renderAll();
    });
}

function clearCanvas() {
    if (!canvas) return;
    Swal.fire({
        title: 'ล้างการวาดทั้งหมด?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'ล้าง', cancelButtonText: 'ยกเลิก',
    }).then(function(r) {
        if (!r.isConfirmed) return;
        _savingUndo = true;
        var toRemove = canvas.getObjects().filter(function(o) { return o !== bgImageObj; });
        toRemove.forEach(function(o) { canvas.remove(o); });
        canvas.renderAll();
        _savingUndo = false; undoStack = []; saveUndo();
    });
}

function combineAllImages(callback) {
    if (imageList.length === 0) { callback(null); return; }
    var MAX_W = 1400; var GAP = 6; var COLS = imageList.length === 1 ? 1 : 2;
    var col_w = Math.floor((MAX_W - (COLS - 1) * GAP) / COLS);
    var elems = new Array(imageList.length); var pending = imageList.length;

    imageList.forEach(function(img, i) {
        var src = (img.annotatedB64 && img.annotatedB64.length > 100) ? img.annotatedB64 : img.b64;
        var el = new Image();
        el.onload = function() { elems[i] = el; if (--pending === 0) drawCombined(); };
        el.onerror = function() { elems[i] = null; if (--pending === 0) drawCombined(); };
        el.src = src;
    });

    function drawCombined() {
        var cells = elems.map(function(el) {
            if (!el) return null;
            var scale = Math.min(1, col_w / el.naturalWidth);
            return { el: el, w: Math.round(el.naturalWidth * scale), h: Math.round(el.naturalHeight * scale) };
        }).filter(Boolean);
        if (cells.length === 0) { callback(null); return; }

        var rows = [], row_h = [];
        for (var i = 0; i < cells.length; i += COLS) {
            var row = cells.slice(i, i + COLS);
            rows.push(row); row_h.push(Math.max.apply(null, row.map(function(c){ return c.h; })));
        }

        var total_h = row_h.reduce(function(s, h){ return s + h; }, 0) + GAP * (rows.length - 1);
        var canvas_w = COLS === 1 ? cells[0].w : (col_w * COLS + GAP * (COLS - 1));

        var tmp = document.createElement('canvas');
        tmp.width = canvas_w; tmp.height = total_h;
        var ctx = tmp.getContext('2d');
        ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, canvas_w, total_h);

        var y = 0;
        rows.forEach(function(row, ri) {
            row.forEach(function(cell, ci) {
                var x = ci * (col_w + GAP);
                ctx.drawImage(cell.el, x, y, cell.w, cell.h);
            });
            y += row_h[ri] + GAP;
        });
        callback(tmp.toDataURL('image/png'));
    }
}

$('#operative_form').on('submit', function(e) {
    e.preventDefault();
    var form = this;
    var btn = $('#btn-save');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');
    
    saveSvgToCurrent();

    combineAllImages(function(combinedB64) {
        var formData = new FormData(form);
        formData.append('combinedB64', combinedB64 || '');
        
        var toSend = imageList.map(function(im, i) {
            return { sort_order: i, b64: im.b64, svgData: im.svgData, name: im.name, itemId: im.itemId, canvasW: im.canvasW, canvasH: im.canvasH, annotatedB64: im.annotatedB64 };
        });
        formData.append('images', JSON.stringify(toSend));

        var actionUrl = $('#rec_id').val() ? 'form-operative-update.php' : 'form-operative-save.php';
        
        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success || res.status === 'success') {
                    Swal.fire({
                        title: 'สำเร็จ',
                        text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = 'form-operative-main.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>';
                    });
                } else {
                    Swal.fire('ข้อผิดพลาด', res.message || 'ไม่สามารถบันทึกข้อมูลได้', 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> บันทึกข้อมูล');
                }
            },
            error: function(err) {
                console.error(err);
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> บันทึกข้อมูล');
            }
        });
    });
});

function deleteOpNote(id) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: 'คุณต้องการลบ Operative Note ฉบับนี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบข้อมูล',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'form-operative-delete.php',
                type: 'POST',
                data: { id: id, an: '<?= htmlspecialchars($an) ?>' },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            title: 'ลบสำเร็จ',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'form-operative-main.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>';
                        });
                    } else {
                        Swal.fire('ข้อผิดพลาด', res.message || 'ไม่สามารถลบข้อมูลได้', 'error');
                    }
                },
                error: function() {
                    Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                }
            });
        }
    });
}

window._templatesData = [];
function loadTemplateList(cb) {
    $.get('form-operative-template-api.php?action=list', function(res) {
        if(res.success) {
            window._templatesData = res.data;
            if(cb) cb(res.data);
        }
    }, 'json');
}

function initSelect2Templates() {
    loadTemplateList(function(data) {
        var opSelect = $('#inp_operation_name');
        var currentVal = opSelect.val();
        opSelect.empty();
        
        var hasCurrent = false;
        opSelect.append('<option value="">-- พิมพ์หรือเลือกรายการ --</option>');
        if(data && data.length > 0) {
            data.forEach(function(item) {
                var selected = (item.operation_name == currentVal) ? 'selected' : '';
                if(selected) hasCurrent = true;
                opSelect.append('<option value="'+item.operation_name+'" '+selected+'>'+item.operation_name+'</option>');
            });
        }
        
        if(currentVal && !hasCurrent) {
            opSelect.append('<option value="'+currentVal+'" selected>'+currentVal+'</option>');
        }

        opSelect.select2({
            tags: true,
            theme: 'bootstrap4',
            placeholder: '-- พิมพ์หรือเลือกรายการ --',
            allowClear: true,
            width: '100%'
        });
    });
}

$(function() {
    $('#inp_surgeon').select2({
        tags: true,
        theme: 'bootstrap4',
        width: '100%',
        placeholder: ''
    });

    initSelect2Templates();

    // Handle change event to load template
    $('#inp_operation_name').on('change', function() {
        var opName = $(this).val();
        if(!opName || !_canEdit) return;
        
        // Find if template exists
        var tpl = window._templatesData.find(t => t.operation_name === opName);
        if (!tpl) return;
        
        // Load template if canvas is empty
        if (imageList.length === 0) {
            fetchTemplateImage(tpl.id, opName);
        } else {
            // Check if we already have this template
            var exists = imageList.find(img => img.name === 'Template: ' + opName);
            if(!exists) {
                Swal.fire({
                    title: 'ดึงข้อมูลจาก Template ไหม?',
                    text: 'ต้องการโหลดภาพและข้อมูลเริ่มต้น (เช่น Diagnosis, Procedure) ของรายการผ่าตัดนี้หรือไม่?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'โหลดข้อมูล',
                    cancelButtonText: 'ไม่เป็นไร'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetchTemplateImage(tpl.id, opName);
                    }
                });
            }
        }
    });
});

function fetchTemplateImage(id, opName) {
    $.get('form-operative-template-api.php?action=get&id=' + id, function(res) {
        if(res.success) {
            if (res.b64) {
                imageList.push({ b64: res.b64, svgData: '', name: 'Template: ' + opName, itemId: null });
                renderThumbs();
                selectImage(imageList.length - 1);
            }
            
            // Auto-fill text fields if they are currently empty
            var fields = ['clinical_diagnosis', 'post_op_diagnosis', 'anesthetic_technique', 'op_position', 'incision', 'finding', 'procedure_detail'];
            var filled = 0;
            fields.forEach(function(f) {
                var el = $('[name="'+f+'"]');
                if(res[f] && el.length > 0) {
                    if(el.val().trim() === '') {
                        el.val(res[f]);
                        filled++;
                    }
                }
            });
            
            if (filled > 0) {
                Swal.fire({
                    title: 'โหลดข้อมูลสำเร็จ',
                    text: 'ดึงข้อมูลเริ่มต้นจาก Template ลงในฟอร์มเรียบร้อยแล้ว',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    }, 'json');
}

function openTemplateModal() {
    $('#templateForm')[0].reset();
    $('#tpl_id').val('');
    
    // Auto-fill from main form to help them start creating a new template
    $('#tpl_clinical_diagnosis').val($('[name="clinical_diagnosis"]').val());
    $('#tpl_post_op_diagnosis').val($('[name="post_op_diagnosis"]').val());
    $('#tpl_anesthetic_technique').val($('[name="anesthetic_technique"]').val());
    $('#tpl_op_position').val($('[name="op_position"]').val());
    $('#tpl_incision').val($('[name="incision"]').val());
    $('#tpl_finding').val($('[name="finding"]').val());
    $('#tpl_procedure_detail').val($('[name="procedure_detail"]').val());
    
    refreshTemplateTable();
    $('#templateModal').modal('show');
}

function editTemplateInModal(id) {
    Swal.fire({ title: 'กำลังโหลด...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $.get('form-operative-template-api.php?action=get&id=' + id, function(res) {
        Swal.close();
        if(res.success) {
            $('#tpl_id').val(id);
            $('#tpl_operation_name').val(res.operation_name);
            $('#tpl_clinical_diagnosis').val(res.clinical_diagnosis || '');
            $('#tpl_post_op_diagnosis').val(res.post_op_diagnosis || '');
            $('#tpl_anesthetic_technique').val(res.anesthetic_technique || '');
            $('#tpl_op_position').val(res.op_position || '');
            $('#tpl_incision').val(res.incision || '');
            $('#tpl_finding').val(res.finding || '');
            $('#tpl_procedure_detail').val(res.procedure_detail || '');
            
            // Scroll to top of modal
            $('#templateModal .modal-body').animate({ scrollTop: 0 }, 'fast');
            $('#tpl_operation_name').focus();
        }
    }, 'json');
}



function refreshTemplateTable() {
    loadTemplateList(function(data) {
        var tbody = $('#templateListTbody');
        tbody.empty();
        if(data.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center text-muted">ยังไม่มีข้อมูล Template</td></tr>');
            return;
        }
        data.forEach(function(item) {
            tbody.append(`
                <tr>
                    <td>${item.operation_name}</td>
                    <td>${item.create_datetime}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info py-0 px-2 mb-1" onclick="loadTemplateToEdit(${item.id}, '${item.operation_name}')" title="ดึงข้อมูลลงฟอร์มเพื่อใช้งาน"><i class="fas fa-download"></i> ใช้</button>
                        <button type="button" class="btn btn-sm btn-warning py-0 px-2 mb-1" onclick="editTemplateInModal(${item.id})" title="แก้ไขข้อมูล Template นี้"><i class="fas fa-pencil-alt"></i> แก้ไข</button>
                        <button type="button" class="btn btn-sm btn-success py-0 px-2 mb-1" onclick="copyTemplate(${item.id}, '${item.operation_name}')" title="คัดลอกให้ผู้อื่น"><i class="fas fa-share"></i> โอน</button>
                        <button type="button" class="btn btn-sm btn-danger py-0 px-2 mb-1" onclick="deleteTemplate(${item.id})" title="ลบ"><i class="fas fa-trash"></i> ลบ</button>
                    </td>
                </tr>
            `);
        });
    });
}

function loadTemplateToEdit(id, opName) {
    $('#templateModal').modal('hide');
    fetchTemplateImage(id, opName);
    
    // Set the dropdown value to match the template name
    var opSelect = $('#inp_operation_name');
    if(opSelect.find("option[value='" + opName + "']").length) {
        opSelect.val(opName).trigger('change.select2');
    } else {
        var newOption = new Option(opName, opName, true, true);
        opSelect.append(newOption).trigger('change');
    }
}

function copyTemplate(id, opName) {
    Swal.fire({
        title: 'กำลังโหลดข้อมูล...',
        text: 'โปรดรอสักครู่',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: 'form-operative-template-api.php?action=users',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            Swal.close();
            if(res.success) {
                var optionsHtml = '<option value="">-- ค้นหาและเลือกผู้รับ --</option>';
                res.data.forEach(function(u) {
                    optionsHtml += `<option value="${u.loginname}">${u.name} (${u.loginname})</option>`;
                });
                
                Swal.fire({
                    title: 'โอน Template',
                    html: `
                        <div class="text-left mb-2 text-muted">ส่งสำเนา "<b>${opName}</b>" ให้แพทย์/ผู้ใช้งานท่านอื่น</div>
                        <select id="swal-target-user" class="form-control" style="width: 100%;">
                            ${optionsHtml}
                        </select>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'โอน Template',
                    cancelButtonText: 'ยกเลิก',
                    didOpen: () => {
                        $('#swal-target-user').select2({
                            dropdownParent: $('.swal2-container')
                        });
                    },
                    preConfirm: () => {
                        var val = $('#swal-target-user').val();
                        if (!val) {
                            Swal.showValidationMessage('กรุณาเลือกผู้รับ');
                            return false;
                        }
                        return val;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'form-operative-template-api.php?action=copy',
                            type: 'POST',
                            data: { id: id, target_user: result.value },
                            dataType: 'json',
                            success: function(cRes) {
                                if (cRes.success) {
                                    Swal.fire('สำเร็จ', 'ส่งสำเนา Template ให้ผู้ใช้อื่นเรียบร้อยแล้ว', 'success');
                                } else {
                                    Swal.fire('ข้อผิดพลาด', cRes.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('ข้อผิดพลาด', 'เซิร์ฟเวอร์ไม่ตอบสนอง (Copy Error: ' + xhr.status + ')', 'error');
                            }
                        });
                    }
                });
            } else {
                Swal.fire('ข้อผิดพลาด', res.message || 'ไม่สามารถโหลดรายชื่อผู้ใช้ได้', 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire('ข้อผิดพลาด', 'เซิร์ฟเวอร์ไม่ตอบสนอง (Users Error: ' + xhr.status + ')', 'error');
        }
    });
}

$('#templateForm').on('submit', function(e) {
    e.preventDefault();
    var fd = new FormData();
    fd.append('operation_name', $('#tpl_operation_name').val());
    
    if ($('#tpl_image')[0].files.length > 0) {
        fd.append('template_image', $('#tpl_image')[0].files[0]);
    }
    
    // Save text fields from Modal into the template
    fd.append('clinical_diagnosis', $('#tpl_clinical_diagnosis').val());
    fd.append('post_op_diagnosis', $('#tpl_post_op_diagnosis').val());
    fd.append('anesthetic_technique', $('#tpl_anesthetic_technique').val());
    fd.append('op_position', $('#tpl_op_position').val());
    fd.append('incision', $('#tpl_incision').val());
    fd.append('finding', $('#tpl_finding').val());
    fd.append('procedure_detail', $('#tpl_procedure_detail').val());
    
    var btn = $(this).find('button[type="submit"]');
    var orgHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> บันทึก...');

    $.ajax({
        url: 'form-operative-template-api.php?action=save',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                Swal.fire({title: 'สำเร็จ', text: 'บันทึก Template แล้ว', icon: 'success', timer: 1500, showConfirmButton: false});
                $('#templateForm')[0].reset();
                refreshTemplateTable();
                initSelect2Templates();
            } else {
                Swal.fire('ข้อผิดพลาด', res.message, 'error');
            }
        },
        complete: function() {
            btn.prop('disabled', false).html(orgHtml);
        }
    });
});

function deleteTemplate(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if(result.isConfirmed) {
            $.post('form-operative-template-api.php?action=delete', {id: id}, function(res) {
                if(res.success) {
                    refreshTemplateTable();
                    initSelect2Templates();
                }
            }, 'json');
        }
    });
}
</script>
