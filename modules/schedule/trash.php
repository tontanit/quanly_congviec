<?php
require_once '../../config/database.php';

// Kiểm tra quyền (Chỉ Admin mới được vào Thùng rác)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include '../../includes/header.php';

// Lấy danh sách các lịch đã bị xóa mềm (is_deleted = 1)
$sql = "SELECT l.*, u.ho_ten FROM lich_cong_tac l 
        JOIN users u ON l.lanh_dao_id = u.id 
        WHERE l.is_deleted = 1 
        ORDER BY l.deleted_at DESC";
$res = mysqli_query($conn, $sql);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-danger mb-0">
                <i class="fa fa-trash-alt me-2"></i>THÙNG RÁC LỊCH CÔNG TÁC
            </h3>
            <p class="text-muted small mb-0">Danh sách các lịch đã xóa tạm thời. Bạn có thể khôi phục hoặc xóa vĩnh viễn.</p>
        </div>
        <div>
            <button onclick="emptyTrash()" class="btn btn-danger btn-sm shadow-sm me-2">
                <i class="fa fa-fire me-1"></i> Dọn sạch thùng rác
            </button>
            <a href="index.php" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Quay lại Lịch
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4">NỘI DUNG & THỜI GIAN</th>
                        <th>LOẠI LỊCH</th>
                        <th>NGƯỜI THỰC HIỆN</th>
                        <th>THỜI ĐIỂM XÓA</th>
                        <th class="text-end pe-4">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($res) > 0): while ($row = mysqli_fetch_assoc($res)): ?>
                            <tr id="row-<?= $row['id'] ?>">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['tieu_de']) ?></div>
                                    <div class="text-muted small">
                                        <i class="fa fa-clock me-1"></i>
                                        <?= date('H:i d/m/Y', strtotime($row['bat_dau'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $color = 'secondary';
                                    if ($row['loai_lich'] == 'Họp') $color = 'primary';
                                    if ($row['loai_lich'] == 'Công tác') $color = 'success';
                                    if ($row['loai_lich'] == 'Tiếp khách') $color = 'warning text-dark';
                                    ?>
                                    <span class="badge bg-<?= $color ?> rounded-pill fw-normal"><?= $row['loai_lich'] ?></span>
                                </td>
                                <td class="small text-secondary"><?= htmlspecialchars($row['ho_ten']) ?></td>
                                <td>
                                    <small class="text-danger">
                                        <?= date('d/m/Y H:i', strtotime($row['deleted_at'])) ?>
                                    </small>
                                </td>
                                <td class="text-end pe-4">
                                    <button onclick="restoreEvent(<?= $row['id'] ?>)" class="btn btn-sm btn-outline-success border-0 me-1" title="Khôi phục">
                                        <i class="fa fa-undo-alt"></i>
                                    </button>
                                    <button onclick="permanentDelete(<?= $row['id'] ?>)" class="btn btn-sm btn-outline-danger border-0" title="Xóa vĩnh viễn">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fa fa-folder-open fa-3x text-light mb-3 d-block"></i>
                                <span class="text-muted">Thùng rác hiện đang trống.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Cấu hình Toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // 1. Hàm Khôi phục lịch
        window.restoreEvent = function(id) {
            Swal.fire({
                title: 'Khôi phục lịch trình?',
                text: "Lịch này sẽ hiển thị lại trên bảng lịch chính.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('restore_event.php', {
                        id: id
                    }, function(res) {
                        if (res.status === 'success') {
                            $(`#row-${id}`).fadeOut(500);
                            Toast.fire({
                                icon: 'success',
                                title: res.message
                            });
                        } else {
                            Swal.fire('Lỗi', res.message, 'error');
                        }
                    });
                }
            });
        }

        // 2. Hàm Xóa vĩnh viễn 1 lịch
        window.permanentDelete = function(id) {
            Swal.fire({
                title: 'Xóa vĩnh viễn?',
                text: "Dữ liệu sẽ bị mất hoàn toàn và không thể khôi phục!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Xác nhận xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('empty_trash.php', {
                        id: id
                    }, function(res) {
                        if (res.status === 'success') {
                            $(`#row-${id}`).fadeOut(500);
                            Toast.fire({
                                icon: 'success',
                                title: res.message
                            });
                        } else {
                            Swal.fire('Lỗi', res.message, 'error');
                        }
                    });
                }
            });
        }

        // 3. Hàm Dọn sạch toàn bộ thùng rác
        window.emptyTrash = function() {
            Swal.fire({
                title: 'Dọn sạch thùng rác?',
                text: "Tất cả dữ liệu trong thùng rác sẽ bị tiêu hủy vĩnh viễn!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Xác nhận dọn sạch',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('empty_trash.php', {
                        action: 'clear_all'
                    }, function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Thành công', res.message, 'success').then(() => {
                                location.reload();
                            });
                        }
                    });
                }
            });
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>