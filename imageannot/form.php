<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('Asia/Bangkok');

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) { header('Location: ../login.php'); exit; }

require_once '../mains/main-report.php';
require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/session-modal.php';

$conn = DbUtils::get_hosxp_connection();
$an   = trim($_REQUEST['an']  ?? '');
$id   = (int)($_REQUEST['id'] ?? 0);

$rec          = null;
$canvas_json  = 'null';
$form_data_arr = [];
$form_note    = '';

// ============================================================
// โหมด re-edit: ดึงข้อมูลจาก DB รวมถึง image_data เพื่อ embed
// ============================================================
$img_data_uri = ''; // base64 data URI สำหรับ inject ลง JS โดยตรง
if ($id > 0) {
    // SELECT image_data ด้วยเพื่อแก้ปัญหา cross-origin canvas
    $stmt = $conn->prepare("SELECT id, title, doc_group, original_name, image_type,
                                    image_data, canvas_json, form_data, form_note,
                                    created_by
                               FROM prs_image_annot
                              WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt->execute(['id' => $id, 'an' => $an]);
    $rec = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rec) {
        // แปลง binary → base64 Data URI เพื่อ inject ลง <script> โดยตรง
        // วิธีนี้หลีกเลี่ยงปัญหา canvas tainted / CORS ทั้งหมด
        $img_bin = $rec['image_data'];
        if (is_resource($img_bin)) $img_bin = stream_get_contents($img_bin);
        if ($img_bin) {
            $mime         = $rec['image_type'] ?: 'image/png';
            $img_data_uri = 'data:' . $mime . ';base64,' . base64_encode($img_bin);
        }

        $canvas_json   = $rec['canvas_json'] ? $rec['canvas_json'] : 'null';
        $form_note     = $rec['form_note'] ?? '';
        $form_data_arr = $rec['form_data'] ? (json_decode($rec['form_data'], true) ?: []) : [];
    }
}

$doc_groups = [
    'งานห้องผ่าตัด','ห้องคลอด','งานผู้ป่วยใน',
    'งานอุบัติเหตุฉุกเฉิน','งานผู้ป่วยนอก','ห้องไอซียู',
    'งานเวชระเบียน','อื่นๆ',
];

// ฟังก์ชัน helper: ดึงค่า form_data ที่บันทึกไว้
function fv($arr, $key, $default='') {
    return htmlspecialchars($arr[$key] ?? $default);
}
function fc($arr, $key, $val) {
    return (isset($arr[$key]) && $arr[$key] === $val) ? 'checked' : '';
}
function fa($arr, $key) {
    return isset($arr[$key]) && $arr[$key] ? 'checked' : '';
}
?>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
<script src="../include/fabric.js"></script>

<style>
/* ---- Layout 2 panel ---- */
#form-layout   { display:flex; gap:14px; align-items:flex-start; }
#form-panel    { width:320px; flex-shrink:0; }
#canvas-panel  { flex:1; min-width:0; }

/* ---- Form panel ---- */
.fp-section    { background:#fff; border:1px solid #dee2e6; border-radius:8px;
                 padding:12px 14px; margin-bottom:10px; }
.fp-section-title { font-size:11px; font-weight:700; text-transform:uppercase;
                    letter-spacing:.05em; color:#6c757d; margin-bottom:8px;
                    border-bottom:1px solid #f1f3f5; padding-bottom:4px; }
.check-grid   { display:grid; grid-template-columns:1fr 1fr; gap:4px; }
.check-item   { display:flex; align-items:center; gap:5px; font-size:12px;
                padding:3px 0; }
.check-item input { cursor:pointer; }
.form-control-sm  { font-size:12px; }
label.small       { font-size:11px; font-weight:600; color:#495057; margin-bottom:2px; }

/* ---- Toolbar ---- */
#opts-bar  { display:flex; gap:8px; align-items:center; flex-wrap:wrap;
             padding:7px 10px; background:#f1f3f5; border-radius:6px; margin-bottom:8px;
             font-size:12px; }
#toolbar   { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:6px; }
.tb-btn    { width:36px; height:36px; border:1px solid #ced4da; border-radius:6px;
             background:#fff; cursor:pointer; display:flex; align-items:center;
             justify-content:center; font-size:16px; transition:.15s; }
.tb-btn:hover  { background:#e9ecef; }
.tb-btn.active { background:#2c6e49; border-color:#2c6e49; color:#fff; }

/* ---- Drop zone ---- */
#drop-zone { border:2px dashed #adb5bd; border-radius:8px; padding:24px;
             text-align:center; color:#6c757d; cursor:pointer; background:#fff;
             transition:.2s; }
#drop-zone:hover, #drop-zone.drag-over { border-color:#2c6e49; background:#f0faf4; }
#drop-zone .drop-icon { font-size:32px; margin-bottom:6px; }

/* ---- Canvas ---- */
#canvas-area   { border:2px solid #dee2e6; border-radius:6px; background:#e9ecef;
                 overflow:hidden; position:relative; }
#canvas-area canvas { display:block; }

/* ---- Responsive ---- */
@media(max-width:768px) {
    #form-layout  { flex-direction:column; }
    #form-panel   { width:100%; }
}
</style>

<div class="container-fluid mt-2">

  <!-- Top action bar -->
  <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
    <h6 class="mb-0 mr-auto font-weight-bold">
      <i class="fas fa-paint-brush text-success"></i>
      <?= $id > 0 ? 'แก้ไขภาพและข้อมูล' : 'บันทึกภาพและข้อมูล' ?>
    </h6>
    <button class="btn btn-success btn-sm shadow-sm" onclick="doSave()">
      <i class="fas fa-save"></i> บันทึก
    </button>
    <?php if ($id > 0): ?>
    <a href="pdf.php?an=<?= urlencode($an) ?>&id=<?= $id ?>" target="_blank"
       class="btn btn-danger btn-sm shadow-sm">
      <i class="fas fa-file-pdf"></i> พิมพ์ PDF
    </a>
    <?php endif; ?>
    <a href="index.php?an=<?= urlencode($an) ?>" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> กลับ
    </a>
  </div>

  <div id="form-layout">

    <!-- ==================== PANEL ซ้าย: Form fields ==================== -->
    <div id="form-panel">

      <!-- ส่วนที่ 1: ข้อมูลทั่วไป -->
      <div class="fp-section">
        <div class="fp-section-title">ข้อมูลทั่วไป</div>
        <div class="mb-2">
          <label class="small">ชื่อเอกสาร / หัวข้อ <span class="text-danger">*</span></label>
          <input type="text" id="inp_title" class="form-control form-control-sm"
                 placeholder="เช่น แผลผ่าตัด, รอยฟกช้ำ"
                 value="<?= fv($rec ?? [], 'title') ?>">
        </div>
        <div class="mb-0">
          <label class="small">กลุ่มเอกสาร</label>
          <select id="inp_group" class="form-control form-control-sm">
            <option value="">-- เลือกกลุ่ม --</option>
            <?php foreach ($doc_groups as $g): ?>
            <option value="<?= htmlspecialchars($g) ?>"
              <?= ($rec && ($rec['doc_group'] ?? '') === $g) ? 'selected' : '' ?>>
              <?= htmlspecialchars($g) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- ส่วนที่ 2: ประเภทภาพ/การบันทึก -->
      <div class="fp-section">
        <div class="fp-section-title">ประเภทการบันทึก</div>
        <div class="check-grid">
          <label class="check-item">
            <input type="checkbox" name="type_wound"     id="type_wound"     <?= fa($form_data_arr,'type_wound') ?>>
            <span>แผล / บาดเจ็บ</span>
          </label>
          <label class="check-item">
            <input type="checkbox" name="type_skin"      id="type_skin"      <?= fa($form_data_arr,'type_skin') ?>>
            <span>ผิวหนัง</span>
          </label>
          <label class="check-item">
            <input type="checkbox" name="type_xray"      id="type_xray"      <?= fa($form_data_arr,'type_xray') ?>>
            <span>X-Ray</span>
          </label>
          <label class="check-item">
            <input type="checkbox" name="type_lab"       id="type_lab"       <?= fa($form_data_arr,'type_lab') ?>>
            <span>ผลแลป</span>
          </label>
          <label class="check-item">
            <input type="checkbox" name="type_equipment" id="type_equipment" <?= fa($form_data_arr,'type_equipment') ?>>
            <span>อุปกรณ์/เครื่องมือ</span>
          </label>
          <label class="check-item">
            <input type="checkbox" name="type_other"     id="type_other"     <?= fa($form_data_arr,'type_other') ?>>
            <span>อื่นๆ</span>
          </label>
        </div>
      </div>

      <!-- ส่วนที่ 3: ลักษณะแผล/บาดเจ็บ -->
      <div class="fp-section">
        <div class="fp-section-title">ลักษณะ (เลือกได้หลายข้อ)</div>
        <div class="check-grid">
          <label class="check-item"><input type="checkbox" name="char_clean"     id="char_clean"     <?= fa($form_data_arr,'char_clean') ?>> <span>แผลสะอาด</span></label>
          <label class="check-item"><input type="checkbox" name="char_infected"  id="char_infected"  <?= fa($form_data_arr,'char_infected') ?>> <span>มีการติดเชื้อ</span></label>
          <label class="check-item"><input type="checkbox" name="char_swelling"  id="char_swelling"  <?= fa($form_data_arr,'char_swelling') ?>> <span>บวม</span></label>
          <label class="check-item"><input type="checkbox" name="char_redness"   id="char_redness"   <?= fa($form_data_arr,'char_redness') ?>> <span>แดง</span></label>
          <label class="check-item"><input type="checkbox" name="char_discharge" id="char_discharge" <?= fa($form_data_arr,'char_discharge') ?>> <span>มีหนอง/ของเหลว</span></label>
          <label class="check-item"><input type="checkbox" name="char_dry"       id="char_dry"       <?= fa($form_data_arr,'char_dry') ?>> <span>แห้งดี</span></label>
          <label class="check-item"><input type="checkbox" name="char_healing"   id="char_healing"   <?= fa($form_data_arr,'char_healing') ?>> <span>หายดี</span></label>
          <label class="check-item"><input type="checkbox" name="char_necrosis"  id="char_necrosis"  <?= fa($form_data_arr,'char_necrosis') ?>> <span>Necrosis</span></label>
        </div>
      </div>

      <!-- ส่วนที่ 4: ขนาด/ตำแหน่ง -->
      <div class="fp-section">
        <div class="fp-section-title">ขนาด / ตำแหน่ง</div>
        <div class="row mb-1">
          <div class="col-6">
            <label class="small">กว้าง (cm)</label>
            <input type="number" step="0.1" id="wound_w" class="form-control form-control-sm"
                   value="<?= fv($form_data_arr,'wound_w') ?>" placeholder="0.0">
          </div>
          <div class="col-6">
            <label class="small">ยาว (cm)</label>
            <input type="number" step="0.1" id="wound_l" class="form-control form-control-sm"
                   value="<?= fv($form_data_arr,'wound_l') ?>" placeholder="0.0">
          </div>
        </div>
        <div class="mb-0">
          <label class="small">ตำแหน่ง</label>
          <input type="text" id="wound_loc" class="form-control form-control-sm"
                 value="<?= fv($form_data_arr,'wound_loc') ?>"
                 placeholder="เช่น ขาขวา, หน้าท้องด้านล่าง">
        </div>
      </div>

      <!-- ส่วนที่ 5: การรักษา/การดูแล -->
      <div class="fp-section">
        <div class="fp-section-title">การรักษา / การดูแล</div>
        <div class="check-grid mb-2">
          <label class="check-item"><input type="checkbox" name="tx_clean"    id="tx_clean"    <?= fa($form_data_arr,'tx_clean') ?>> <span>ทำแผล</span></label>
          <label class="check-item"><input type="checkbox" name="tx_stitch"   id="tx_stitch"   <?= fa($form_data_arr,'tx_stitch') ?>> <span>เย็บแผล</span></label>
          <label class="check-item"><input type="checkbox" name="tx_drain"    id="tx_drain"    <?= fa($form_data_arr,'tx_drain') ?>> <span>ใส่ Drain</span></label>
          <label class="check-item"><input type="checkbox" name="tx_dressing" id="tx_dressing" <?= fa($form_data_arr,'tx_dressing') ?>> <span>เปลี่ยน Dressing</span></label>
          <label class="check-item"><input type="checkbox" name="tx_remove"   id="tx_remove"   <?= fa($form_data_arr,'tx_remove') ?>> <span>ตัดไหม</span></label>
          <label class="check-item"><input type="checkbox" name="tx_photo"    id="tx_photo"    <?= fa($form_data_arr,'tx_photo') ?>> <span>ถ่ายภาพติดตาม</span></label>
        </div>
      </div>

      <!-- ส่วนที่ 6: บันทึกเพิ่มเติม -->
      <div class="fp-section">
        <div class="fp-section-title">บันทึกเพิ่มเติม</div>
        <textarea id="inp_note" class="form-control form-control-sm" rows="4"
                  placeholder="บันทึกรายละเอียดเพิ่มเติม..."><?= htmlspecialchars($form_note) ?></textarea>
      </div>

    </div><!-- /form-panel -->

    <!-- ==================== PANEL ขวา: Canvas ==================== -->
    <div id="canvas-panel">

      <!-- Drop zone (new mode) -->
      <?php if ($id === 0): ?>
      <div id="drop-zone" onclick="document.getElementById('file-input').click()">
        <div class="drop-icon">📷</div>
        <div class="font-weight-bold">คลิกหรือลากไฟล์ภาพมาวางที่นี่</div>
        <div class="small mt-1 text-muted">PNG, JPG, WEBP, GIF | ขนาดไม่เกิน 15 MB</div>
        <div class="small text-muted">หรือถ่ายภาพจากกล้องโดยตรง (มือถือ)</div>
        <input type="file" id="file-input" accept="image/*" capture="environment" style="display:none">
      </div>
      <?php endif; ?>

      <!-- Editor (hidden until image loaded) -->
      <div id="editor-section" style="<?= $id === 0 ? 'display:none' : '' ?>">

        <!-- Toolbar tools -->
        <div id="toolbar">
          <button class="tb-btn"        id="btn-select"    onclick="setTool('select')"    title="เลือก/ย้าย">&#9654;</button>
          <button class="tb-btn active" id="btn-pen"       onclick="setTool('pen')"       title="ปากกา">&#9998;</button>
          <button class="tb-btn"        id="btn-line"      onclick="setTool('line')"      title="เส้นตรง">&#8213;</button>
          <button class="tb-btn"        id="btn-arrow"     onclick="setTool('arrow')"     title="ลูกศร">&#10148;</button>
          <button class="tb-btn"        id="btn-rect"      onclick="setTool('rect')"      title="สี่เหลี่ยม">&#9645;</button>
          <button class="tb-btn"        id="btn-circle"    onclick="setTool('circle')"    title="วงรี">&#9711;</button>
          <button class="tb-btn"        id="btn-text"      onclick="setTool('text')"      title="ข้อความ (ดับเบิ้ลคลิก)">T</button>
          <button class="tb-btn"        id="btn-highlight" onclick="setTool('highlight')" title="ไฮไลต์">&#9632;</button>
          <div style="width:1px;background:#dee2e6;margin:0 2px"></div>
          <button class="tb-btn" onclick="undoAction()"    title="Undo">&#8617;</button>
          <button class="tb-btn" onclick="deleteSelected()" title="ลบที่เลือก">&#128465;</button>
          <button class="tb-btn" onclick="clearCanvas()"   title="ล้างทั้งหมด">&#9003;</button>
        </div>

        <!-- Options -->
        <div id="opts-bar">
          <label>สี:</label>
          <input type="color" id="colorPicker" value="#e63946" style="width:32px;height:28px;border:none;cursor:pointer;border-radius:4px;">
          <label>เส้น:</label>
          <input type="range" id="strokeWidth" min="1" max="20" value="3" style="width:70px">
          <span id="strokeVal" style="min-width:16px">3</span>
          <label>โปร่ง:</label>
          <input type="range" id="opacitySlider" min="10" max="100" value="100" style="width:70px">
          <span id="opacityVal">100%</span>
        </div>

        <!-- Canvas -->
        <div id="canvas-area">
          <canvas id="c"></canvas>
        </div>
        <div class="text-muted small mt-1">
          <i class="fas fa-info-circle"></i>
          เลือก tool "ข้อความ" แล้ว <b>ดับเบิ้ลคลิก</b> บนภาพเพื่อพิมพ์ข้อความ
        </div>

      </div><!-- /editor-section -->
    </div><!-- /canvas-panel -->

  </div><!-- /form-layout -->
</div><!-- /container -->

<script>
// ============================================================
// Global
// ============================================================
var canvas;
var currentTool  = 'pen';
var isDown       = false;
var startX, startY, activeShape;
var history      = [];
var originalImageB64 = '';
var originalFileName = '';

// ============================================================
// Init canvas
// ============================================================
function initCanvas(w, h) {
    if (canvas) canvas.dispose();
    canvas = new fabric.Canvas('c', {
        width:  w, height: h,
        isDrawingMode: true, selection: false,
        preserveObjectStacking: true,
    });
    applyBrushSettings();
    bindEvents();
    saveHistory();
}

// ============================================================
// โหลดภาพขึ้น canvas
// ============================================================
function loadImageToCanvas(src, filename) {
    originalImageB64 = src;
    originalFileName = filename || '';

    fabric.Image.fromURL(src, function(img) {
        var maxW  = document.getElementById('canvas-area').clientWidth || 700;
        var scale = Math.min(1, maxW / img.width);
        var w = Math.round(img.width  * scale);
        var h = Math.round(img.height * scale);

        initCanvas(w, h);
        img.set({ scaleX: scale, scaleY: scale, selectable: false, evented: false });
        canvas.add(img);
        canvas.sendToBack(img);
        canvas.renderAll();
        saveHistory();

        document.getElementById('editor-section').style.display = '';
        var dz = document.getElementById('drop-zone');
        if (dz) dz.style.display = 'none';
    });
    // NOTE: ไม่ใส่ crossOrigin เพราะ src เป็น base64 Data URI ไม่มีปัญหา CORS
}

// ============================================================
// Re-edit: inject base64 จาก PHP ลง JS โดยตรง
// PHP embed base64 ทำให้ไม่ต้อง fetch URL ไม่มีปัญหา canvas tainted
// ============================================================
<?php if ($id > 0 && $rec && $img_data_uri): ?>
(function() {
    // img_data_uri ถูก echo จาก PHP หลัง base64_encode(binary) 
    var b64Uri      = <?= json_encode($img_data_uri) ?>;
    var canvasJson  = <?= $canvas_json ?>;
    var origName    = <?= json_encode($rec['original_name'] ?? '') ?>;

    originalImageB64 = b64Uri;
    originalFileName = origName;

    fabric.Image.fromURL(b64Uri, function(img) {
        var maxW  = document.getElementById('canvas-area').clientWidth || 700;
        var scale = Math.min(1, maxW / img.width);
        var w = Math.round(img.width  * scale);
        var h = Math.round(img.height * scale);

        initCanvas(w, h);

        if (canvasJson) {
            // loadFromJSON restore ทุก stroke, shape, text ที่เคยวาดไว้
            canvas.loadFromJSON(canvasJson, function() {
                canvas.renderAll();
                saveHistory();
            });
        } else {
            img.set({ scaleX: scale, scaleY: scale, selectable: false, evented: false });
            canvas.add(img);
            canvas.sendToBack(img);
            canvas.renderAll();
            saveHistory();
        }
        document.getElementById('editor-section').style.display = '';
    });
})();
<?php endif; ?>

// ============================================================
// File input & Drag-drop (new mode)
// ============================================================
var fileInput = document.getElementById('file-input');
if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        if (e.target.files[0]) readFile(e.target.files[0]);
    });
}
var dropZone = document.getElementById('drop-zone');
if (dropZone) {
    ['dragover','dragenter'].forEach(function(ev) {
        dropZone.addEventListener(ev, function(e){ e.preventDefault(); dropZone.classList.add('drag-over'); });
    });
    dropZone.addEventListener('dragleave', function(){ dropZone.classList.remove('drag-over'); });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault(); dropZone.classList.remove('drag-over');
        var f = e.dataTransfer.files[0];
        if (f && f.type.startsWith('image/')) readFile(f);
    });
}
function readFile(file) {
    if (file.size > 15 * 1024 * 1024) {
        Swal.fire('ไฟล์ใหญ่เกินไป','ขนาดไม่เกิน 15 MB','warning'); return;
    }
    var r = new FileReader();
    r.onload = function(e) { loadImageToCanvas(e.target.result, file.name); };
    r.readAsDataURL(file);
}

// ============================================================
// Tool
// ============================================================
function setTool(tool) {
    currentTool = tool;
    document.querySelectorAll('.tb-btn').forEach(function(b){ b.classList.remove('active'); });
    var btn = document.getElementById('btn-' + tool);
    if (btn) btn.classList.add('active');
    if (!canvas) return;
    canvas.isDrawingMode = (tool === 'pen');
    canvas.selection     = (tool === 'select');
    if (tool === 'pen') applyBrushSettings();
}

function applyBrushSettings() {
    if (!canvas) return;
    var color   = document.getElementById('colorPicker').value;
    var width   = parseInt(document.getElementById('strokeWidth').value);
    var opacity = parseInt(document.getElementById('opacitySlider').value) / 100;
    canvas.freeDrawingBrush.color = hexToRgba(color, opacity);
    canvas.freeDrawingBrush.width = width;
}
function hexToRgba(hex, alpha) {
    var r=parseInt(hex.slice(1,3),16), g=parseInt(hex.slice(3,5),16), b=parseInt(hex.slice(5,7),16);
    return 'rgba('+r+','+g+','+b+','+alpha+')';
}

document.getElementById('strokeWidth').addEventListener('input', function(){
    document.getElementById('strokeVal').textContent = this.value; applyBrushSettings();
});
document.getElementById('opacitySlider').addEventListener('input', function(){
    document.getElementById('opacityVal').textContent = this.value+'%'; applyBrushSettings();
});
document.getElementById('colorPicker').addEventListener('input', applyBrushSettings);

// ============================================================
// Mouse events (line/arrow/rect/circle/highlight)
// ============================================================
function bindEvents() {
    canvas.on('mouse:down', function(opt) {
        var tools = ['line','arrow','rect','circle','highlight'];
        if (tools.indexOf(currentTool) === -1) return;
        isDown = true;
        var ptr = canvas.getPointer(opt.e);
        startX = ptr.x; startY = ptr.y;
        var color   = document.getElementById('colorPicker').value;
        var sw      = parseInt(document.getElementById('strokeWidth').value);
        var opacity = parseInt(document.getElementById('opacitySlider').value)/100;
        var rgba    = hexToRgba(color, opacity);

        if (currentTool === 'line' || currentTool === 'arrow') {
            activeShape = new fabric.Line([startX,startY,startX,startY],
                { stroke:rgba, strokeWidth:sw, selectable:true });
        } else if (currentTool === 'rect') {
            activeShape = new fabric.Rect(
                { left:startX, top:startY, width:0, height:0,
                  fill:'transparent', stroke:rgba, strokeWidth:sw, selectable:true });
        } else if (currentTool === 'circle') {
            activeShape = new fabric.Ellipse(
                { left:startX, top:startY, rx:0, ry:0,
                  fill:'transparent', stroke:rgba, strokeWidth:sw, selectable:true });
        } else if (currentTool === 'highlight') {
            activeShape = new fabric.Rect(
                { left:startX, top:startY, width:0, height:0,
                  fill:hexToRgba(color, 0.3), stroke:'transparent', strokeWidth:0, selectable:true });
        }
        if (activeShape) canvas.add(activeShape);
    });

    canvas.on('mouse:move', function(opt) {
        if (!isDown || !activeShape) return;
        var ptr = canvas.getPointer(opt.e);
        if (currentTool==='line'||currentTool==='arrow') {
            activeShape.set({ x2:ptr.x, y2:ptr.y });
        } else if (currentTool==='rect'||currentTool==='highlight') {
            activeShape.set({
                width:Math.abs(ptr.x-startX), height:Math.abs(ptr.y-startY),
                left:Math.min(ptr.x,startX),  top:Math.min(ptr.y,startY),
            });
        } else if (currentTool==='circle') {
            activeShape.set({
                rx:Math.abs(ptr.x-startX)/2, ry:Math.abs(ptr.y-startY)/2,
                left:Math.min(ptr.x,startX), top:Math.min(ptr.y,startY),
            });
        }
        canvas.renderAll();
    });

    canvas.on('mouse:up', function() {
        isDown=false; activeShape=null; saveHistory();
    });

    canvas.on('mouse:dblclick', function(opt) {
        if (currentTool !== 'text') return;
        var ptr   = canvas.getPointer(opt.e);
        var color = document.getElementById('colorPicker').value;
        var op    = parseInt(document.getElementById('opacitySlider').value)/100;
        var sw    = parseInt(document.getElementById('strokeWidth').value);
        var txt   = new fabric.IText('ข้อความ', {
            left: ptr.x, top: ptr.y,
            fontSize: 14 + sw * 2,
            fill: hexToRgba(color, op),
            fontFamily: 'Arial',
            selectable: true, editable: true,
        });
        canvas.add(txt);
        canvas.setActiveObject(txt);
        txt.enterEditing();
        canvas.renderAll();
        saveHistory();
    });

    canvas.on('object:added',    saveHistory);
    canvas.on('object:modified', saveHistory);
}

// ============================================================
// Undo / Delete / Clear
// ============================================================
function saveHistory() {
    if (!canvas) return;
    var j = JSON.stringify(canvas.toJSON());
    if (history[history.length-1] !== j) history.push(j);
    if (history.length > 50) history.shift();
}
function undoAction() {
    if (!canvas || history.length <= 1) return;
    history.pop();
    canvas.loadFromJSON(history[history.length-1], function(){ canvas.renderAll(); });
}
function deleteSelected() {
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (o) { canvas.remove(o); canvas.renderAll(); }
}
function clearCanvas() {
    if (!canvas) return;
    Swal.fire({
        title:'ล้าง annotation ทั้งหมด?', icon:'warning',
        showCancelButton:true, confirmButtonColor:'#dc3545',
        confirmButtonText:'ล้าง', cancelButtonText:'ยกเลิก',
    }).then(function(r) {
        if (!r.isConfirmed) return;
        var bg = canvas.getObjects().filter(function(o){ return !o.selectable; });
        canvas.clear();
        bg.forEach(function(o){ canvas.add(o); canvas.sendToBack(o); });
        canvas.renderAll(); saveHistory();
    });
}

// ============================================================
// Collect form data เป็น JSON
// ============================================================
function collectFormData() {
    var checkboxNames = [
        'type_wound','type_skin','type_xray','type_lab','type_equipment','type_other',
        'char_clean','char_infected','char_swelling','char_redness','char_discharge',
        'char_dry','char_healing','char_necrosis',
        'tx_clean','tx_stitch','tx_drain','tx_dressing','tx_remove','tx_photo',
    ];
    var obj = {};
    checkboxNames.forEach(function(n) {
        var el = document.getElementById(n);
        if (el) obj[n] = el.checked;
    });
    obj.wound_w   = document.getElementById('wound_w').value;
    obj.wound_l   = document.getElementById('wound_l').value;
    obj.wound_loc = document.getElementById('wound_loc').value;
    return JSON.stringify(obj);
}

// ============================================================
// Save
// ============================================================
function doSave() {
    var title = document.getElementById('inp_title').value.trim();
    if (!title) { Swal.fire('แจ้งเตือน','กรุณาระบุชื่อเอกสาร','warning'); return; }
    if (!canvas) { Swal.fire('แจ้งเตือน','กรุณาโหลดภาพก่อน','warning'); return; }

    var annotatedB64 = canvas.toDataURL({ format:'png', multiplier:1 });
    var canvasJson   = JSON.stringify(canvas.toJSON());
    var formDataJson = collectFormData();
    var formNote     = document.getElementById('inp_note').value;

    var fd = new FormData();
    fd.append('an',              '<?= htmlspecialchars($an) ?>');
    fd.append('title',           title);
    fd.append('doc_group',       document.getElementById('inp_group').value);
    fd.append('canvas_json',     canvasJson);
    fd.append('annotated_image', annotatedB64);
    fd.append('form_data',       formDataJson);
    fd.append('form_note',       formNote);

    <?php if ($id === 0): ?>
    fd.append('image_data',    originalImageB64);
    fd.append('original_name', originalFileName);
    var url = 'save.php';
    <?php else: ?>
    fd.append('id', '<?= $id ?>');
    var url = 'update.php';
    <?php endif; ?>

    Swal.fire({ title:'กำลังบันทึก...', allowOutsideClick:false,
                didOpen:function(){ Swal.showLoading(); } });

    $.ajax({
        url: url, type: 'POST', data: fd, processData:false, contentType:false,
        success: function(resp) {
            try {
                var data = (typeof resp==='string') ? JSON.parse(resp) : resp;
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ','บันทึกเรียบร้อย','success').then(function(){
                        window.location.href = 'index.php?an=<?= urlencode($an) ?>';
                    });
                } else {
                    Swal.fire('ผิดพลาด', data.message||'เกิดข้อผิดพลาด','error');
                }
            } catch(ex) {
                Swal.fire('ผิดพลาด','ไม่สามารถอ่านผลลัพธ์ได้','error');
            }
        },
        error: function() { Swal.fire('ผิดพลาด','ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์','error'); }
    });
}
</script>
