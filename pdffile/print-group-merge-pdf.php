<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : (isset($_REQUEST['loginname']) ? $_REQUEST['loginname'] : '');
// if (!$loginname) die('Unauthorized');

$an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';
$scripts = isset($_REQUEST['scripts']) ? $_REQUEST['scripts'] : '';
$group_id = isset($_REQUEST['group_id']) ? (int)$_REQUEST['group_id'] : 0;

if (!$an || (!$scripts && !$group_id)) die('ไม่พบข้อมูล AN หรือ Scripts/Group');

if (!$scripts && $group_id > 0) {
    require_once '../include/DbUtils.php';
    $conn = DbUtils::get_hosxp_connection();
    $stmt = $conn->prepare("SELECT pdf_script FROM prs_group_print_item WHERE group_print = :group_print ORDER BY sort_order");
    $stmt->execute(['group_print' => $group_id]);
    $arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['pdf_script']) $arr[] = $row['pdf_script'];
    }
    $scripts = implode(',', $arr);
}

$scriptList = explode(',', $scripts);

// ปิด Session ก่อนทำ cURL แบบ Loopback เพื่อป้องกันอาการ Session Deadlock (ทำระบบค้าง/ช้ามาก)
session_write_close();

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$currentDir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
// ถอยกลับไป 1 โฟลเดอร์ (ลบ /pdffile ออก) เพื่อให้เป็น Base URL ของระบบหลัก
$baseDir = preg_replace('/\/pdffile$/i', '', $currentDir);
$baseUrl = $protocol . $_SERVER['HTTP_HOST'] . $baseDir;

$hasPage = false;

// Optimize by fetching all PDFs concurrently using cURL multi
$mh = curl_multi_init();
$chArray = [];

foreach ($scriptList as $i => $script) {
    $script = trim($script);
    if (!$script) continue;
    
    // ถ้าผู้ใช้ไม่ได้ใส่ชื่อโฟลเดอร์มา (ไม่มีเครื่องหมาย /) ให้เติม pdffile/ ให้โดยอัตโนมัติ (รองรับข้อมูลเดิม)
    if (strpos($script, '/') === false) {
        $script = 'pdffile/' . $script;
    }
    
    $url = $baseUrl . '/' . ltrim($script, '/') . '?an=' . urlencode($an) . '&loginname=' . urlencode($loginname);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    // ส่ง Session Cookie ไปด้วยเพื่อให้ระบบต้นทางรู้ว่าเป็น User เดียวกัน
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    curl_multi_add_handle($mh, $ch);
    $chArray[$i] = $ch;
}

// Execute all queries simultaneously
$active = null;
do {
    $mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($mh) == -1) {
        usleep(100);
    }
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
}

$pdfResults = [];
foreach ($chArray as $i => $ch) {
    $pdfResults[$i] = curl_multi_getcontent($ch);
    curl_multi_remove_handle($mh, $ch);
}
curl_multi_close($mh);

// Process the fetched PDFs
foreach ($pdfResults as $pdfData) {
    if ($pdfData && strpos(substr($pdfData, 0, 10), '%PDF') !== false) {
        try {
            $pagecount = $mpdf->setSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($pdfData));
            for ($i = 1; $i <= $pagecount; $i++) {
                $mpdf->AddPage();
                $tplId = $mpdf->importPage($i);
                $mpdf->useTemplate($tplId);
                $hasPage = true;
            }
        } catch (Exception $e) {
            // Ignore parse errors from single files
        }
    }
}

if (!$hasPage) {
    $mpdf->AddPage();
    $mpdf->WriteHTML('<div style="font-family: garuda, sans-serif; text-align:center; padding: 50px;">
        <h3>ไม่สามารถดึงข้อมูลเอกสารได้ หรือไม่มีเอกสารในกลุ่มที่เลือก</h3>
        <p>URL Base: ' . htmlspecialchars($baseUrl) . '</p>
    </div>');
}

$mpdf->Output('Group_Print_AN_' . $an . '_' . date('Ymd') . '.pdf', 'I');
