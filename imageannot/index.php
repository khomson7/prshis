<?php
require_once '../include/Session.php';
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) { header('Location: ../login.php'); exit; }

require_once '../mains/main-report.php';
require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/session-modal.php';

date_default_timezone_set('Asia/Bangkok');

$conn = DbUtils::get_hosxp_connection();
$an   = trim($_REQUEST['an'] ?? '');

// ---- ดึงรายการทั้งหมดของ AN ----
// ไม่ SELECT image_data/annotated_image เพื่อลด memory
$stmt = $conn->prepare("SELECT id, an, title, doc_group, original_name,
                                image_type, created_by, created_at, updated_at
                           FROM prs_image_annot
                          WHERE an = :an AND is_deleted = 0
                          ORDER BY created_at DESC");
$stmt->execute(['an' => $an]);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

Session::insertSystemAccessLog(json_encode([
    'form' => 'IMAGE-ANNOT-LIST', 'an' => $an,
], JSON_UNESCAPED_UNICODE));
?>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<style>
.img-card        { border:1px solid #dee2e6; border-radius:8px; overflow:hidden;
                   background:#fff; transition:box-shadow .2s; }
.img-card:hover  { box-shadow:0 3px 14px rgba(0,0,0,.13); }
.img-thumb       { width:100%; height:160px; object-fit:cover; background:#f1f3f5;
                   display:block; cursor:pointer; }
.img-thumb-placeholder { width:100%; height:160px; background:#f1f3f5;
                          display:flex; align-items:center; justify-content:center;
                          color:#adb5bd; font-size:32px; }
.img-card-body   { padding:10px 12px; }
.img-title       { font-weight:700; font-size:13px; margin-bottom:3px;
                   white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.badge-group     { font-size:.72rem; padding:2px 7px; border-radius:10px;
                   background:#e8f4fd; color:#0c5460; border:1px solid #bee5eb; }
.img-meta        { font-size:.72rem; color:#6c757d; margin-top:4px; }
.img-actions     { display:flex; gap:4px; flex-wrap:wrap; margin-top:8px; }
.filter-btn.active { background:#2c6e49; color:#fff; border-color:#2c6e49; }
</style>

<div class="container-fluid mt-2">
  <div class="d-flex align-items-center mb-3">
    <h5 class="mb-0 mr-auto">
      <i class="fas fa-images text-success"></i> ภาพถ่ายและ Annotation
    </h5>
    <a href="form.php?an=<?= urlencode($an) ?>" class="btn btn-success btn-sm shadow-sm">
      <i class="fas fa-camera"></i> Upload ภาพใหม่
    </a>
  </div>

  <?php if (empty($list)): ?>
  <div class="alert alert-light text-center text-muted py-5">
    <i class="fas fa-image fa-3x mb-3 d-block"></i>
    ยังไม่มีภาพสำหรับผู้ป่วยรายนี้
  </div>
  <?php else: ?>

  <!-- Filter -->
  <div class="mb-3">
    <span class="text-muted small">กรองตามกลุ่ม:</span>
    <button class="btn btn-sm btn-outline-secondary ml-1 filter-btn active" data-group="all">ทั้งหมด</button>
    <?php
    $used = array_unique(array_column($list, 'doc_group'));
    foreach ($used as $g): if (!$g) continue; ?>
    <button class="btn btn-sm btn-outline-info ml-1 filter-btn" data-group="<?= htmlspecialchars($g) ?>">
      <?= htmlspecialchars($g) ?>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Grid -->
  <div class="row" id="imgGrid">
  <?php foreach ($list as $r):
      $is_owner  = ($r['created_by'] === $loginname);
      $dt = $r['updated_at'] ?: $r['created_at'];
      $dt_thai = date('d/m/', strtotime($dt)) . (date('Y', strtotime($dt)) + 543) . date(' H:i', strtotime($dt));
  ?>
  <div class="col-6 col-md-3 col-lg-2 mb-3 img-col" data-group="<?= htmlspecialchars($r['doc_group'] ?? '') ?>">
    <div class="img-card">
      <!-- Thumbnail โหลดจาก getimage.php type=annotated -->
      <img class="img-thumb"
           src="getimage.php?type=annotated&id=<?= $r['id'] ?>&an=<?= urlencode($an) ?>"
           onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
           onclick="previewImg(<?= $r['id'] ?>)"
           alt="<?= htmlspecialchars($r['title']) ?>"
           loading="lazy">
      <div class="img-thumb-placeholder" style="display:none">
        <i class="fas fa-image"></i>
      </div>

      <div class="img-card-body">
        <div class="img-title"><?= htmlspecialchars($r['title']) ?></div>
        <?php if ($r['doc_group']): ?>
        <span class="badge-group"><?= htmlspecialchars($r['doc_group']) ?></span>
        <?php endif; ?>
        <div class="img-meta">
          <i class="fas fa-user"></i> <?= htmlspecialchars($r['created_by']) ?><br>
          <i class="fas fa-clock"></i> <?= $dt_thai ?>
        </div>
        <div class="img-actions">
          <!-- ดูภาพเต็ม -->
          <button class="btn btn-sm btn-info flex-grow-1"
                  onclick="previewImg(<?= $r['id'] ?>)" title="ดูภาพ">
            <i class="fas fa-eye"></i>
          </button>
          <!-- PDF -->
          <a href="pdf.php?an=<?= urlencode($an) ?>&id=<?= $r['id'] ?>" target="_blank"
             class="btn btn-sm btn-danger" title="PDF">
            <i class="fas fa-file-pdf"></i>
          </a>
          <?php if ($is_owner): ?>
          <!-- แก้ไข -->
          <a href="form.php?an=<?= urlencode($an) ?>&id=<?= $r['id'] ?>"
             class="btn btn-sm btn-warning" title="แก้ไข">
            <i class="fas fa-edit"></i>
          </a>
          <!-- ลบ -->
          <button class="btn btn-sm btn-outline-danger"
                  onclick="doDelete(<?= $r['id'] ?>)" title="ลบ">
            <i class="fas fa-trash"></i>
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  </div><!-- /row -->
  <?php endif; ?>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title mb-0" id="previewTitle"></h6>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body text-center p-2">
        <img id="previewImgEl" src="" style="max-width:100%; height:auto;" alt="preview">
      </div>
    </div>
  </div>
</div>

<script>
var currentAn = '<?= htmlspecialchars($an) ?>';

// ---- Filter ----
document.querySelectorAll('.filter-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); });
        this.classList.add('active');
        var group = this.dataset.group;
        document.querySelectorAll('.img-col').forEach(function(col) {
            col.style.display = (group === 'all' || col.dataset.group === group) ? '' : 'none';
        });
    });
});

// ---- Preview ----
function previewImg(id) {
    document.getElementById('previewImgEl').src =
        'getimage.php?type=annotated&id=' + id + '&an=' + encodeURIComponent(currentAn);
    $('#previewModal').modal('show');
}

// ---- Delete ----
function doDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        icon:  'warning',
        showCancelButton:  true,
        confirmButtonColor:'#dc3545',
        confirmButtonText: 'ลบ',
        cancelButtonText:  'ยกเลิก',
    }).then(function(r) {
        if (!r.isConfirmed) return;
        $.post('delete.php', { id: id, an: currentAn }, function(resp) {
            try {
                var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                if (data.status === 'success') {
                    Swal.fire('ลบสำเร็จ','','success').then(function(){ location.reload(); });
                } else {
                    Swal.fire('ผิดพลาด', data.message, 'error');
                }
            } catch(ex) {
                Swal.fire('ผิดพลาด', 'ไม่สามารถอ่านผลลัพธ์ได้', 'error');
            }
        });
    });
}
</script>
