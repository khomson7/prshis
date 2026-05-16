<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck     = Session::checkPermissionAndShowMessage('IMAGE_ANNOT', 'VIEW');
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
    'form' => 'IMAGE-ANNOT-FORM',
    'an'   => $an,
], JSON_UNESCAPED_UNICODE));

// ---- หา record ล่าสุดของ AN นี้ ----
$ids = null;
$stmt_chk = $conn->prepare("SELECT id FROM prs_image_annot WHERE an = :an AND is_deleted = 0 ORDER BY id DESC LIMIT 1");
$stmt_chk->execute(['an' => $an]);
$row_chk = $stmt_chk->fetch();
if ($row_chk) $ids = $row_chk['id'];

// ---- โหลดข้อมูลสำหรับ re-edit ----
$rec        = null;
$items      = [];
$note_saved = '';

if ($ids) {
    $stmt_m = $conn->prepare("SELECT * FROM prs_image_annot WHERE id = :id");
    $stmt_m->execute(['id' => $ids]);
    $rec = $stmt_m->fetch(PDO::FETCH_ASSOC);
    if ($rec) $note_saved = $rec['note'] ?? '';

    // โหลด items — ดึง image_data ด้วยเพื่อ embed base64 ลงหน้า (แก้ปัญหา canvas tainted)
    $stmt_i = $conn->prepare("SELECT id, sort_order, image_type, original_name,
                                      canvas_w, canvas_h, svg_data, image_data
                                FROM prs_image_annot_item
                               WHERE annot_id = :annot_id
                               ORDER BY sort_order ASC");
    $stmt_i->execute(['annot_id' => $ids]);
    while ($row_i = $stmt_i->fetch(PDO::FETCH_ASSOC)) {
        $bin = $row_i['image_data'];
        if (is_resource($bin)) $bin = stream_get_contents($bin);
        $mime = $row_i['image_type'] ?: 'image/png';
        $row_i['image_b64'] = $bin ? ('data:' . $mime . ';base64,' . base64_encode($bin)) : '';
        unset($row_i['image_data']); // ไม่ส่ง binary ต่อไป ใช้ b64 แทน
        $items[] = $row_i;
    }
}
?>
<script src="../include/fabric.js"></script>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
<style>
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
<form id="image_annot_form">
    <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
    <input type="hidden" name="id" id="rec_id" value="<?= htmlspecialchars($ids ?? '') ?>">

    <div class="container-fluid">

        <!-- ---- Top bar ---- -->
        <div class="row align-items-center mb-3">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()">
                    <i class="fas fa-times"></i> ปิดหน้านี้
                </button>
            </div>
            <div class="col">
                <h5 class="mb-0"><b>บันทึกภาพและ Annotation</b></h5>
            </div>
            <div class="col-auto">
                <?php if ($ids): ?>
                <a href="../pdffile/image-annot-pdf.php?an=<?= urlencode($an) ?>&id=<?= $ids ?>&loginname=<?= urlencode($loginname) ?>"
                   target="_blank" class="btn btn-sm btn-info px-3 shadow-sm">
                    <i class="fas fa-file-pdf"></i> พิมพ์ PDF
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- ---- Upload zone ---- -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold py-2">
                <i class="fas fa-images"></i> ภาพที่บันทึก
            </div>
            <div class="card-body">

                <!-- Thumbnail strip -->
                <div id="thumb-strip">
                    <span id="thumb-empty" class="text-muted small" style="align-self:center">
                        ยังไม่มีภาพ — กด "เพิ่มภาพ" หรือลากไฟล์มาวาง
                    </span>
                </div>

                <!-- Drop zone + add button -->
                <div id="drop-zone" onclick="document.getElementById('file-input').click()">
                    <i class="fas fa-camera fa-lg mr-1"></i>
                    คลิกหรือลากไฟล์ภาพมาวางที่นี่ (เพิ่มได้ครั้งละหลายภาพ)<br>
                    <small class="text-muted">PNG, JPG, WEBP | แต่ละภาพไม่เกิน 10 MB</small>
                </div>
                <input type="file" id="file-input" accept="image/*" multiple style="display:none">

            </div>
        </div>

        <!-- ---- Canvas Editor ---- -->
        <div class="card mb-3" id="editor-card" style="display:none">
            <div class="card-header font-weight-bold py-2 d-flex align-items-center">
                <i class="fas fa-paint-brush mr-2"></i>
                วาด Annotation — ภาพที่ <span id="editing-num">1</span>
                <span id="editing-name" class="text-muted small ml-2"></span>
            </div>
            <div class="card-body">

                <!-- Toolbar -->
                <div id="draw-toolbar">
                    <button type="button" class="tb-btn active" id="btn-pen"
                            onclick="setTool('pen')" title="ปากกาวาดอิสระ">
                        <i class="fas fa-pen"></i> ปากกา
                    </button>
                    <button type="button" class="tb-btn" id="btn-line"
                            onclick="setTool('line')" title="เส้นตรง">
                        <i class="fas fa-minus"></i> เส้น
                    </button>
                    <button type="button" class="tb-btn" id="btn-arrow"
                            onclick="setTool('arrow')" title="ลูกศร">
                        &#10148; ลูกศร
                    </button>
                    <button type="button" class="tb-btn" id="btn-rect"
                            onclick="setTool('rect')" title="สี่เหลี่ยม">
                        <i class="far fa-square"></i> กล่อง
                    </button>
                    <button type="button" class="tb-btn" id="btn-circle"
                            onclick="setTool('circle')" title="วงรี">
                        <i class="far fa-circle"></i> วงกลม
                    </button>
                    <button type="button" class="tb-btn" id="btn-text"
                            onclick="setTool('text')" title="พิมพ์ข้อความ (ดับเบิ้ลคลิก)">
                        <i class="fas fa-font"></i> ข้อความ
                    </button>
                    <div class="tb-sep"></div>
                    <label class="tb-btn" style="cursor:pointer" title="สี">
                        <i class="fas fa-palette"></i>
                        <input type="color" id="colorPicker" value="#e63946"
                               style="width:24px;height:22px;border:none;padding:0;cursor:pointer">
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

                <!-- Canvas -->
                <div id="canvas-wrap">
                    <div id="canvas-placeholder">เลือกภาพจาก thumbnail ด้านบนเพื่อเริ่มวาด</div>
                    <canvas id="c"></canvas>
                </div>
                <div class="text-muted small mt-1">
                    <i class="fas fa-info-circle"></i>
                    เลือก tool "ข้อความ" แล้ว <b>ดับเบิ้ลคลิก</b> บนภาพเพื่อพิมพ์ข้อความ &nbsp;|&nbsp;
                    การวาดจะถูกบันทึกอัตโนมัติเมื่อสลับภาพหรือกดบันทึก
                </div>

            </div>
        </div>

        <!-- ---- Note ---- -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold py-2">
                <i class="fas fa-sticky-note"></i> บันทึกเพิ่มเติม
            </div>
            <div class="card-body">
                <textarea class="form-control" name="note" id="inp_note" rows="3"
                          placeholder="บันทึกรายละเอียดเพิ่มเติม..."><?= htmlspecialchars($note_saved) ?></textarea>
            </div>
        </div>

        <!-- ---- Save button ---- -->
        <div class="row mb-5">
            <div class="col text-center">
                <?php if (Session::checkPermission('IMAGE_ANNOT', 'EDIT') && ReportQueryUtils::checkReadOnly($an)): ?>
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                    <i class="fas fa-save"></i> บันทึกข้อมูล
                </button>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /container -->
</form>
</div><!-- /formContainer -->

<script>
// ============================================================
// Image list — แต่ละ element:
//   { b64: 'data:...', svgData: '...', name: '...', itemId: null/id }
// ============================================================
var imageList   = [];
var currentIdx  = -1;   // index ที่กำลัง active บน canvas
var canvas;             // Fabric.js instance
var currentTool = 'pen';
var isDown      = false;
var startX, startY, activeShape;
var undoStack   = [];

// ============================================================
// โหลด existing items จาก PHP (re-edit)
// ============================================================
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
            annotatedB64: '',
        });
    });
    renderThumbs();
    if (imageList.length > 0) selectImage(0);
})();
<?php endif; ?>

// ============================================================
// File input / Drag-drop
// ============================================================
document.getElementById('file-input').addEventListener('change', function(e) {
    handleFiles(Array.from(e.target.files));
    this.value = '';
});
var dz = document.getElementById('drop-zone');
dz.addEventListener('dragover',  function(e){ e.preventDefault(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave', function()  { dz.classList.remove('drag-over'); });
dz.addEventListener('drop', function(e) {
    e.preventDefault(); dz.classList.remove('drag-over');
    handleFiles(Array.from(e.dataTransfer.files).filter(function(f){
        return f.type.startsWith('image/');
    }));
});

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
            // auto-select ภาพแรกที่เพิ่ม
            if (imageList.length === 1) selectImage(0);
        };
        r.readAsDataURL(file);
    });
}

// ============================================================
// Thumbnails
// ============================================================
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
        // ถ้ามี svgData วาดทับ ใช้ภาพ b64 ก่อน (svg จะแสดงบน canvas)
        im.src   = img.b64;
        im.alt   = img.name;

        var num  = document.createElement('span');
        num.className = 'thumb-num';
        num.textContent = idx + 1;

        var del  = document.createElement('button');
        del.type = 'button';
        del.className = 'thumb-del';
        del.innerHTML = '&times;';
        del.onclick = function(e) { e.stopPropagation(); removeImage(idx); };

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

// ============================================================
// เลือกภาพ → load ลง canvas
// ============================================================
function selectImage(idx) {
    // บันทึก SVG ของภาพเดิมก่อนสลับ
    if (currentIdx >= 0 && currentIdx !== idx && canvas) {
        saveSvgToCurrent();
    }
    currentIdx = idx;
    renderThumbs();

    var img = imageList[idx];
    document.getElementById('editor-card').style.display = '';
    document.getElementById('editing-num').textContent = idx + 1;
    document.getElementById('editing-name').textContent = img.name ? ('(' + img.name + ')') : '';

    // โหลดภาพขึ้น canvas
    fabric.Image.fromURL(img.b64, function(fimg) {
        var maxW  = document.getElementById('canvas-wrap').clientWidth || 700;

        // ถ้ามีขนาด canvas ที่บันทึกไว้ (re-edit) ให้ใช้ขนาดเดิม เพื่อ annotation ตรงตำแหน่ง
        var w, h, scale;
        if (img.canvasW && img.canvasH) {
            w     = img.canvasW;
            h     = img.canvasH;
            scale = w / fimg.width;
        } else {
            scale = Math.min(1, maxW / fimg.width);
            w     = Math.round(fimg.width  * scale);
            h     = Math.round(fimg.height * scale);
        }

        if (canvas) canvas.dispose();
        canvas = new fabric.Canvas('c', {
            width: w, height: h,
            isDrawingMode: true,
            selection: false,
            preserveObjectStacking: true,
        });
        applyBrush();
        bindCanvasEvents();
        undoStack = [];

        // excludeFromExport: true → canvas.toSVG() จะไม่ embed ภาพนี้ซ้ำ
        fimg.set({ scaleX: scale, scaleY: scale, selectable: false, evented: false,
                   excludeFromExport: true });
        canvas.add(fimg);
        canvas.sendToBack(fimg);

        // restore SVG annotation ถ้ามี
        // เพิ่ม object ทีละชิ้น (ไม่ใช้ groupSVGElements) เพื่อรักษาตำแหน่งที่ถูกต้อง
        if (img.svgData && img.svgData.trim() !== '') {
            fabric.loadSVGFromString(img.svgData, function(objects) {
                if (objects && objects.length > 0) {
                    objects.forEach(function(obj) {
                        if (!obj || obj.type === 'image') return; // ข้าม bg image ที่อาจ embed ใน SVG เก่า
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
    // base64 Data URI — ไม่ต้อง crossOrigin
}

// ============================================================
// SVG helpers
// ============================================================
function saveSvgToCurrent() {
    if (currentIdx < 0 || !canvas) return;

    // บันทึก SVG (เฉพาะ annotation — bg image ถูก exclude แล้ว)
    // เก็บ canvasW/canvasH ไว้ด้วยใน custom attribute เพื่อ restore ตำแหน่งได้ถูกต้อง
    var svgStr = canvas.toSVG();
    // inject ขนาด canvas ลงใน SVG comment เผื่อตรวจสอบ
    imageList[currentIdx].svgData    = svgStr;
    imageList[currentIdx].canvasW    = canvas.getWidth();
    imageList[currentIdx].canvasH    = canvas.getHeight();

    // capture ภาพแบน (image + annotation รวมกัน) สำหรับ PDF
    try {
        imageList[currentIdx].annotatedB64 = canvas.toDataURL({ format: 'png', multiplier: 1 });
    } catch(e) {
        imageList[currentIdx].annotatedB64 = '';
    }
}

// ============================================================
// Tool
// ============================================================
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
    var color   = document.getElementById('colorPicker').value;
    var width   = parseInt(document.getElementById('strokeWidth').value);
    canvas.freeDrawingBrush.color = color;
    canvas.freeDrawingBrush.width = width;
}

document.getElementById('strokeWidth').addEventListener('input', function(){
    document.getElementById('strokeVal').textContent = this.value;
    applyBrush();
});
document.getElementById('colorPicker').addEventListener('input', applyBrush);

// ============================================================
// Mouse events (line / arrow / rect / circle)
// ============================================================
function bindCanvasEvents() {
    canvas.on('mouse:down', function(opt) {
        var tools = ['line','arrow','rect','circle'];
        if (tools.indexOf(currentTool) === -1) return;
        isDown = true;
        var ptr = canvas.getPointer(opt.e);
        startX = ptr.x; startY = ptr.y;
        var color = document.getElementById('colorPicker').value;
        var sw    = parseInt(document.getElementById('strokeWidth').value);

        if (currentTool === 'line' || currentTool === 'arrow') {
            activeShape = new fabric.Line([startX,startY,startX,startY],
                { stroke: color, strokeWidth: sw, selectable: true });
        } else if (currentTool === 'rect') {
            activeShape = new fabric.Rect({ left:startX, top:startY, width:0, height:0,
                fill:'transparent', stroke:color, strokeWidth:sw, selectable:true });
        } else if (currentTool === 'circle') {
            activeShape = new fabric.Ellipse({ left:startX, top:startY, rx:0, ry:0,
                fill:'transparent', stroke:color, strokeWidth:sw, selectable:true });
        }
        if (activeShape) canvas.add(activeShape);
    });

    canvas.on('mouse:move', function(opt) {
        if (!isDown || !activeShape) return;
        var ptr = canvas.getPointer(opt.e);
        if (currentTool==='line'||currentTool==='arrow') {
            activeShape.set({ x2:ptr.x, y2:ptr.y });
        } else if (currentTool==='rect') {
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
        isDown=false; activeShape=null; saveUndo();
    });

    // ข้อความ: ดับเบิ้ลคลิก
    canvas.on('mouse:dblclick', function(opt) {
        if (currentTool !== 'text') return;
        var ptr   = canvas.getPointer(opt.e);
        var color = document.getElementById('colorPicker').value;
        var sw    = parseInt(document.getElementById('strokeWidth').value);
        var txt   = new fabric.IText('ข้อความ', {
            left:ptr.x, top:ptr.y,
            fontSize: 14 + sw * 2, fill:color,
            fontFamily:'Arial', selectable:true, editable:true,
        });
        canvas.add(txt);
        canvas.setActiveObject(txt);
        txt.enterEditing();
        canvas.renderAll();
    });

    canvas.on('object:added',    saveUndo);
    canvas.on('object:modified', saveUndo);
}

// ============================================================
// Undo / Clear
// ============================================================
function saveUndo() {
    if (!canvas) return;
    var s = JSON.stringify(canvas.toJSON());
    if (undoStack[undoStack.length-1] !== s) undoStack.push(s);
    if (undoStack.length > 40) undoStack.shift();
}
function undoCanvas() {
    if (!canvas || undoStack.length <= 1) return;
    undoStack.pop();
    canvas.loadFromJSON(undoStack[undoStack.length-1], function(){ canvas.renderAll(); });
}
function clearCanvas() {
    if (!canvas) return;
    Swal.fire({
        title:'ล้างการวาดทั้งหมด?', icon:'warning',
        showCancelButton:true, confirmButtonColor:'#dc3545',
        confirmButtonText:'ล้าง', cancelButtonText:'ยกเลิก',
    }).then(function(r) {
        if (!r.isConfirmed) return;
        var bg = canvas.getObjects().filter(function(o){ return !o.selectable; });
        canvas.clear();
        bg.forEach(function(o){ canvas.add(o); canvas.sendToBack(o); });
        canvas.renderAll(); undoStack = [];
    });
}

// ============================================================
// Submit
// ============================================================
$('#image_annot_form').on('submit', function(e) {
    e.preventDefault();

    if (imageList.length === 0) {
        Swal.fire('แจ้งเตือน','กรุณาเพิ่มภาพอย่างน้อย 1 ภาพ','warning');
        return;
    }

    // บันทึก SVG ของภาพที่กำลังแสดงอยู่
    saveSvgToCurrent();

    // เตรียมข้อมูล
    var payload = imageList.map(function(img, i) {
        return {
            sort_order:   i,
            b64:          img.b64,
            svgData:      img.svgData      || '',
            annotatedB64: img.annotatedB64 || '',
            canvasW:      img.canvasW      || 0,
            canvasH:      img.canvasH      || 0,
            name:         img.name         || '',
            itemId:       img.itemId       || null,
        };
    });

    var id  = $('#rec_id').val();
    var url = id ? 'form-image-annot-update.php' : 'form-image-annot-save.php';

    var postData = {
        an:     $('[name="an"]').val(),
        id:     id,
        note:   $('#inp_note').val(),
        images: JSON.stringify(payload),
    };

    Swal.fire({ title:'กำลังบันทึก...', allowOutsideClick:false,
                didOpen:function(){ Swal.showLoading(); } });

    $.ajax({
        url:  url,
        type: 'POST',
        data: postData,
        success: function(resp) {
            try {
                var data = (typeof resp==='string') ? JSON.parse(resp) : resp;
                if (data.status === 'success') {
                    if (data.id) $('#rec_id').val(data.id);
                    Swal.fire('สำเร็จ','บันทึกข้อมูลเรียบร้อยแล้ว','success').then(function(){
                        window.location.reload(true);
                    });
                } else {
                    Swal.fire('ข้อผิดพลาด', data.message||'เกิดข้อผิดพลาด', 'error');
                }
            } catch(ex) {
                Swal.fire('ข้อผิดพลาด','ไม่สามารถอ่านผลลัพธ์จากเซิร์ฟเวอร์','error');
            }
        },
        error: function() {
            Swal.fire('ข้อผิดพลาด','ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์','error');
        }
    });
});
</script>
