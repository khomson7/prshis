<?php  // require_once './project/function/Session.php';
require_once '../include/Session.php';
//ตรวจสอบว่า session login ตรงกันหรือไม่
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];

if ($login != $loginname) {
    session_start();
    session_destroy();
}

//ส่วนหัวหน้า
require_once '../mains/main-report.php';

$permissionCheck = Session::checkPermissionAndShowMessage('ADMISSION_NOTE', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);
require_once '../include/session-modal.php';


require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

?>
<br>


<canvas id="signature-pad" width=400 height=200 style="border:1px solid #ccc;"></canvas>
<button onclick="clearSignature()">ล้าง</button>
<button onclick="saveSignature()">บันทึก</button>
<form id="signature-form" method="POST" action="save_signature.php">
  <input type="hidden" name="signature" id="signature">
</form>


<script>
const canvas = document.getElementById('signature-pad');
const ctx = canvas.getContext('2d');
let drawing = false;
canvas.addEventListener('mousedown', e => {
  drawing = true;
  ctx.beginPath();
  ctx.moveTo(e.offsetX, e.offsetY);
});
canvas.addEventListener('mouseup', () => drawing = false);
canvas.addEventListener('mousemove', e => {
  if (!drawing) return;
  ctx.lineWidth = 2;
  ctx.lineTo(e.offsetX, e.offsetY);
  ctx.stroke();
});
function clearSignature() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.beginPath();
}
function saveSignature() {
  const dataURL = canvas.toDataURL();
  document.getElementById('signature').value = dataURL;
  document.getElementById('signature-form').submit();
}
</script>