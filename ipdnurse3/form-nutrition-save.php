<?php
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/Session.php';
date_default_timezone_set("Asia/Bangkok");

$conn = DbUtils::get_hosxp_connection();

$an = $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);

$vstdate = empty($_REQUEST['vstdate']) ? null : $_REQUEST['vstdate'];
$height = empty($_REQUEST['height']) ? null : $_REQUEST['height'];
$bw = empty($_REQUEST['bw']) ? null : $_REQUEST['bw'];
$bmi = empty($_REQUEST['bmi']) ? null : $_REQUEST['bmi'];
$age_y = empty($_REQUEST['age_y']) ? null : $_REQUEST['age_y'];
$check_bmi = empty($_REQUEST['check_bmi']) ? null : $_REQUEST['check_bmi'];
$bw_1week = empty($_REQUEST['bw_1week']) ? null : $_REQUEST['bw_1week'];
$bw_2_3week = empty($_REQUEST['bw_2_3week']) ? null : $_REQUEST['bw_2_3week'];
$bw_1month = empty($_REQUEST['bw_1month']) ? null : $_REQUEST['bw_1month'];
$bw_3month = empty($_REQUEST['bw_3month']) ? null : $_REQUEST['bw_3month'];
$bw_5month = empty($_REQUEST['bw_5month']) ? null : $_REQUEST['bw_5month'];
$percen_1week = empty($_REQUEST['percen_1week']) ? null : $_REQUEST['percen_1week'];
$percen_2_3week = empty($_REQUEST['percen_2_3week']) ? null : $_REQUEST['percen_2_3week'];
$percen_1month = empty($_REQUEST['percen_1month']) ? null : $_REQUEST['percen_1month'];
$percen_3month = empty($_REQUEST['percen_3month']) ? null : $_REQUEST['percen_3month'];
$percen_5month = empty($_REQUEST['percen_5month']) ? null : $_REQUEST['percen_5month'];
$level_of_check = empty($_REQUEST['level_of_check']) ? null : $_REQUEST['level_of_check'];

$create_datetime = date('Y-m-d H:i:s');
$create_user = $_SESSION['loginname'];
$update_user = $_SESSION['loginname'];
$update_datetime = date('Y-m-d H:i:s');
$version = 1;

try {
    if ($an != '') {
        $stmt = $conn->prepare("INSERT INTO " . DbConstant::KPHIS_DBNAME . ".prs_check_vitalsign(
                an, hn, vstdate, height, bw, bmi, age_y, check_bmi,
                bw_1week, bw_2_3week, bw_1month, bw_3month, bw_5month,
                percen_1week, percen_2_3week, percen_1month, percen_3month, percen_5month,
                level_of_check,
                create_user, create_datetime, update_user, version, update_datetime)
                VALUES(
                :an, :hn, :vstdate, :height, :bw, :bmi, :age_y, :check_bmi,
                :bw_1week, :bw_2_3week, :bw_1month, :bw_3month, :bw_5month,
                :percen_1week, :percen_2_3week, :percen_1month, :percen_3month, :percen_5month,
                :level_of_check,
                :create_user, :create_datetime, :update_user, :version, :update_datetime)");

        $stmt->execute(array(
            'an' => $an,
            'hn' => $hn,
            'vstdate' => $vstdate,
            'height' => $height,
            'bw' => $bw,
            'bmi' => $bmi,
            'age_y' => $age_y,
            'check_bmi' => $check_bmi,
            'bw_1week' => $bw_1week,
            'bw_2_3week' => $bw_2_3week,
            'bw_1month' => $bw_1month,
            'bw_3month' => $bw_3month,
            'bw_5month' => $bw_5month,
            'percen_1week' => $percen_1week,
            'percen_2_3week' => $percen_2_3week,
            'percen_1month' => $percen_1month,
            'percen_3month' => $percen_3month,
            'percen_5month' => $percen_5month,
            'level_of_check' => $level_of_check,
            'create_user' => $create_user,
            'create_datetime' => $create_datetime,
            'update_user' => $update_user,
            'version' => $version,
            'update_datetime' => $update_datetime,
        ));

        $new_id = $conn->lastInsertId();

        Session::insertSystemAccessLog(json_encode(array(
            'form' => 'NUTRITION-FORM',
            'action' => 'INSERT',
            'version' => $version,
            'an' => $an,
        ), JSON_UNESCAPED_UNICODE));

        // --- NEW Logic for automated Progress Note (Moderate or Severe) ---
        $evaluation_message = empty($_REQUEST['evaluation_message']) ? null : $_REQUEST['evaluation_message'];

        if ($evaluation_message) {
            // Check if progress note with the same detail already exists for this AN
            $sql_check_note = "SELECT COUNT(*) FROM " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note_item
                                   WHERE an = :an AND progress_note_item_detail = :detail";
            $stmt_check_note = $conn->prepare($sql_check_note);
            $stmt_check_note->execute(['an' => $an, 'detail' => $evaluation_message]);

            if ($stmt_check_note->fetchColumn() == 0) {
                // 1. Insert into ipd_progress_note (Parent)
                $progress_note_date = date('Y-m-d');
                $progress_note_time = date('H:i:s');
                $groupname = isset($_SESSION['groupname']) ? $_SESSION['groupname'] : '';
                $progress_note_owner_type = 'nurse'; // Default
                if (strpos($groupname, 'แพทย์') !== false) {
                    $progress_note_owner_type = 'doctor';
                } else if (strpos($groupname, 'พยาบาล') !== false) {
                    $progress_note_owner_type = 'nurse';
                }
                $progress_note_doctor = isset($_SESSION['doctorcode']) ? $_SESSION['doctorcode'] : null;

                $stmt_pn = $conn->prepare("INSERT INTO " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note
                        (an, progress_note_date, progress_note_time, progress_note_owner_type, progress_note_doctor, create_user, create_datetime, update_user, update_datetime, version)
                        VALUES (:an, :progress_note_date, :progress_note_time, :progress_note_owner_type, :progress_note_doctor, :create_user, :create_datetime, :update_user, :update_datetime, :version)");
                $stmt_pn->execute([
                    'an' => $an,
                    'progress_note_date' => $progress_note_date,
                    'progress_note_time' => $progress_note_time,
                    'progress_note_owner_type' => $progress_note_owner_type,
                    'progress_note_doctor' => $progress_note_doctor,
                    'create_user' => $create_user,
                    'create_datetime' => $create_datetime,
                    'update_user' => $create_user,
                    'update_datetime' => $create_datetime,
                    'version' => 1
                ]);
                $progress_note_id = $conn->lastInsertId();

                // 2. Insert into ipd_progress_note_item (Child)
                $stmt_pni = $conn->prepare("INSERT INTO " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note_item
                        (progress_note_id, an, progress_note_item_type, progress_note_item_detail, create_user, create_datetime, update_user, update_datetime, version)
                        VALUES (:progress_note_id, :an, 'note', :detail, :create_user, :create_datetime, :update_user, :update_datetime, :version)");
                $stmt_pni->execute([
                    'progress_note_id' => $progress_note_id,
                    'an' => $an,
                    'detail' => $evaluation_message,
                    'create_user' => $create_user,
                    'create_datetime' => $create_datetime,
                    'update_user' => $update_user,
                    'update_datetime' => $update_datetime,
                    'version' => 1
                ]);
            }
        }

        $output_error = '<script>
            NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");
            setTimeout(function(){ window.location.href = "form-nutrition.php?an=' . $an . '&id=" + ' . json_encode($new_id) . '; }, 1000);
            </script>';

    } else {
        $output_error = '<script>NotificationMessage("กรุณากรอกข้อมูลให้ครบถ้วน", "error");</script>';
    }

} catch (PDOException $e) {
    echo $e->getMessage();
    $output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';
}
echo $output_error;
?>