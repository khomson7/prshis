<?php
date_default_timezone_set('Asia/Bangkok');
require_once '../include/Session.php';
require_once '../include/session-sso.php';
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('CA_BREAST', 'VIEW');

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

try {
    $conn = DbUtils::get_hosxp_connection();
    $an   = isset($_REQUEST['an'])  ? trim($_REQUEST['an'])  : '';
    $hn   = KphisQueryUtils::getHnByAn($an);
    $ids  = isset($_REQUEST['id'])  ? (int)$_REQUEST['id']   : 0;

    Session::insertSystemAccessLog(json_encode(['form'=>'CA-BREAST','an'=>$an],JSON_UNESCAPED_UNICODE));

    $rec = null;
    if ($ids) {
        $stmt = $conn->prepare("SELECT * FROM prs_ca_breast WHERE id=:id AND an=:an AND is_deleted=0");
        $stmt->execute(['id'=>$ids,'an'=>$an]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger m-3">Database Error: '.htmlspecialchars($e->getMessage()).'</div>';
}

function v($rec,$key,$default=''){
    return isset($rec[$key])&&$rec[$key]!==null ? $rec[$key] : $default;
}
function chk($rec,$key){
    return isset($rec[$key])&&$rec[$key]==1 ? 'checked' : '';
}
function sel($rec,$key,$val){
    return isset($rec[$key])&&$rec[$key]==$val ? 'selected' : '';
}

$session_name = isset($_SESSION['name'])          ? $_SESSION['name']          : $loginname;
$session_pos  = isset($_SESSION['entryposition']) ? $_SESSION['entryposition'] : '';
$creator_name = v($rec,'created_name',$session_name);
$creator_pos  = v($rec,'created_position',$session_pos);
?>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<style>
.cab-section-title{background:linear-gradient(135deg,#7b1fa2,#4a148c);color:#fff;font-weight:bold;padding:6px 12px;border-radius:4px 4px 0 0;font-size:.92rem;}
.cab-card{border:1px solid #ce93d8;border-radius:4px;margin-bottom:14px;}
.cab-card .cab-body{padding:12px 14px;}
.form-label-sm{font-size:.85rem;font-weight:600;color:#333;margin-bottom:3px;}
.chk-group label{margin-right:16px;font-size:.9rem;cursor:pointer;}
.chk-group input{margin-right:4px;cursor:pointer;}
.lab-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:6px;}
.lab-item{display:flex;flex-direction:column;}
.lab-item label{font-size:.78rem;color:#555;margin-bottom:2px;font-weight:600;}
.lab-item input{border:1px solid #ced4da;border-radius:4px;padding:3px 6px;font-size:.85rem;width:100%;}
.lab-item input:focus{border-color:#7b1fa2;outline:none;box-shadow:0 0 0 2px rgba(123,31,162,.15);}
.hmed-row{display:flex;align-items:center;padding:5px 8px;border-bottom:1px solid #f3e5f5;}
.hmed-row:last-child{border-bottom:none;}
.hmed-row label{flex:1;font-size:.88rem;cursor:pointer;margin:0;}
.hmed-qty{width:80px;border:1px solid #ced4da;border-radius:4px;padding:2px 6px;font-size:.82rem;margin-left:8px;}
.order-note{background:#fff3e0;border:1px dashed #fb8c00;border-radius:6px;padding:10px 14px;font-size:.88rem;}
.monitor-warn{border:2px solid #333;border-radius:4px;padding:8px 10px;text-align:center;font-weight:bold;font-size:.88rem;margin:10px 0;background:#fff8e1;color:#333;}
.monitor-warn u{text-decoration:underline;}
.vs-table{width:100%;border-collapse:collapse;font-size:.78rem;margin-bottom:8px;}
.vs-table th,.vs-table td{border:1px solid #aaa;padding:2px 3px;text-align:center;}
.vs-table th{background:#e8e8e8;font-weight:bold;}
</style>

<div id="formContainer">
<form id="cab_form">
<input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
<input type="hidden" name="id" id="rec_id" value="<?= $ids ?: '' ?>">

<div class="container-fluid">

  <!-- Top bar -->
  <div class="row align-items-center mb-3">
    <div class="col-auto">
      <a href="ca-breast-main.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>"
         class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> กลับ</a>
    </div>
    <div class="col">
      <h5 class="mb-0"><b>CA Breast — Paclitaxel Regimen (AC‑4T)</b>
        <small class="text-muted ml-2" style="font-size:.8rem;">CHEMOTHERAPY ORDER SHEET</small>
        <?php if ($ids): ?>
          <span class="badge badge-secondary ml-1" style="font-size:.72rem;">ID: <?= $ids ?></span>
        <?php else: ?>
          <span class="badge badge-success ml-1" style="font-size:.72rem;">รายการใหม่</span>
        <?php endif; ?>
      </h5>
    </div>
    <?php if ($ids): ?>
    <div class="col-auto">
      <a href="../pdffile/ca-breast-pdf.php?an=<?= urlencode($an) ?>&id=<?= $ids ?>&loginname=<?= urlencode($loginname) ?>"
         target="_blank" class="btn btn-sm btn-info px-3 shadow-sm">
         <i class="fas fa-file-pdf"></i> พิมพ์ PDF
      </a>
    </div>
    <?php endif; ?>
  </div>

  <div class="row">
    <!-- ======== COL LEFT: PROGRESS NOTE ======== -->
    <div class="col-md-4">

      <!-- ECOG -->
      <div class="cab-card">
        <div class="cab-section-title"><i class="fas fa-user-injured mr-1"></i> PROGRESS NOTE</div>
        <div class="cab-body">
          <div class="form-label-sm mb-1">ECOG Performance Status</div>
          <div class="chk-group mb-3">
            <?php foreach([1,2,3,4] as $e): ?>
              <label>
                <input type="radio" name="ecog" value="<?=$e?>" <?= (v($rec,'ecog')==$e)?'checked':'' ?>>
                <?=$e?>
              </label>
            <?php endforeach; ?>
          </div>

          <div class="form-label-sm">Diagnosis</div>
          <textarea name="diagnosis" class="form-control form-control-sm mb-3" rows="3"
            placeholder="วินิจฉัย..."><?= htmlspecialchars(v($rec,'diagnosis')) ?></textarea>

          <div class="form-label-sm">Operation</div>
          <textarea name="operation" class="form-control form-control-sm mb-3" rows="2"
            placeholder="ผ่าตัด..."><?= htmlspecialchars(v($rec,'operation')) ?></textarea>

          <!-- Lab CBC -->
          <div class="form-label-sm mb-1">Lab — CBC</div>
          <div class="lab-grid mb-2">
            <?php
            $cbc_fields=[['lab_hb','Hb'],['lab_wbc','WBC'],['lab_n','N%'],['lab_l','L%'],
                          ['lab_m','M%'],['lab_e','E%'],['lab_b','B%'],
                          ['lab_hct','Hct'],['lab_plt','Plt'],['lab_anc','ANC']];
            foreach($cbc_fields as [$f,$lbl]): ?>
              <div class="lab-item">
                <label><?=$lbl?></label>
                <input type="number" name="<?=$f?>" step="0.01" min="0"
                       value="<?= htmlspecialchars(v($rec,$f)) ?>" placeholder="0">
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Lab Electrolyte -->
          <div class="form-label-sm mb-1">Lab — Electrolyte / Renal</div>
          <div class="lab-grid mb-2">
            <?php
            $elec=[['lab_na','Na⁺'],['lab_k','K⁺'],['lab_bun','BUN'],
                   ['lab_hco3','HCO₃⁻'],['lab_cl','Cl⁻'],['lab_scr','Scr']];
            foreach($elec as [$f,$lbl]): ?>
              <div class="lab-item">
                <label><?=$lbl?></label>
                <input type="number" name="<?=$f?>" step="0.01"
                       value="<?= htmlspecialchars(v($rec,$f)) ?>" placeholder="0">
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Lab LFT -->
          <div class="form-label-sm mb-1">Lab — LFT</div>
          <div class="lab-grid">
            <?php
            $lft=[['lab_alb','Alb'],['lab_glob','Glob'],['lab_tb','TB'],
                  ['lab_db','DB'],['lab_ast','AST'],['lab_alt','ALT'],['lab_alp','ALP']];
            foreach($lft as [$f,$lbl]): ?>
              <div class="lab-item">
                <label><?=$lbl?></label>
                <input type="number" name="<?=$f?>" step="0.01"
                       value="<?= htmlspecialchars(v($rec,$f)) ?>" placeholder="0">
              </div>
            <?php endforeach; ?>
          </div>

        </div>
      </div>
    </div><!-- /col-left -->

    <!-- ======== COL MIDDLE: ORDER FOR CHEMOTHERAPY ======== -->
    <div class="col-md-4">
      <div class="cab-card">
        <div class="cab-section-title"><i class="fas fa-flask mr-1"></i> ORDER FOR CHEMOTHERAPY</div>
        <div class="cab-body">

          <!-- ประเภท -->
          <div class="form-label-sm mb-1">ประเภทการรักษา</div>
          <div class="chk-group mb-3">
            <label>
              <input type="radio" name="order_type" value="adjuvant" <?= (v($rec,'order_type')=='adjuvant')?'checked':'' ?>>
              CBrC14 A (Adjuvant)
            </label>
            <label>
              <input type="radio" name="order_type" value="metastasis" <?= (v($rec,'order_type')=='metastasis')?'checked':'' ?>>
              CBrC24 (Metastasis)
            </label>
          </div>

          <!-- Date & Cycle -->
          <div class="row mb-2">
            <div class="col-7">
              <div class="form-label-sm">วันที่ <span class="text-danger">*</span></div>
              <input type="date" name="order_date" class="form-control form-control-sm"
                     value="<?= v($rec,'order_date',date('Y-m-d')) ?>">
            </div>
            <div class="col-5">
              <div class="form-label-sm">Cycle ที่</div>
              <select name="cycle_no" class="form-control form-control-sm">
                <option value="">--</option>
                <?php for($i=1;$i<=4;$i++): ?>
                  <option value="<?=$i?>" <?= sel($rec,'cycle_no',$i) ?>><?=$i?>/4</option>
                <?php endfor; ?>
              </select>
            </div>
          </div>

          <!-- BW / Ht / BSA -->
          <div class="row mb-2">
            <div class="col-4">
              <div class="form-label-sm">BW (kg)</div>
              <input type="number" name="bw" id="bw" class="form-control form-control-sm"
                     step="0.1" min="0" value="<?= htmlspecialchars(v($rec,'bw')) ?>"
                     oninput="calcBSA()">
            </div>
            <div class="col-4">
              <div class="form-label-sm">Ht (cm)</div>
              <input type="number" name="ht" id="ht" class="form-control form-control-sm"
                     step="0.1" min="0" value="<?= htmlspecialchars(v($rec,'ht')) ?>"
                     oninput="calcBSA()">
            </div>
            <div class="col-4">
              <div class="form-label-sm">BSA (m²)</div>
              <input type="number" name="bsa" id="bsa" class="form-control form-control-sm"
                     step="0.0001" readonly style="background:#f8f9fa"
                     value="<?= htmlspecialchars(v($rec,'bsa')) ?>">
            </div>
          </div>

          <!-- Pre-chemo orders -->
          <div class="form-label-sm mb-1">คำสั่งก่อนเคมี</div>
          <div class="mb-2">
            <label class="d-flex align-items-center" style="gap:8px;">
              <input type="checkbox" name="order_ac4" value="1" <?= chk($rec,'order_ac4') ?>>
              <span style="font-size:.88rem;">AC 4 cycles Before start Paclitaxel</span>
            </label>
            <label class="d-flex align-items-center" style="gap:8px;">
              <input type="checkbox" name="order_cbc_lab" value="1" <?= chk($rec,'order_cbc_lab') ?>>
              <span style="font-size:.88rem;">CBC, LFT, BUN, Electrolyte</span>
            </label>
            <label class="d-flex align-items-center" style="gap:8px;">
              <input type="checkbox" name="order_nss1000" value="1" <?= chk($rec,'order_nss1000') ?>>
              <span style="font-size:.88rem;">NSS 1000 mL IV drip 80 cc/hr</span>
            </label>
          </div>

          <!-- Pre-medication -->
          <div class="form-label-sm">Pre-medication <small class="text-muted">(30 min Before chemotherapy)</small></div>
          <div class="order-note mb-2">
            <label class="d-flex align-items-center mb-1" style="gap:8px;">
              <input type="checkbox" name="premed_dexa_ondan" value="1" <?= v($rec,'premed_dexa_ondan',1)?'checked':'' ?>>
              <span>Dexamethasone 20 mg + Ondansetron 8 mg in D5W 100 mL IV drip in 30 min</span>
            </label>
            <label class="d-flex align-items-center mb-1" style="gap:8px;">
              <input type="checkbox" name="premed_cpm" value="1" <?= v($rec,'premed_cpm',1)?'checked':'' ?>>
              <span>CPM 10 mg IV push</span>
            </label>
            <label class="d-flex align-items-center mb-1" style="gap:8px;">
              <input type="checkbox" name="premed_famotidine" value="1" <?= v($rec,'premed_famotidine',1)?'checked':'' ?>>
              <span>Famotidine (20) 1 tab oral stat</span>
            </label>
            <input type="text" name="premed_other" class="form-control form-control-sm mt-1"
                   placeholder="อื่นๆ..." value="<?= htmlspecialchars(v($rec,'premed_other')) ?>">
          </div>

          <!-- Chemotherapy Order -->
          <div class="form-label-sm mb-1">Chemotherapy Order</div>
          <div class="order-note mb-2">
            <div class="row align-items-center">
              <div class="col-auto" style="font-size:.88rem;">1. Paclitaxel</div>
              <div class="col">
                <input type="number" name="paclitaxel_dose" id="paclitaxel_dose"
                       class="form-control form-control-sm"
                       step="0.01" min="0" placeholder="mg (auto)"
                       value="<?= htmlspecialchars(v($rec,'paclitaxel_dose')) ?>">
              </div>
              <div class="col-auto" style="font-size:.82rem;color:#555;">mg</div>
            </div>
            <small class="text-muted d-block mt-1">
              (175 mg/m²) in NSS 500 mL IV drip in 3 hr via Infusion pump
            </small>
          </div>

          <div class="monitor-warn">
            ***<u>Monitor vital signs first 15 min</u><br>
            <u>and every 15 min &times; 4 times after starting Paclitaxel</u>***
          </div>

          <!-- นัดครั้งต่อไป -->
          <div class="form-label-sm mt-2">นัดครั้งต่อไปวันที่</div>
          <input type="date" name="next_appt_date" class="form-control form-control-sm mb-2"
                 value="<?= v($rec,'next_appt_date') ?>">

          <!-- Follow-up LAB -->
          <div class="form-label-sm mb-1">LAB (Follow-up)</div>
          <div style="display:flex;flex-wrap:wrap;gap:4px 14px;font-size:.88rem;">
            <?php
            $fu_labs=[['fu_lab_cbc','CBC'],['fu_lab_electrolyte','Electrolyte'],
                      ['fu_lab_lft','LFT'],['fu_lab_bun','BUN'],['fu_lab_scr','Scr'],
                      ['fu_lab_ua','UA'],['fu_lab_cea','CEA'],['fu_lab_cxrpa','CXR PA']];
            foreach($fu_labs as [$f,$lbl]): ?>
              <label><input type="checkbox" name="<?=$f?>" value="1" <?= chk($rec,$f) ?>>&nbsp;<?=$lbl?></label>
            <?php endforeach; ?>
          </div>
          <input type="text" name="fu_lab_other" class="form-control form-control-sm mt-1"
                 placeholder="Other..." value="<?= htmlspecialchars(v($rec,'fu_lab_other')) ?>">

        </div>
      </div>
    </div><!-- /col-middle -->

    <!-- ======== COL RIGHT: ORDER FOR CONTINUATION ======== -->
    <div class="col-md-4">
      <div class="cab-card">
        <div class="cab-section-title"><i class="fas fa-pills mr-1"></i> ORDER FOR CONTINUATION</div>
        <div class="cab-body p-2">

          <div class="alert alert-light border mb-2 py-2" style="font-size:.85rem;">
            <i class="fas fa-utensils mr-1"></i> Regular diet &nbsp;&nbsp;
            <i class="fas fa-heartbeat mr-1"></i> Record V/S as usual
          </div>

          <!-- V/S Monitoring Grid -->
          <div class="form-label-sm mb-1">บันทึก Vital Signs (ทุก 15 นาที)</div>
          <table class="vs-table mb-3">
            <thead>
              <tr>
                <th style="width:28%">เวลา</th>
                <th>BP<br><small>(mmHg)</small></th>
                <th>PR<br><small>(/min)</small></th>
                <th>RR<br><small>(/min)</small></th>
                <th>SpO₂<br><small>(%)</small></th>
                <th>T<br><small>(°C)</small></th>
              </tr>
            </thead>
            <tbody>
              <?php
              $vs_labels = ['ก่อนให้ยา','15 นาที','30 นาที','45 นาที','60 นาที','75 นาที'];
              foreach($vs_labels as $vl): ?>
                <tr>
                  <td style="text-align:left;padding:2px 4px;"><?= $vl ?></td>
                  <td style="min-width:40px;">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <!-- Medication / Home Med -->
          <div class="form-label-sm mb-1">Medication / Home Med</div>
          <div style="border:1px solid #ce93d8;border-radius:4px;overflow:hidden;font-size:.85rem;">

            <!-- Fixed meds -->
            <?php
            $fixed_meds=[
              ['hmed_dexa4',    'Dexamethasone 4 mg&nbsp;<b>#6</b>',      'Sig 1x2 po pc &nbsp;Day 2–4'],
              ['hmed_ondan8',   'Ondansetron 8 mg&nbsp;<b>#6</b>',        'Sig 1x2 po ac &nbsp;Day 2–4'],
              ['hmed_metoclo',  'Metoclopramide 10 mg&nbsp;<b>#20</b>',   'Sig 1x3 po ac prn for N/V'],
              ['hmed_tramadol', 'Tramadol 50 mg&nbsp;<b>#20</b>',         'Sig 1×prn q 6 hr for pain'],
              ['hmed_senokot',  'Senokot&nbsp;<b>#20</b>',                'Sig 2×hs prn for constipation'],
            ];
            foreach($fixed_meds as [$f,$label,$sig]): ?>
              <div class="hmed-row">
                <label>
                  <input type="checkbox" name="<?=$f?>" value="1" <?= chk($rec,$f) ?>>
                  &nbsp;<?= $label ?>
                </label>
                <small class="text-muted ml-2" style="font-size:.78rem;"><?= $sig ?></small>
              </div>
            <?php endforeach; ?>

            <!-- Meds with qty -->
            <?php
            $qty_meds=[
              ['hmed_multivit','hmed_multivit_qty','Multivitamin','Sig 1x3 po pc'],
              ['hmed_ff200',   'hmed_ff200_qty',  'FF 200 mg',   'Sig 1x3 po pc'],
              ['hmed_lorazepam','hmed_lorazepam_qty','Lorazepam 0.5 mg','Sig 1x1 po hs for insomnia'],
            ];
            foreach($qty_meds as [$f,$fq,$label,$sig]): ?>
              <div class="hmed-row">
                <label>
                  <input type="checkbox" name="<?=$f?>" value="1" <?= chk($rec,$f) ?>>
                  &nbsp;<?= $label ?>
                </label>
                <input type="text" name="<?=$fq?>" class="hmed-qty"
                       placeholder="#..." value="<?= htmlspecialchars(v($rec,$fq)) ?>">
                <small class="text-muted ml-1" style="font-size:.75rem;"><?= $sig ?></small>
              </div>
            <?php endforeach; ?>

            <!-- Extra meds -->
            <?php for($x=1;$x<=3;$x++): ?>
              <div class="hmed-row flex-column align-items-start" style="gap:4px;">
                <div class="d-flex align-items-center w-100">
                  <input type="checkbox" name="hmed_extra<?=$x?>_chk" value="1" class="mr-1">
                  <input type="text" name="hmed_extra<?=$x?>"
                         class="form-control form-control-sm"
                         placeholder="ยาเพิ่มเติม <?=$x?>..."
                         value="<?= htmlspecialchars(v($rec,"hmed_extra$x")) ?>">
                </div>
                <div class="d-flex align-items-center w-100 ml-3">
                  <small class="mr-2" style="font-size:.78rem;">Sig:</small>
                  <input type="text" name="hmed_extra<?=$x?>_sig"
                         class="form-control form-control-sm"
                         placeholder="วิธีใช้..."
                         value="<?= htmlspecialchars(v($rec,"hmed_extra{$x}_sig")) ?>">
                </div>
              </div>
            <?php endfor; ?>

          </div><!-- /home-med box -->

        </div>
      </div>
    </div><!-- /col-right -->
  </div><!-- /row -->

  <!-- ======== Signature ======== -->
  <div class="cab-card">
    <div class="cab-section-title"><i class="fas fa-pen-nib mr-1"></i> ผู้บันทึก / แพทย์ผู้สั่งใช้</div>
    <div class="cab-body">
      <div class="row">
        <div class="col-md-4">
          <div class="form-label-sm">ชื่อผู้บันทึก</div>
          <input type="text" class="form-control form-control-sm" readonly
                 value="<?= htmlspecialchars($creator_name) ?>" style="background:#f8f9fa;">
          <input type="hidden" name="created_name" value="<?= htmlspecialchars($creator_name) ?>">
        </div>
        <div class="col-md-4">
          <div class="form-label-sm">ตำแหน่ง</div>
          <input type="text" class="form-control form-control-sm" readonly
                 value="<?= htmlspecialchars($creator_pos) ?>" style="background:#f8f9fa;">
          <input type="hidden" name="created_position" value="<?= htmlspecialchars($creator_pos) ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <small class="text-muted">
            <i class="fas fa-info-circle mr-1"></i>
            บันทึกโดย: <b><?= htmlspecialchars($loginname) ?></b>
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Save button -->
  <div class="row mb-5">
    <div class="col text-center">
      <?php if (Session::checkPermission('CA_BREAST','EDIT') && ReportQueryUtils::checkReadOnly($an)): ?>
        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
          <i class="fas fa-save"></i> บันทึกข้อมูล
        </button>
      <?php endif; ?>
    </div>
  </div>

</div><!-- /container-fluid -->
</form>
</div><!-- /formContainer -->

<script>
// คำนวณ BSA (Mosteller formula)
function calcBSA() {
    var bw = parseFloat(document.getElementById('bw').value)||0;
    var ht = parseFloat(document.getElementById('ht').value)||0;
    if (bw > 0 && ht > 0) {
        var bsa = Math.sqrt((bw * ht) / 3600);
        document.getElementById('bsa').value = bsa.toFixed(4);
        // คำนวณ Paclitaxel dose = 175 mg/m²
        var dose = (175 * bsa).toFixed(2);
        var doseEl = document.getElementById('paclitaxel_dose');
        if (!doseEl.dataset.manual || doseEl.dataset.manual === '0') {
            doseEl.value = dose;
        }
    }
}

document.getElementById('paclitaxel_dose').addEventListener('input', function(){
    this.dataset.manual = '1';
});

// Form submit
document.getElementById('cab_form').addEventListener('submit', function(e){
    e.preventDefault();
    var dateVal = document.querySelector('[name="order_date"]').value;
    if (!dateVal) {
        Swal.fire('กรุณาระบุ','กรุณากรอกวันที่สั่งยา','warning'); return;
    }
    var data = new FormData(this);
    Swal.fire({
        title:'กำลังบันทึก...', allowOutsideClick:false,
        didOpen: function(){ Swal.showLoading(); }
    });
    $.ajax({
        url:'ca-breast-save.php', type:'POST', data: data, processData:false, contentType:false,
        success: function(resp){
            try {
                var d = (typeof resp==='string') ? JSON.parse(resp) : resp;
                if (d.status==='success') {
                    Swal.fire({icon:'success',title:'บันทึกสำเร็จ',showConfirmButton:false,timer:1500})
                        .then(function(){
                            window.location.href='ca-breast-main.php?an='+encodeURIComponent('<?= addslashes($an) ?>')
                                +'&loginname='+encodeURIComponent('<?= addslashes($loginname) ?>')
                                +'&new_id='+d.id;
                        });
                } else {
                    Swal.fire('ผิดพลาด', d.message||'ไม่สามารถบันทึกได้','error');
                }
            } catch(ex){ Swal.fire('ผิดพลาด','ไม่สามารถอ่านผลลัพธ์','error'); }
        },
        error: function(){ Swal.fire('ผิดพลาด','เกิดข้อผิดพลาด network','error'); }
    });
});
</script>

