<?php
require_once '../include/Session.php';
Session::checkLoginSessionAndShowMessage();
require_once '../mains/main-report.php'; // Top nav

?>
<div class="container-fluid mt-4 mb-5">
    <div class="row mb-3">
        <div class="col">
            <h4 class="mb-0"><i class="fas fa-cogs text-primary"></i> ตั้งค่ากลุ่มและเอกสารสำหรับการพิมพ์แบบกลุ่ม</h4>
        </div>
    </div>

    <div class="row">
        <!-- ฝั่งซ้าย: จัดการกลุ่ม (Group) -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">1. รายการกลุ่ม (Groups)</h6>
                    <button class="btn btn-sm btn-light" onclick="openGroupModal()">
                        <i class="fas fa-plus"></i> เพิ่มกลุ่ม
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>รหัส</th>
                                <th>ชื่อกลุ่ม</th>
                                <th width="120" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="groupTbody">
                            <!-- Data -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ฝั่งขวา: จัดการเอกสาร (Items) -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">2. เอกสารในกลุ่ม: <span id="currentGroupName" class="text-warning">กรุณาเลือกกลุ่มจากด้านซ้าย</span></h6>
                    <button class="btn btn-sm btn-light" id="btnAddItem" onclick="openItemModal()">
                        <i class="fas fa-plus"></i> เพิ่มเอกสาร
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="60" class="text-center">ลำดับ</th>
                                <th>ชื่อเอกสารแสดงผล</th>
                                <th>ชื่อไฟล์สคริปต์ (PDF Script)</th>
                                <th width="120" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="itemTbody">
                            <tr><td colspan="4" class="text-center text-muted py-4">กรุณาเลื่อกกลุ่มเพื่อดูรายการเอกสาร</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Group -->
<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="groupForm" class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="groupModalTitle">เพิ่มกลุ่มใหม่</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_group_print" name="group_print">
                <div class="form-group">
                    <label>ชื่อกลุ่ม <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modal_group_name" name="group_name" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Item -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="itemForm" class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="itemModalTitle">เพิ่มเอกสารในกลุ่ม</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_item_id" name="id">
                <input type="hidden" id="modal_item_group_print" name="group_print">
                
                <div class="form-group">
                    <label>ชื่อเอกสารแสดงผล <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modal_document_name" name="document_name" placeholder="เช่น แบบประเมิน Alcohol" required>
                </div>
                <div class="form-group">
                    <label>ชื่อไฟล์สคริปต์ PDF (พร้อมชื่อแฟ้ม) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modal_pdf_script" name="pdf_script" placeholder="เช่น pdffile/alcohol-pdf.php หรือ allpdf/xxx-pdf.php" required>
                    <small class="text-muted">ใส่ชื่อแฟ้มนำหน้าด้วย เช่น <b>pdffile/alcohol-pdf.php</b> หรือ <b>allpdf/report.php</b> (หากไม่ใส่โฟลเดอร์ ระบบจะถือว่าเป็น pdffile/ ให้โดยอัตโนมัติ)</small>
                </div>
                <div class="form-group">
                    <label>ลำดับการเรียง (Sort Order)</label>
                    <input type="number" class="form-control" id="modal_sort_order" name="sort_order" value="0">
                    <small class="text-muted">ตัวเลขยิ่งน้อย ยิ่งอยู่บนสุด</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
<style>
    .cursor-pointer { cursor: pointer; }
    .tr-active { background-color: #d1ecf1 !important; font-weight: bold; }
</style>

<script>
let currentSelectedGroup = 0;

$(document).ready(function() {
    loadGroups();

    // Submit Group Form
    $('#groupForm').submit(function(e) {
        e.preventDefault();
        $.post('print-group-setup-api.php?action=save_group', $(this).serialize(), function(res) {
            if(res.success) {
                $('#groupModal').modal('hide');
                loadGroups();
                Swal.fire({icon: 'success', title: 'บันทึกสำเร็จ', timer: 1000, showConfirmButton: false});
            } else {
                Swal.fire('ข้อผิดพลาด', res.message, 'error');
            }
        }, 'json');
    });

    // Submit Item Form
    $('#itemForm').submit(function(e) {
        e.preventDefault();
        $.post('print-group-setup-api.php?action=save_item', $(this).serialize(), function(res) {
            if(res.success) {
                $('#itemModal').modal('hide');
                loadItems(currentSelectedGroup);
                Swal.fire({icon: 'success', title: 'บันทึกสำเร็จ', timer: 1000, showConfirmButton: false});
            } else {
                Swal.fire('ข้อผิดพลาด', res.message, 'error');
            }
        }, 'json');
    });
});

function loadGroups() {
    $.get('print-group-setup-api.php?action=get_groups', function(res) {
        let html = '';
        if(res.success && res.data.length > 0) {
            res.data.forEach(g => {
                let activeClass = (g.group_print == currentSelectedGroup) ? 'tr-active' : '';
                let safeName = $('<div>').text(g.group_name).html().replace(/'/g, "\\'");
                html += `<tr class="cursor-pointer ${activeClass}" data-id="${g.group_print}" onclick="selectGroup(${g.group_print}, '${safeName}')">
                    <td class="align-middle">${g.group_print}</td>
                    <td class="align-middle">${g.group_name}</td>
                    <td class="text-center align-middle" onclick="event.stopPropagation();">
                        <button class="btn btn-sm btn-warning" onclick="editGroup(${g.group_print}, '${safeName}')"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteGroup(${g.group_print})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
        } else {
            html = '<tr><td colspan="3" class="text-center">ไม่พบข้อมูลกลุ่ม</td></tr>';
        }
        $('#groupTbody').html(html);
        
        // Refresh items if a group is still selected
        if(currentSelectedGroup > 0) {
            loadItems(currentSelectedGroup);
        }
    }, 'json');
}

function selectGroup(groupId, groupName) {
    currentSelectedGroup = groupId;
    $('#groupTbody tr').removeClass('tr-active');
    $('#groupTbody tr[data-id="' + groupId + '"]').addClass('tr-active');
    $('#currentGroupName').text(groupName);
    loadItems(groupId);
}

function loadItems(groupId) {
    if(groupId <= 0) return;
    $.get('print-group-setup-api.php?action=get_items&group_print=' + groupId, function(res) {
        let html = '';
        if(res.success && res.data.length > 0) {
            res.data.forEach(item => {
                let encodedDoc = $('<div>').text(item.document_name).html();
                let encodedPdf = $('<div>').text(item.pdf_script).html();
                html += `<tr>
                    <td class="text-center align-middle">${item.sort_order}</td>
                    <td class="align-middle">${encodedDoc}</td>
                    <td class="align-middle text-primary">${encodedPdf}</td>
                    <td class="text-center align-middle">
                        <button class="btn btn-sm btn-warning" onclick="editItem(${item.id}, '${encodedDoc}', '${encodedPdf}', ${item.sort_order})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteItem(${item.id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
        } else {
            html = '<tr><td colspan="4" class="text-center py-4">ยังไม่มีเอกสารในกลุ่มนี้</td></tr>';
        }
        $('#itemTbody').html(html);
    }, 'json');
}

// Group Modals
function openGroupModal() {
    $('#groupForm')[0].reset();
    $('#modal_group_print').val('');
    $('#groupModalTitle').text('เพิ่มกลุ่มใหม่');
    $('#groupModal').modal('show');
}
function editGroup(id, name) {
    $('#groupForm')[0].reset();
    $('#modal_group_print').val(id);
    $('#modal_group_name').val(name);
    $('#groupModalTitle').text('แก้ไขชื่อกลุ่ม');
    $('#groupModal').modal('show');
}
function deleteGroup(id) {
    Swal.fire({
        title: 'ยืนยันการลบกลุ่ม?',
        text: 'เอกสารทั้งหมดในกลุ่มนี้จะถูกลบไปด้วย!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('print-group-setup-api.php?action=delete_group', {group_print: id}, function(res) {
                if(res.success) {
                    if(currentSelectedGroup == id) {
                        currentSelectedGroup = 0;
                        $('#currentGroupName').text('กรุณาเลือกกลุ่มจากด้านซ้าย');
                        $('#btnAddItem').prop('disabled', true);
                        $('#itemTbody').html('<tr><td colspan="4" class="text-center text-muted py-4">กรุณาเลื่อกกลุ่มเพื่อดูรายการเอกสาร</td></tr>');
                    }
                    loadGroups();
                } else {
                    Swal.fire('ข้อผิดพลาด', res.message, 'error');
                }
            }, 'json');
        }
    });
}

// Item Modals
function openItemModal() {
    if(currentSelectedGroup <= 0) {
        Swal.fire('คำแนะนำ', 'กรุณาคลิกเลือกรายการ "กลุ่มเอกสาร" ที่ฝั่งซ้ายก่อนครับ เพื่อกำหนดว่าจะเพิ่มเอกสารลงในกลุ่มไหน', 'info');
        return;
    }
    $('#itemForm')[0].reset();
    $('#modal_item_id').val('');
    $('#modal_item_group_print').val(currentSelectedGroup);
    $('#itemModalTitle').text('เพิ่มเอกสารใหม่');
    
    // Auto increment sort_order visually
    let maxSort = 0;
    $('#itemTbody tr').each(function() {
        let val = parseInt($(this).find('td:first').text());
        if(!isNaN(val) && val > maxSort) maxSort = val;
    });
    $('#modal_sort_order').val(maxSort + 1);
    
    $('#itemModal').modal('show');
}
function editItem(id, docName, pdfScript, sortOrder) {
    $('#itemForm')[0].reset();
    $('#modal_item_id').val(id);
    $('#modal_item_group_print').val(currentSelectedGroup);
    $('#modal_document_name').val(docName);
    $('#modal_pdf_script').val(pdfScript);
    $('#modal_sort_order').val(sortOrder);
    $('#itemModalTitle').text('แก้ไขเอกสาร');
    $('#itemModal').modal('show');
}
function deleteItem(id) {
    Swal.fire({
        title: 'ยืนยันการลบเอกสาร?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'ลบเอกสาร'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('print-group-setup-api.php?action=delete_item', {id: id}, function(res) {
                if(res.success) {
                    loadItems(currentSelectedGroup);
                } else {
                    Swal.fire('ข้อผิดพลาด', res.message, 'error');
                }
            }, 'json');
        }
    });
}
</script>
