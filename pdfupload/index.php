<?php
require_once '../include/Session.php';

// =====================================================
// ป้องกันการเรียกผ่าน GET (บังคับใช้ POST เท่านั้น)
// =====================================================
/*if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "<script>window.close();</script>";
    exit;
}
*/
// =====================================================
// ระบบ Single Sign-On (SSO) ข้าม Port/Server (แบบ POST)
// =====================================================
if (isset($_POST['hash']) && isset($_POST['loginuser']) && isset($_POST['t']) && isset($_POST['an'])) {
    $secret_key = "PRSHIS_SECRET_2026";

    // ขยายเวลาเป็น 1 ชั่วโมง (3600 วิ) และใช้ abs() เพื่อแก้ปัญหาเวลา 2 เซิร์ฟเวอร์เดินไม่เท่ากัน
    if (abs(time() - $_POST['t']) <= 3600) {
        $expected_hash = md5($_POST['loginuser'] . $_POST['t'] . $_POST['an'] . $secret_key);

        if ($_POST['hash'] === $expected_hash) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['loginname'] = $_POST['loginuser'];
        }
    }
}

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) {
    $redirect = 'pdfupload/index.php';
    if (!empty($_REQUEST['an'])) {
        $redirect .= '?an=' . urlencode($_REQUEST['an']);
    }
    header('Location: ../login.php?redirect=' . urlencode($redirect));
    exit;
}

require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('PDF-UPLOAD', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/session-modal.php';

date_default_timezone_set('Asia/Bangkok');

$conn = DbUtils::get_kphis_log_db_connection();
$an = trim($_REQUEST['an'] ?? '');

// ---- Doc groups ----
$doc_groups = [
    'งานห้องผ่าตัด',
    'ห้องคลอด',
    'งานผู้ป่วยใน',
    'งานอุบัติเหตุฉุกเฉิน',
    'งานผู้ป่วยนอก',
    'ห้องไอซียู',
    'งานเวชระเบียน',
    'อื่นๆ',
];

// ---- ดึงรายการ PDF ของ AN นี้ ----
$stmt = $conn->prepare("SELECT id, an, doc_name, doc_group, original_name, file_size, upload_at, upload_by 
                         FROM prs_pdf_upload
                         WHERE an = :an AND is_deleted = 0
                         ORDER BY upload_at DESC");
$stmt->execute(['an' => $an]);
$pdf_list = $stmt->fetchAll();

// ---- ตรวจสอบข้อมูล Clinical Summary ของ AN นี้ ----
$has_clinical_summary = false;
try {
    $conn_kphis = DbUtils::get_hosxp_connection();
    $stmt_summary = $conn_kphis->prepare("SELECT COUNT(*) FROM prs_clinical_summary WHERE an = :an");
    $stmt_summary->execute(['an' => $an]);
    $has_clinical_summary = $stmt_summary->fetchColumn() > 0;
} catch (Exception $e) {
    // ป้องกันกรณีที่ตารางยังไม่ถูกสร้าง หรือ DB error ทำให้หน้าเว็บพัง
}

Session::insertSystemAccessLog(json_encode([
    'form' => 'PDF-UPLOAD-LIST',
    'an' => $an,
], JSON_UNESCAPED_UNICODE));
?>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<style>
    .pdf-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 10px;
        background: #fff;
        transition: box-shadow .2s;
    }

    .pdf-card:hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, .12);
    }

    .badge-group {
        font-size: .75rem;
        padding: 3px 8px;
        border-radius: 12px;
        background: #e8f4fd;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .owner-badge {
        font-size: .72rem;
        color: #6c757d;
    }

    .btn-action {
        font-size: .8rem;
        padding: 3px 10px;
    }

    #uploadModal .modal-header {
        background: #2c6e49;
        color: #fff;
    }
</style>

<div class="container-fluid mt-2">
    <div class="d-flex align-items-center mb-3">
        <h5 class="mb-0 mr-auto">
            <i class="fas fa-file-pdf text-danger"></i>
            เอกสาร PDF แนบประวัติผู้ป่วย
        </h5>
        <button class="btn btn-primary btn-sm shadow-sm mr-2" onclick="fetchClinicalSummary()">
            <i class="fas fa-cloud-download-alt"></i> ดึงข้อมูล Clinical Summary
        </button>
        <button class="btn btn-secondary btn-sm shadow-sm mr-2" onclick="openTokenModal()" id="btnTokenConfig" title="ตั้งค่า / อัพเดท Token">
            <i class="fas fa-key"></i> <span id="tokenStatusLabel">ตั้งค่า Token</span>
        </button>
        <button class="btn btn-success btn-sm shadow-sm" onclick="openUploadModal(0)">
            <i class="fas fa-upload"></i> Upload PDF ใหม่
        </button>
    </div>

    <?php if (empty($pdf_list)): ?>
        <div class="alert alert-light text-center text-muted py-4">
            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
            ยังไม่มีเอกสาร PDF สำหรับผู้ป่วยรายนี้
        </div>
    <?php else: ?>

        <!-- Filter by group -->
        <div class="mb-2 d-flex align-items-center flex-wrap">
            <span class="text-muted small mr-1">กรองตามกลุ่ม:</span>
            <button class="btn btn-sm btn-outline-secondary ml-1 mb-1 filter-btn active" data-group="all">ทั้งหมด</button>
            <?php
            $used_groups = array_unique(array_column($pdf_list, 'doc_group'));
            foreach ($used_groups as $g):
                if (!$g)
                    continue; ?>
                <button class="btn btn-sm btn-outline-info ml-1 mb-1 filter-btn" data-group="<?= htmlspecialchars($g) ?>">
                    <?= htmlspecialchars($g) ?>
                </button>
            <?php endforeach; ?>

            <?php if ($has_clinical_summary): ?>
            <form action="../pdffile/clinical-summary-pdf.php" method="POST" target="_blank" class="d-inline ml-1 mb-1">
                <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-medical"></i> Clinical Summary
                </button>
            </form>
            <?php endif; ?>
        </div>

        <div id="pdfListContainer">
            <?php foreach ($pdf_list as $f):
                $is_owner = ($f['upload_by'] === $loginname);
                $size_kb = round($f['file_size'] / 1024, 1);
                $upload_dt = date('d/m/' . (date('Y', strtotime($f['upload_at'])) + 543) . ' H:i', strtotime($f['upload_at']));
                ?>
                <div class="pdf-card" data-group="<?= htmlspecialchars($f['doc_group'] ?? '') ?>">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-file-pdf fa-2x text-danger mr-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="font-weight-bold"><?= htmlspecialchars($f['doc_name']) ?></div>
                            <div class="mt-1">
                                <?php if ($f['doc_group']): ?>
                                    <span class="badge-group"><?= htmlspecialchars($f['doc_group']) ?></span>
                                <?php endif; ?>
                                <span class="owner-badge ml-2">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($f['upload_by']) ?>
                                    &nbsp;|&nbsp;
                                    <i class="fas fa-clock"></i> <?= $upload_dt ?>
                                    &nbsp;|&nbsp;
                                    <?= $size_kb ?> KB
                                </span>
                            </div>
                            <?php if ($f['original_name']): ?>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-paperclip"></i> <?= htmlspecialchars($f['original_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-2 text-nowrap">
                            <a href="view.php?an=<?= urlencode($an) ?>&id=<?= $f['id'] ?>" target="_blank"
                                class="btn btn-sm btn-info btn-action">
                                <i class="fas fa-eye"></i> ดูและพิมพ์
                            </a>
                            <?php if ($is_owner): ?>
                                <button class="btn btn-sm btn-warning btn-action"
                                    onclick="openUploadModal(<?= $f['id'] ?>, '<?= addslashes($f['doc_name']) ?>', '<?= addslashes($f['doc_group'] ?? '') ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-action" onclick="deleteFile(<?= $f['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Upload / Edit Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title mb-0" id="modalTitle">
                    <i class="fas fa-upload"></i> Upload PDF
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
                    <input type="hidden" name="id" id="edit_id" value="0">

                    <div class="form-group">
                        <label><b>ชื่อเอกสาร</b> <span class="text-danger">*</span></label>
                        <input type="text" name="doc_name" id="doc_name" class="form-control"
                            placeholder="เช่น ใบยินยอมผ่าตัด, Operative Note" required>
                    </div>

                    <div class="form-group">
                        <label><b>กลุ่มเอกสาร</b></label>
                        <select name="doc_group" id="doc_group" class="form-control">
                            <option value="">-- เลือกกลุ่ม --</option>
                            <?php foreach ($doc_groups as $g): ?>
                                <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><b>ไฟล์ PDF</b> <span id="file_required" class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="pdf_file" id="pdf_file" class="custom-file-input"
                                accept=".pdf,application/pdf">
                            <label class="custom-file-label" for="pdf_file" id="file_label">เลือกไฟล์ PDF</label>
                        </div>
                        <small class="text-muted">รองรับเฉพาะ .pdf | ขนาดไม่เกิน 20 MB</small>
                    </div>

                    <div class="form-group mb-0">
                        <label><b>วันเวลา Upload</b></label>
                        <input type="text" class="form-control-plaintext font-weight-bold text-success"
                            value="<?= date('d/m/' . (date('Y') + 543) . ' H:i') ?>" readonly>
                        <small class="text-muted">บันทึกอัตโนมัติตามเวลาจริง</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== Token Config Modal ===== -->
<div class="modal fade" id="tokenModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background:#343a40;color:#fff;">
                <h6 class="modal-title mb-0">
                    <i class="fas fa-key"></i> ตั้งค่า SATI API Token
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="tokenStatusInfo" class="alert alert-secondary small mb-3" style="display:none;"></div>
                <div class="form-group">
                    <label><b>Tenant ID</b> <span class="text-danger">*</span></label>
                    <input type="text" id="input_tenant_id" class="form-control form-control-sm font-monospace"
                        placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                </div>
                <div class="form-group mb-1">
                    <label><b>Bearer Token (JWT)</b> <span class="text-danger">*</span></label>
                    <textarea id="input_bearer_token" class="form-control form-control-sm font-monospace" rows="5"
                        placeholder="eyJhbGci..."></textarea>
                    <small class="text-muted">Token มีอายุ 24 ชั่วโมง ต้องอัพเดทก่อนหมดอายุ</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-dark btn-sm" id="btnSaveToken" onclick="saveToken()">
                    <i class="fas fa-save"></i> บันทึก Token
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var currentAn = '<?= htmlspecialchars($an) ?>';

    // ---- Load Token Status on page load ----
    (function loadTokenStatus() {
        $.post('fetch-clinical-summary.php', { action: 'get_token_status' }, function(resp) {
            try {
                var d = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                var btn = document.getElementById('tokenStatusLabel');
                var btnEl = document.getElementById('btnTokenConfig');
                if (d.is_expired || !d.token_length) {
                    btn.textContent = 'Token หมดอายุ!';
                    btnEl.classList.remove('btn-secondary');
                    btnEl.classList.add('btn-danger');
                } else {
                    btn.textContent = 'Token OK (หมด ' + d.expire_time + ')';
                    btnEl.classList.remove('btn-danger');
                    btnEl.classList.add('btn-secondary');
                }
            } catch(e) {}
        });
    })();

    // ---- Filter by group ----
    document.querySelectorAll('.filter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            var group = this.dataset.group;
            document.querySelectorAll('.pdf-card').forEach(function (card) {
                card.style.display = (group === 'all' || card.dataset.group === group) ? '' : 'none';
            });
        });
    });

    // ---- Custom file label ----
    document.getElementById('pdf_file').addEventListener('change', function () {
        var label = document.getElementById('file_label');
        label.textContent = this.files.length ? this.files[0].name : 'เลือกไฟล์ PDF';
    });

    // ---- Open modal ----
    function openUploadModal(id, docName, docGroup) {
        var isEdit = id > 0;
        document.getElementById('modalTitle').innerHTML =
            isEdit ? '<i class="fas fa-edit"></i> แก้ไขเอกสาร'
                : '<i class="fas fa-upload"></i> Upload PDF ใหม่';
        document.getElementById('edit_id').value = id || 0;
        document.getElementById('doc_name').value = docName || '';
        document.getElementById('file_label').textContent = 'เลือกไฟล์ PDF';
        document.getElementById('pdf_file').value = '';

        var sel = document.getElementById('doc_group');
        sel.value = docGroup || '';

        // ถ้า edit ไม่บังคับเลือกไฟล์ใหม่
        document.getElementById('pdf_file').required = !isEdit;
        document.getElementById('file_required').style.display = isEdit ? 'none' : '';

        $('#uploadModal').modal('show');
    }

    // ---- Submit form ----
    $('#uploadForm').on('submit', function (e) {
        e.preventDefault();
        var id = parseInt($('#edit_id').val());
        var url = id > 0 ? 'upload-update.php' : 'upload-save.php';
        var fd = new FormData(this);

        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');

        $.ajax({
            url: url,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (resp) {
                try {
                    var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                    if (data.status === 'success') {
                        Swal.fire('สำเร็จ', 'บันทึกเอกสารเรียบร้อยแล้ว', 'success').then(function () {
                            location.reload();
                        });
                    } else {
                        Swal.fire('ผิดพลาด', data.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                } catch (ex) {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถอ่านผลลัพธ์จากเซิร์ฟเวอร์ได้', 'error');
                }
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> บันทึก');
            },
            error: function () {
                Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> บันทึก');
            }
        });
    });

    // ---- Delete ----
    function deleteFile(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: 'ไม่สามารถกู้คืนได้หลังจากลบ',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post('upload-delete.php', { id: id, an: currentAn }, function (resp) {
                try {
                    var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                    if (data.status === 'success') {
                        Swal.fire('ลบสำเร็จ', '', 'success').then(function () { location.reload(); });
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (ex) {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถอ่านผลลัพธ์ได้', 'error');
                }
            });
        });
    }

    // ---- Fetch Clinical Summary ----
    function fetchClinicalSummary() {
        Swal.fire({
            title: 'ดึงข้อมูล Clinical Summary',
            text: 'ระบบกำลังดึงข้อมูลจาก API ภายนอก กรุณารอสักครู่...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.post('fetch-clinical-summary.php', { an: currentAn }, function(resp) {
            try {
                var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ', data.message, 'success');
                } else if (data.status === 'not_found') {
                    Swal.fire({
                        title: 'ไม่พบข้อมูล',
                        text: data.message,
                        icon: 'info'
                    });
                } else if (data.need_token) {
                    // Token หมดอายุหรือไม่มี — เปิด modal ให้อัพเดทเลย
                    Swal.fire({
                        title: 'Token หมดอายุ / ไม่มี Token',
                        html: data.message + '<br><br>กรุณาอัพเดท Token ใหม่',
                        icon: 'warning',
                        confirmButtonText: 'ไปตั้งค่า Token',
                        showCancelButton: true,
                        cancelButtonText: 'ปิด'
                    }).then(function(r) {
                        if (r.isConfirmed) openTokenModal();
                    });
                } else {
                    var errorText = data.message;
                    if (data.raw_response) {
                        errorText += '<br><br><b>HTTP Code:</b> ' + data.httpcode;
                        errorText += '<br><b>Raw Response:</b> <pre style="text-align:left;font-size:12px;">' + data.raw_response + '</pre>';
                    }
                    Swal.fire({ title: 'ผิดพลาด', html: errorText, icon: 'error' });
                }
            } catch (ex) {
                Swal.fire('ผิดพลาด', 'ไม่สามารถอ่านผลลัพธ์จากเซิร์ฟเวอร์ได้', 'error');
            }
        }).fail(function() {
            Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
        });
    }

    // ---- Token Modal ----
    function openTokenModal() {
        // โหลดสถานะปัจจุบัน
        $.post('fetch-clinical-summary.php', { action: 'get_token_status' }, function(resp) {
            try {
                var d = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                var info = document.getElementById('tokenStatusInfo');
                info.style.display = '';
                if (d.is_expired || !d.token_length) {
                    info.className = 'alert alert-danger small mb-3';
                    info.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <b>Token หมดอายุแล้ว</b> กรุณาวาง Token ใหม่จาก SATI';
                } else {
                    info.className = 'alert alert-success small mb-3';
                    info.innerHTML = '<i class="fas fa-check-circle"></i> Token ยังใช้งานได้ จะหมดอายุ: <b>' + d.expire_time + '</b>'
                        + ' | อัพเดทโดย: ' + d.updated_by;
                }
                document.getElementById('input_tenant_id').value = d.tenant_id || '';
                document.getElementById('input_bearer_token').value = '';
            } catch(e) {}
        });
        $('#tokenModal').modal('show');
    }

    function saveToken() {
        var tenant = document.getElementById('input_tenant_id').value.trim();
        var token  = document.getElementById('input_bearer_token').value.trim();
        if (!tenant || !token) {
            Swal.fire('แจ้งเตือน', 'กรุณากรอก Tenant ID และ Bearer Token ให้ครบ', 'warning');
            return;
        }
        var btn = document.getElementById('btnSaveToken');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...';

        $.post('fetch-clinical-summary.php', {
            action: 'update_token',
            tenant_id: tenant,
            bearer_token: token
        }, function(resp) {
            try {
                var d = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                if (d.status === 'success') {
                    $('#tokenModal').modal('hide');
                    Swal.fire('สำเร็จ', d.message, 'success');
                    // อัพเดทปุ่มสถานะ
                    var btnLabel = document.getElementById('tokenStatusLabel');
                    var btnEl = document.getElementById('btnTokenConfig');
                    btnEl.classList.remove('btn-danger');
                    btnEl.classList.add('btn-secondary');
                    // คำนวณเวลาหมดอายุ
                    var now = new Date();
                    now.setHours(now.getHours() + 24);
                    var dd = String(now.getDate()).padStart(2,'0');
                    var mm = String(now.getMonth()+1).padStart(2,'0');
                    var yy = now.getFullYear() + 543;
                    var hh = String(now.getHours()).padStart(2,'0');
                    var min = String(now.getMinutes()).padStart(2,'0');
                    btnLabel.textContent = 'Token OK (หมด ' + dd + '/' + mm + '/' + yy + ' ' + hh + ':' + min + ')';
                } else {
                    Swal.fire('ผิดพลาด', d.message, 'error');
                }
            } catch(e) {
                Swal.fire('ผิดพลาด', 'ไม่สามารถอ่านผลลัพธ์ได้', 'error');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> บันทึก Token';
        }).fail(function() {
            Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> บันทึก Token';
        });
    }
</script>