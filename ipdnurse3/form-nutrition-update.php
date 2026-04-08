<?php
date_default_timezone_set("Asia/Bangkok");
require_once '../include/Session.php';
Session::checkLoginSessionAndShowMessage();

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection();

$an = $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
$id = $_REQUEST['id'];

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

$update_datetime = date('Y-m-d H:i:s');
$update_user = $_SESSION['loginname'];
$version0 = $_REQUEST['version'];
$version = $version0 + 1;
$status_action = 'Y';


try {
    if ($an != '') {
        $output_error = '<script>NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");</script>';

        $stmt = $conn->prepare("UPDATE " . DbConstant::KPHIS_DBNAME . ".prs_check_vitalsign SET
                an=:an, hn=:hn, vstdate=:vstdate, height=:height, bw=:bw, bmi=:bmi,
                age_y=:age_y, check_bmi=:check_bmi,
                bw_1week=:bw_1week, bw_2_3week=:bw_2_3week,
                bw_1month=:bw_1month, bw_3month=:bw_3month, bw_5month=:bw_5month,
                percen_1week=:percen_1week, percen_2_3week=:percen_2_3week,
                percen_1month=:percen_1month, percen_3month=:percen_3month, percen_5month=:percen_5month,
                update_user=:update_user, version=:version, update_datetime=:update_datetime,status_action=:status_action
                WHERE id=:id");

        $stmt->execute(array(
            'id' => $id,
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
            'update_user' => $update_user,
            'version' => $version,
            'update_datetime' => $update_datetime,
            'status_action' => $status_action
        ));

        Session::insertSystemAccessLog(json_encode(array(
            'form' => 'NUTRITION-FORM',
            'action' => 'UPDATE',
            'version' => $version,
            'an' => $an,
        ), JSON_UNESCAPED_UNICODE));

        // Logic to check for "Severe" status or BMI < 16 for automated Progress Note
        $is_severe = false;
        // BMI Severe check (< 16)
        if ($bmi != null && (float) $bmi < 16) {
            $is_severe = true;
        } else {
            // Check weight loss percentages against "Severe" thresholds
            if ($percen_1week != null && (float) $percen_1week > 2)
                $is_severe = true;
            if ($percen_2_3week != null && (float) $percen_2_3week > 3)
                $is_severe = true;
            if ($percen_1month != null && (float) $percen_1month > 5)
                $is_severe = true;
            if ($percen_3month != null && (float) $percen_3month > 8)
                $is_severe = true;
            if ($percen_5month != null && (float) $percen_5month > 10)
                $is_severe = true;
        }

        if ($is_severe) {
            // Check if progress note with the same detail already exists for this AN
            // Using progress_note_item_detail as a unique marker for this automated note
            $sql_check_note = "SELECT COUNT(*) FROM " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note_item
                                   WHERE an = :an AND progress_note_item_detail = 'ประเมิน Nutrition ผล Serve'";
            $stmt_check_note = $conn->prepare($sql_check_note);
            $stmt_check_note->execute(['an' => $an]);

            if ($stmt_check_note->fetchColumn() == 0) {
                // 1. Insert into ipd_progress_note (Parent)
                $progress_note_date = date('Y-m-d');
                $progress_note_time = date('H:i:s');
                $groupname = $_SESSION['groupname'];
                $progress_note_owner_type = 'nurse'; // Default
                if (strpos($groupname, 'แพทย์') !== false) {
                    $progress_note_owner_type = 'doctor';
                } else if (strpos($groupname, 'พยาบาล') !== false) {
                    $progress_note_owner_type = 'nurse';
                }
                $progress_note_doctor = $_SESSION['doctorcode'];

                $stmt_pn = $conn->prepare("INSERT INTO " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note
                        (an, progress_note_date, progress_note_time, progress_note_owner_type, progress_note_doctor, create_user,
                         create_datetime, update_user, update_datetime, version)
                        VALUES (:an, :progress_note_date, :progress_note_time, :progress_note_owner_type, :progress_note_doctor, :create_user, 
                        :create_datetime, :update_user, :update_datetime, :version)");
                $stmt_pn->execute([
                    'an' => $an,
                    'progress_note_date' => $progress_note_date,
                    'progress_note_time' => $progress_note_time,
                    'progress_note_owner_type' => $progress_note_owner_type,
                    'progress_note_doctor' => $progress_note_doctor,
                    'create_user' => $update_user,
                    'create_datetime' => $update_datetime,
                    'update_user' => $update_user,
                    'update_datetime' => $update_datetime,
                    'version' => 1
                ]);
                $progress_note_id = $conn->lastInsertId();

                // 2. Insert into ipd_progress_note_item (Child)
                $stmt_pni = $conn->prepare("INSERT INTO " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note_item
                        (progress_note_id, an, progress_note_item_type, progress_note_item_detail, create_user, create_datetime, update_user, update_datetime, version)
                        VALUES (:progress_note_id, :an, 'note', 'ประเมิน Nutrition ผล Serve', :create_user, :create_datetime, :update_user, :update_datetime, :version)");
                $stmt_pni->execute([
                    'progress_note_id' => $progress_note_id,
                    'an' => $an,
                    'create_user' => $update_user,
                    'create_datetime' => $update_datetime,
                    'update_user' => $update_user,
                    'update_datetime' => $update_datetime,
                    'version' => 1
                ]);
            }
        }

    } else {
        echo '<script>alert("กรุณากรอกข้อมูลให้ครบถ้วน");</script>';
    }

} catch (PDOException $e) {
    echo $e->getMessage();
    $output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';
}
echo $output_error;
?>