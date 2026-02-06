<?php
require_once '../../config/database.php';

// Kiểm tra quyền: Chỉ Admin và Người giao mới được vào trang này
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'nguoi_thuc_hien') {
    header("Location: ../../index.php");
    exit();
}

// Lấy danh sách nhân viên để gán việc
$sql_users = "SELECT id, ho_ten FROM users WHERE role = 'nguoi_thuc_hien'";
$result_users = mysqli_query($conn, $sql_users);

// Xử lý khi nhấn nút Lưu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_cv = mysqli_real_escape_string($conn, $_POST['ten_cong_viec']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $nguoi_lam_id = (int)$_POST['nguoi_thuc_hien_id'];
    $ngay_nhan = $_POST['ngay_nhan'];
    $han_hoan_thanh = $_POST['han_hoan_thanh'];
    $nguoi_giao_id = $_SESSION['user_id'];

    $sql_insert = "INSERT INTO cong_viec (ten_cong_viec, mo_ta, nguoi_giao_id, nguoi_thuc_hien_id, ngay_nhan, han_hoan_thanh, trang_thai) 
                   VALUES ('$ten_cv', '$mo_ta', '$nguoi_giao_id', '$nguoi_lam_id', '$ngay_nhan', '$han_hoan_thanh', 'Chưa thực hiện')";

    if (mysqli_query($conn, $sql_insert)) {
        // Gán thông báo thành công vào session để list.php hiển thị Toast
        $_SESSION['success'] = "Đã tạo và giao công việc mới thành công!";
        header("Location: list.php"); // Chuyển về trang danh sách
        exit();
    } else {
        $error = "Lỗi hệ thống: " . mysqli_error($conn);
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
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }

    .input-group-text {
        background-color: #f8f9fa;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="list.php" class="btn btn-outline-primary btn-sm rounded-circle me-3 shadow-sm">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0 text-dark text-uppercase">Tạo công việc mới</h3>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger shadow-sm animate__animated animate__shakeX">
                    <i class="fa fa-exclamation-circle me-2"></i><?= $error ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa fa-file-signature me-2"></i>FORM GIAO VIỆC</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label">Tên công việc</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-edit text-primary"></i></span>
                                <input type="text" name="ten_cong_viec" class="form-control form-control-lg"
                                    placeholder="Nhập tên công việc ngắn gọn..." required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Mô tả chi tiết</label>
                            <textarea name="mo_ta" class="form-control" rows="4"
                                placeholder="Mô tả nội dung, yêu cầu hoặc đính kèm link tài liệu nếu có..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Người thực hiện</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-user-check text-primary"></i></span>
                                    <select name="nguoi_thuc_hien_id" class="form-select" required>
                                        <option value="">-- Chọn nhân viên --</option>
                                        <?php while ($user = mysqli_fetch_assoc($result_users)): ?>
                                            <option value="<?= $user['id']; ?>"><?= htmlspecialchars($user['ho_ten']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Ngày giao (Mặc định)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-calendar-day text-primary"></i></span>
                                    <input type="date" name="ngay_nhan" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label text-danger">Hạn hoàn thành (Deadline)</label>
                                <div class="input-group border border-danger rounded">
                                    <span class="input-group-text bg-danger text-white border-0"><i class="fa fa-clock"></i></span>
                                    <input type="date" name="han_hoan_thanh" class="form-control border-0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Trạng thái ban đầu</label>
                                <input type="text" class="form-control bg-light" value="Chưa thực hiện" readonly>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="list.php" class="btn btn-light px-4 fw-bold text-muted">Hủy bỏ</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                                <i class="fa fa-paper-plane me-2"></i>GIAO VIỆC NGAY
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>