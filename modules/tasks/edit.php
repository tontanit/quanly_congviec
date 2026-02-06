<?php
require_once '../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$id = (int)$_GET['id'];
$current_user_id = $_SESSION['user_id'];

// 1. Lấy dữ liệu cũ
$sql_old = "SELECT * FROM cong_viec WHERE id = $id";
$res_old = mysqli_query($conn, $sql_old);
$task = mysqli_fetch_assoc($res_old);

if (!$task) {
    header("Location: list.php");
    exit();
}

// 2. Lấy danh sách nhân viên
$sql_users = "SELECT id, ho_ten FROM users WHERE role = 'nguoi_thuc_hien'";
$result_users = mysqli_query($conn, $sql_users);

// 3. Xử lý khi nhấn nút Cập nhật
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_cv = mysqli_real_escape_string($conn, $_POST['ten_cong_viec']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $nguoi_lam_id = (int)$_POST['nguoi_thuc_hien_id'];
    $ngay_nhan = $_POST['ngay_nhan'];
    $han_hoan_thanh = $_POST['han_hoan_thanh'];
    $trang_thai = $_POST['trang_thai'];

    // --- LOGIC GHI LOG TỰ ĐỘNG ---
    $log_messages = [];
    if ($trang_thai !== $task['trang_thai']) {
        $log_messages[] = "📢 Hệ thống: Trạng thái thay đổi từ [" . $task['trang_thai'] . "] thành [" . $trang_thai . "].";
    }
    if ($han_hoan_thanh !== $task['han_hoan_thanh']) {
        $old_date = date('d/m/Y', strtotime($task['han_hoan_thanh']));
        $new_date = date('d/m/Y', strtotime($han_hoan_thanh));
        $log_messages[] = "📅 Hệ thống: Đã thay đổi hạn chót từ $old_date thành $new_date.";
    }

    $sql_update = "UPDATE cong_viec SET 
                   ten_cong_viec = '$ten_cv', 
                   mo_ta = '$mo_ta', 
                   nguoi_thuc_hien_id = $nguoi_lam_id, 
                   ngay_nhan = '$ngay_nhan', 
                   han_hoan_thanh = '$han_hoan_thanh',
                   trang_thai = '$trang_thai'
                   WHERE id = $id";

    if (mysqli_query($conn, $sql_update)) {
        // Ghi log vào bình luận
        foreach ($log_messages as $msg) {
            $msg_escaped = mysqli_real_escape_string($conn, $msg);
            $sql_log = "INSERT INTO binh_luan (cong_viec_id, user_id, noi_dung) 
                        VALUES ($id, $current_user_id, '$msg_escaped')";
            mysqli_query($conn, $sql_log);
        }

        // Tạo session thông báo để list.php bắt được bằng SweetAlert2
        $_SESSION['success'] = "Đã cập nhật công việc thành công!";
        header("Location: list.php");
        exit();
    } else {
        $error = "Lỗi cập nhật: " . mysqli_error($conn);
    }
}

include '../../includes/header.php';
?>

<style>
    body {
        background-color: #f4f7f6;
    }

    .card {
        border-radius: 15px;
        border: none;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.25 row rgba(255, 193, 7, 0.25);
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="list.php" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0 text-dark text-uppercase">Chỉnh sửa công việc</h3>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger shadow-sm"><?= $error ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa fa-edit me-2"></i>THÔNG TIN CHI TIẾT</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="editForm">
                        <div class="mb-4">
                            <label class="form-label"><i class="fa fa-tag me-2 text-warning"></i>Tên công việc</label>
                            <input type="text" name="ten_cong_viec" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($task['ten_cong_viec']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><i class="fa fa-align-left me-2 text-warning"></i>Mô tả chi tiết</label>
                            <textarea name="mo_ta" class="form-control" rows="4"
                                placeholder="Nhập nội dung hướng dẫn..."><?= htmlspecialchars($task['mo_ta']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fa fa-user me-2 text-warning"></i>Người thực hiện</label>
                                <select name="nguoi_thuc_hien_id" class="form-select" required>
                                    <?php while ($user = mysqli_fetch_assoc($result_users)): ?>
                                        <option value="<?= $user['id']; ?>" <?= ($user['id'] == $task['nguoi_thuc_hien_id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($user['ho_ten']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fa fa-tasks me-2 text-warning"></i>Trạng thái</label>
                                <select name="trang_thai" class="form-select fw-bold text-primary">
                                    <?php
                                    $status_list = ['Chưa thực hiện', 'Đang thực hiện', 'Đã hoàn thành', 'Quá hạn'];
                                    foreach ($status_list as $st): ?>
                                        <option value="<?= $st ?>" <?= ($task['trang_thai'] == $st) ? 'selected' : ''; ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fa fa-calendar-plus me-2 text-warning"></i>Ngày giao</label>
                                <input type="date" name="ngay_nhan" class="form-control" value="<?= $task['ngay_nhan']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fa fa-calendar-check me-2 text-warning"></i>Hạn hoàn thành</label>
                                <input type="date" name="han_hoan_thanh" class="form-control" value="<?= $task['han_hoan_thanh']; ?>" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-light px-4 fw-bold" onclick="history.back()">Hủy bỏ</button>
                            <button type="submit" class="btn btn-warning px-5 fw-bold text-dark shadow-sm">
                                <i class="fa fa-save me-2"></i>CẬP NHẬT THAY ĐỔI
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Thêm hiệu ứng xác nhận trước khi lưu nếu cần (Tùy chọn)
    $('#editForm').on('submit', function(e) {
        // Bạn có thể thêm confirm ở đây nếu muốn người dùng chắc chắn về các thay đổi log
    });
</script>

<?php include '../../includes/footer.php'; ?>