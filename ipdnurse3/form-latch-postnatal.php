<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;
require_once '../mains/main-report.php';
Session::checkPermissionAndShowMessage('FORM_LATCH_POSTNATAL', 'VIEW');
require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';
try {
    $conn = DbUtils::get_hosxp_connection();
    $an   = $_REQUEST['an'];
    Session::insertSystemAccessLog(json_encode(['form'=>'LATCH-POSTNATAL','an'=>$an],JSON_UNESCAPED_UNICODE));
    $stmt = $conn->prepare("SELECT * FROM `prs_latch_postnatal` WHERE an=:an LIMIT 1");
    $stmt->execute(['an'=>$an]);
    $row = $stmt->fetch();
} catch (Exception $e) {
    echo '<div class="alert alert-danger">DB Error: '.htmlspecialchars($e->getMessage()).'</div>';
}
function sv($row,$key,$def=null){ return isset($row[$key]) ? $row[$key] : $def; }
// เวลาเริ่มต้น: ครั้งคี่ 06:00/12:00/18:00 ครั้งคู่ 06:00/13:00/19:00
$defaultTimes = [
  1 => ['00:01','08:00','16:00'],
  2 => ['00:01','08:00','16:00'],
  3 => ['00:01','08:00','16:00'],
  4 => ['00:01','08:00','16:00'],
];
$latchItems = [
  ['key'=>'l','label'=>'L = Latch','max'=>2],
  ['key'=>'a','label'=>'A = Audible','max'=>2],
  ['key'=>'t','label'=>'T = Type of nipple','max'=>2],
  ['key'=>'c','label'=>'C = Comfort','max'=>2],
  ['key'=>'h','label'=>'H = Hold','max'=>2],
  ['key'=>'milk_level','label'=>'ระดับน้ำนม','max'=>3],
];
$check_ = ReportQueryUtils::getProduction(26);
?>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
<style>
.latch-ref th,.latch-ref td{border:1px solid #aaa;padding:5px 4px;vertical-align:middle;font-size:0.82rem;}
.latch-ref thead th{background:#007bff;color:#fff;text-align:center;}
.latch-grid th,.latch-grid td{border:1px solid #aaa;padding:3px 2px;vertical-align:middle;font-size:0.78rem;text-align:center;}
.latch-grid thead th{background:#007bff;color:#fff;}
.latch-grid .row-label{text-align:left;font-weight:bold;padding:4px 6px;background:#e3f2fd;white-space:nowrap;}
.latch-grid .day-header{background:#1565c0;color:#fff;font-weight:bold;}
.latch-grid .time-header{background:#1976d2;color:#fff;}
.latch-grid .total-row{background:#E1F5FE;font-weight:bold;}
.r-grp{display:flex;flex-wrap:wrap;gap:3px;justify-content:center;}
.r-grp label{display:flex;flex-direction:column;align-items:center;font-size:0.7rem;cursor:pointer;padding:2px 4px;border-radius:3px;border:1px solid #ccc;min-width:28px;}
.r-grp label:hover{background:#bbdefb;}
.r-grp input[type=radio]:checked+span{font-weight:bold;color:#007bff;}
.score-badge{display:inline-block;min-width:28px;text-align:center;padding:1px 6px;border-radius:12px;font-weight:bold;background:#007bff;color:#fff;font-size:0.85rem;}
.t-input{width:72px;font-size:0.78rem;}
.section-header{background:#007bff;color:#fff;padding:7px 12px;border-radius:6px 6px 0 0;font-weight:bold;}
.stress-table th,.stress-table td{border:1px solid #aaa;padding:5px;vertical-align:middle;font-size:0.84rem;}
.stress-table thead th{background:#5c6bc0;color:#fff;text-align:center;}
.yn-radio label{margin-right:14px;cursor:pointer;}
</style>

<div id="formContainer">
<form id="latch_form">
<input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
<div class="container-fluid">

  <!-- Header -->
  <div class="row align-items-center mb-3">
    <div class="col-auto">
      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()">
        <i class="fas fa-times"></i> ปิดหน้านี้
      </button>
    </div>
    <div class="col">
      <h5 class="mb-0"><b>แบบประเมิน LATCH score และสุขภาพจิตมารดาหลังคลอด</b>
        <small class="text-muted">FM-NSO-OBG-015-01</small>
      </h5>
    </div>
    <div class="col-auto">
      <a href="../pdffile/form-latch-postnatal-pdf.php?an=<?= htmlspecialchars($an) ?>" target="_blank" class="btn btn-sm btn-info shadow-sm text-white">
        <i class="fas fa-print"></i> พิมพ์ PDF
      </a>
    </div>
  </div>

  <!-- ส่วนที่ 1: LATCH Reference Table -->
  <div class="card mb-3">
    <div class="section-header"><i class="fas fa-baby"></i> ส่วนที่ 1 — LATCH Score เกณฑ์การประเมิน</div>
    <div class="card-body p-2">
      <div class="table-responsive">
        <table class="latch-ref w-100">
          <thead><tr><th style="width:20%">การประเมิน</th><th style="width:27%">2 คะแนน</th><th style="width:27%">1 คะแนน</th><th style="width:26%">0 คะแนน</th></tr></thead>
          <tbody>
            <tr><td><b>L = Latch</b> การอมหัวนม</td><td>อมลึกถึงลานนม</td><td>มีการพยายามช่วยจับหัวนมเข้าปากทารก อมเฉพาะหัวนม</td><td>ซึม ไม่ยอมดูดนม</td></tr>
            <tr><td><b>A = Audible</b> เสียงกลืนน้ำนม</td><td>ได้ยินเสียงกลืนเป็นช่วงๆ</td><td>นานๆ กลืนครั้งและกลืนเฉพาะมีการกระตุ้น</td><td>ไม่ได้ยินเสียงกลืน</td></tr>
            <tr><td><b>T = Type of nipple</b> ลักษณะหัวนม</td><td>หัวนมยื่นออกมาดี หรือหลังจากการกระตุ้น</td><td>หัวนมแบนหรือยื่นออกมาเล็กน้อย</td><td>หัวนมบุ๋มเข้าไป</td></tr>
            <tr><td><b>C = Comfort</b> ความสุขสบาย</td><td>เต้านมยืดหยุ่นดีหัวนมปกติแม่รู้สึกสบายขณะให้นมบุตร</td><td>แม่ปวดเล็กน้อยหรือปานกลาง</td><td>แม่ไม่สบายคัดตึงด้านนม</td></tr>
            <tr><td><b>H = Hold</b> การอุ้มทารก</td><td>ท่าอุ้มถูกต้องเจ้าหน้าที่ไม่ต้องช่วยเหลือ</td><td>แม่ต้องการความช่วยเหลือจากเจ้าหน้าที่ในการจัดท่า</td><td>เจ้าหน้าที่ช่วยเหลือทุกอย่างในการจัดท่าให้นม</td></tr>
            <tr><td><b>ระดับน้ำนม</b> (0-3)</td>
              <td colspan="3">
                <span class="badge badge-secondary mr-1">0</span>เมืื่อบีบลานนมแล้วไม่มีน้ำนมไหลออกมา &nbsp;
                <span class="badge badge-info mr-1">1</span>เมืื่อบีบลานนมแล้วมีน้ำนมไหลออกมา 1-2 หยด &nbsp;
                <span class="badge badge-primary mr-1">2</span>เมืื่อบีบลานนมแล้วมีน้ำนมไหลออกมามากกว่า 3 หยดขึ้นไปแต่น้ำนมไม่พุ่ง &nbsp;
                <span class="badge badge-success mr-1">3</span>เมืื่อบีบลานนมแล้วมีน้ำนมไหลพุ่ง
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ตารางบันทึก LATCH 4 ครั้ง × 3 เวลา -->
  <div class="card mb-3">
    <div class="section-header"><i class="fas fa-table"></i> ตารางการประเมิน LATCH และประสิทธิภาพการไหลของน้ำนม (4 ครั้ง × 3 เวลา)</div>
    <div class="card-body p-1">
      <div class="table-responsive">
        <table class="latch-grid w-100">
          <thead>
            <!-- วันที่ -->
            <tr>
              <th class="row-label text-center" style="width:10%; background:#1565c0; color:#fff;">วัน/เดือน/ปี</th>
              <?php for($d=1;$d<=4;$d++): ?>
              <th colspan="3" class="day-header" style="width:22.5%">
                <input type="date" name="assess_date_<?= $d ?>"
                  class="form-control form-control-sm text-center"
                  value="<?= sv($row,"assess_date_$d",'') ?>"
                  onchange="updateDayHeaders(<?= $d ?>)">
              </th>
              <?php endfor; ?>
            </tr>
            <!-- เวลา -->
            <tr>
              <th class="row-label text-center" style="background:#1976d2; color:#fff;">เวลา</th>
              <?php for($d=1;$d<=4;$d++):
                for($t=1;$t<=3;$t++):
                  $savedTime = sv($row,"assess_time_{$d}_{$t}",'');
                  $defTime   = $savedTime !== '' ? $savedTime : $defaultTimes[$d][$t-1];
              ?>
              <th class="time-header" style="width:7.5%">
                <input type="time" name="assess_time_<?= $d ?>_<?= $t ?>"
                  class="form-control form-control-sm t-input mx-auto"
                  value="<?= htmlspecialchars($defTime) ?>"
                  onchange="calcTotal(<?= $d ?>,<?= $t ?>)">
              </th>
              <?php endfor; endfor; ?>
            </tr>
          </thead>
          <tbody>

            <!-- LATCH criteria rows -->
            <?php foreach($latchItems as $item):
              $fkey = ($item['key']==='milk_level') ? 'milk_level' : 'latch_'.$item['key'];
            ?>
            <tr>
              <td class="row-label"><?= $item['label'] ?></td>
              <?php for($d=1;$d<=4;$d++):
                for($t=1;$t<=3;$t++):
                  $colKey = "{$fkey}_{$d}_{$t}";
                  $saved  = sv($row,$colKey,null);
              ?>
              <td>
                <div class="r-grp">
                  <?php for($sc=0;$sc<=$item['max'];$sc++): ?>
                  <label>
                    <input type="radio"
                      name="<?= $colKey ?>"
                      value="<?= $sc ?>"
                      <?= ($saved!==null && (int)$saved===$sc) ? 'checked' : '' ?>
                      onchange="calcTotal(<?= $d ?>,<?= $t ?>)">
                    <span><?= $sc ?></span>
                  </label>
                  <?php endfor; ?>
                </div>
              </td>
              <?php endfor; endfor; ?>
            </tr>
            <?php endforeach; ?>

            <!-- คะแนนรวม -->
            <tr class="total-row">
              <td class="row-label">คะแนนรวม<br><small>(เต็ม 13)</small></td>
              <?php for($d=1;$d<=4;$d++):
                for($t=1;$t<=3;$t++): ?>
              <td>
                <span id="tot_<?= $d ?>_<?= $t ?>" class="score-badge">
                  <?= sv($row,"latch_total_{$d}_{$t}",'-') ?>
                </span>
                <input type="hidden" name="latch_total_<?= $d ?>_<?= $t ?>" id="tot_h_<?= $d ?>_<?= $t ?>"
                  value="<?= sv($row,"latch_total_{$d}_{$t}",'') ?>">
              </td>
              <?php endfor; endfor; ?>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ส่วนที่ 2: สุขภาพจิต -->
  <div class="card mb-3">
    <div class="section-header" style="background:#5c6bc0;"><i class="fas fa-brain"></i> การประเมินสุขภาพจิตมารดาหลังคลอด</div>
    <div class="card-body p-2">
      <h6 class="font-weight-bold mb-2">ส่วนที่ 1 การประเมินความเครียด</h6>
      <div class="table-responsive">
        <table class="stress-table w-100">
          <thead>
            <tr>
              <th style="width:5%">ข้อ</th>
              <th style="width:40%">อาการ</th>
              <th style="width:13%">แทบไม่มี<br><small>(0)</small></th>
              <th style="width:13%">บางครั้ง<br><small>(1)</small></th>
              <th style="width:13%">บ่อยครั้ง<br><small>(2)</small></th>
              <th style="width:13%">เป็นประจำ<br><small>(3)</small></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sItems=[1=>'มีปัญหาการนอน นอนไม่หลับหรือนอนมากเกินไป',2=>'มีสมาธิน้อยลง',3=>'หงุดหงิด/กระวนกระวาย/ว้าวุ่นใจ',4=>'รู้สึกเบื่อ เซ็ง',5=>'ไม่อยากพบปะผู้คน'];
            foreach($sItems as $qn=>$ql):
              $sv=sv($row,"stress_q$qn",null);
            ?>
            <tr>
              <td class="text-center"><?= $qn ?></td>
              <td><?= $ql ?></td>
              <?php for($sc=0;$sc<=3;$sc++): ?>
              <td class="text-center">
                <input type="radio" name="stress_q<?= $qn ?>" value="<?= $sc ?>"
                  id="sq<?= $qn ?>v<?= $sc ?>"
                  <?= ($sv!==null&&(int)$sv===$sc)?'checked':'' ?>
                  onchange="calcStress()">
                <label for="sq<?= $qn ?>v<?= $sc ?>"></label>
              </td>
              <?php endfor; ?>
            </tr>
            <?php endforeach; ?>
            <tr style="background:#e8eaf6;font-weight:bold;">
              <td colspan="2" class="text-right">คะแนนรวม</td>
              <td colspan="4" class="text-center">
                <span id="stress_total_disp" class="score-badge" style="background:#5c6bc0;"><?= sv($row,'stress_total','-') ?></span>
                <input type="hidden" name="stress_total" id="stress_total" value="<?= sv($row,'stress_total','') ?>">
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="row mt-2 mb-3">
        <div class="col-md-4"><div style="border:2px solid #28a745;border-radius:6px;padding:8px;background:#f0fff4;font-size:0.82rem;"><b>0-4:</b> ไม่มีความเครียด</div></div>
        <div class="col-md-4"><div style="border:2px solid #ffc107;border-radius:6px;padding:8px;background:#fffbea;font-size:0.82rem;"><b>5-7:</b> สงสัยมีความเครียด ควรปรึกษา</div></div>
        <div class="col-md-4"><div style="border:2px solid #dc3545;border-radius:6px;padding:8px;background:#fff0f0;font-size:0.82rem;"><b>8+:</b> ความเครียดสูง ควรพบบุคลากรสาธารณสุข</div></div>
      </div>
      <h6 class="font-weight-bold mb-2">ส่วนที่ 2 การคัดกรองโรคซึมเศร้า</h6>
      <?php
      $dItems=[1=>'ใน 2 สัปดาห์ที่ผ่านมา รวมวันนี้ด้วย ท่านรู้สึกหดหู่ เศร้า หรือท้อแท้สิ้นหวังหรือไม่',2=>'ใน 2 สัปดาห์ที่ผ่านมา รวมวันนี้ด้วย ท่านรู้สึกเบื่อเอือมหรือทำอะไรไม่เพลิดเพลินหรือไม่'];
      foreach($dItems as $qn=>$ql):
        $sv=sv($row,"depression_q$qn",null);
      ?>
      <div class="mb-2 p-2" style="background:#f9f9f9;border-radius:4px;border:1px solid #ddd;">
        <div class="mb-1"><?= $qn ?>. <?= $ql ?></div>
        <div class="yn-radio">
          <label><input type="radio" name="depression_q<?= $qn ?>" value="1" <?= ($sv!==null&&(int)$sv===1)?'checked':'' ?>> มี</label>
          <label><input type="radio" name="depression_q<?= $qn ?>" value="0" <?= ($sv!==null&&(int)$sv===0)?'checked':'' ?>> ไม่มี</label>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ส่วนที่ 3: การดื่มสุรา -->
  <div class="card mb-3">
    <div class="section-header" style="background:#ef5350;"><i class="fas fa-wine-bottle"></i> ส่วนที่ 3 — การดื่มสุรา</div>
    <div class="card-body">
      <p class="mb-2"><b>ในรอบ 1 ปีที่ผ่านมา คุณเคยดื่มสุราหรือไม่?</b>
        <small class="text-muted">(เบียร์ เหล้า สาโท กระแช่ ไวน์ เป็นต้น)</small></p>
      <div class="yn-radio mb-3">
        <label><input type="radio" name="alcohol_ever" id="alcohol_ever_1" value="1"
          <?= (sv($row,'alcohol_ever',null)==='1')?'checked':'' ?> onchange="toggleAlcohol()">
          <b class="text-danger">เคย</b></label>
        <label><input type="radio" name="alcohol_ever" id="alcohol_ever_0" value="0"
          <?= (sv($row,'alcohol_ever',null)==='0')?'checked':'' ?> onchange="toggleAlcohol()">
          ไม่เคย</label>
      </div>
      <div id="alcohol_section" style="display:none;">
        <div class="alert alert-warning py-2">
          <i class="fas fa-exclamation-triangle"></i>
          <b>ควรได้รับการประเมินปัญหาการดื่มสุรา</b> เนื่องจากมีผลต่อทารกและการเลี้ยงบุตร
        </div>
        <div class="form-group">
          <label><b>การส่งต่อข้อมูลเพื่อพบแพทย์ภาวะผิดปกติ:</b></label>
          <textarea name="alcohol_refer" class="form-control" rows="3"><?= htmlspecialchars(sv($row,'alcohol_refer','')) ?></textarea>
        </div>
        <div class="alert alert-info py-2">
          <i class="fas fa-info-circle"></i> กรุณาทำแบบประเมิน <b>AUDIT</b>
          <a href="form-alcohol.php?an=<?= htmlspecialchars($an) ?>" target="_blank" class="btn btn-sm btn-outline-primary ml-2">
            <i class="fas fa-external-link-alt"></i> เปิดแบบประเมิน AUDIT
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-2 mb-5">
    <div class="col text-center">
      <button type="submit" class="btn btn-primary btn-lg px-5 shadow"><i class="fas fa-save"></i> บันทึกข้อมูล</button>
    </div>
  </div>

</div>
</form>
</div>

<script>
var latchKeys = ['latch_l','latch_a','latch_t','latch_c','latch_h','milk_level'];

function calcTotal(day, t) {
    var total = 0, hasAny = false;
    latchKeys.forEach(function(k) {
        var sel = document.querySelector('input[name="'+k+'_'+day+'_'+t+'"]:checked');
        if (sel) { total += parseInt(sel.value); hasAny = true; }
    });
    var disp = document.getElementById('tot_'+day+'_'+t);
    var hid  = document.getElementById('tot_h_'+day+'_'+t);
    if (disp) disp.textContent = hasAny ? total : '-';
    if (hid)  hid.value = hasAny ? total : '';
}

function calcStress() {
    var total = 0, hasAny = false;
    for (var q = 1; q <= 5; q++) {
        var sel = document.querySelector('input[name="stress_q'+q+'"]:checked');
        if (sel) { total += parseInt(sel.value); hasAny = true; }
    }
    var disp = document.getElementById('stress_total_disp');
    var hid  = document.getElementById('stress_total');
    if (disp) disp.textContent = hasAny ? total : '-';
    if (hid)  hid.value = hasAny ? total : '';
}

function toggleAlcohol() {
    var ev1 = document.getElementById('alcohol_ever_1');
    var sec = document.getElementById('alcohol_section');
    if (sec) sec.style.display = (ev1 && ev1.checked) ? 'block' : 'none';
}

$("#latch_form").on("submit", function(e) {
    e.preventDefault();

    // Validation: ถ้ามีการคลิกประเมินในเวลาใด ต้องคลิกให้ครบ 6 หัวข้อ
    for(var d=1; d<=4; d++) {
        var dateInput = document.querySelector('input[name="assess_date_'+d+'"]');
        if(dateInput && dateInput.value !== '') {
            for(var t=1; t<=3; t++) {
                var checkedCount = 0;
                latchKeys.forEach(function(k) {
                    if(document.querySelector('input[name="'+k+'_'+d+'_'+t+'"]:checked')) {
                        checkedCount++;
                    }
                });
                if (checkedCount > 0 && checkedCount < 6) {
                    Swal.fire("ข้อมูลไม่ครบถ้วน", "กรุณาประเมินให้ครบทุกหัวข้อ (L,A,T,C,H,ระดับน้ำนม) สำหรับครั้งที่ "+d+" เวลาที่ "+t, "warning");
                    return; // Stop submit
                }
            }
        }
    }

    $.ajax({
        url: "form-latch-postnatal-save.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(resp) {
            try {
                var d = JSON.parse(resp);
                if (d.status === "success") {
                    Swal.fire("สำเร็จ","บันทึกข้อมูลเรียบร้อยแล้ว","success").then(function(){window.location.reload(true);});
                } else {
                    Swal.fire("ข้อผิดพลาด", d.message, "error");
                }
            } catch(err) {
                Swal.fire("ข้อผิดพลาด","ไม่สามารถอ่านข้อมูลจากเซิร์ฟเวอร์ได้","error");
            }
        },
        error: function(){ Swal.fire("ข้อผิดพลาด","ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้","error"); }
    });
});

function updateDayHeaders(d) {
    var dateInput = document.querySelector('input[name="assess_date_'+d+'"]');
    var isSet = dateInput && dateInput.value !== '';
    for(var t=1; t<=3; t++) {
        var timeI = document.querySelector('input[name="assess_time_'+d+'_'+t+'"]');
        if(timeI) timeI.disabled = !isSet;
        latchKeys.forEach(function(k) {
            var radios = document.querySelectorAll('input[name="'+k+'_'+d+'_'+t+'"]');
            radios.forEach(function(r){ r.disabled = !isSet; });
        });
    }
}

window.onload = function() {
    for (var d = 1; d <= 4; d++) {
        updateDayHeaders(d);
        for (var t = 1; t <= 3; t++) {
            calcTotal(d, t);
        }
    }
    calcStress();
    toggleAlcohol();
};
</script>
