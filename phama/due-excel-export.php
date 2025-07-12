<?php  // require_once './project/function/Session.php';



require_once '../include/Session.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


require_once '../include/DbUtils.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

//หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
/*if ($login != $loginname) {
    session_start();
    session_destroy();
} */
//require_once '../mains/main-report.php';

//Session::checkLoginSessionAndShowMessage(); //เช็ค session

// รับค่าจาก POST
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

if (empty($start_date) || empty($end_date)) {
    die("กรุณาระบุวันที่เริ่มต้นและวันที่สิ้นสุด");
}

// เชื่อมต่อฐานข้อมูล
/*
$host = "localhost";
$user = "your_db_user";
$pass = "your_db_password";
$dbname = "your_db_name";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
*/
// ดึงข้อมูล
$sql = "SELECT * FROM prs_due_check WHERE start_medication BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

// สร้าง Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// เขียน header (ชื่อคอลัมน์)
if ($row = $result->fetch_assoc()) {
    $col = 1;
    foreach (array_keys($row) as $columnName) {
        $sheet->setCellValueByColumnAndRow($col, 1, $columnName);
        $col++;
    }

    // เขียนข้อมูล
    $rowIndex = 2;
    do {
        $col = 1;
        foreach ($row as $cell) {
            $sheet->setCellValueByColumnAndRow($col, $rowIndex, $cell);
            $col++;
        }
        $rowIndex++;
    } while ($row = $result->fetch_assoc());
} else {
    die("ไม่พบข้อมูลในช่วงวันที่ที่เลือก");
}

$stmt->close();
$conn->close();

// ตั้งค่าหัวเพื่อดาวน์โหลด
$filename = "export_" . date("Ymd_His") . ".xlsx";
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=\"$filename\"");
header("Cache-Control: max-age=0");

// เขียนไฟล์ไปยัง output
//$writer = new Xlsx($spreadsheet);
$writer = new Xlsx($spreadsheet); // <- ต้องใช้ new Xlsx() ที่ถูกต้อง
$writer->save('php://output');
exit;

?>


