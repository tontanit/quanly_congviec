<?php
require_once '../../config/database.php';

// Kiểm tra quyền: Chỉ Admin và Người giao mới được vào trang này
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'nguoi_thuc_hien') {
    header("Location: ../../index.php");
    exit();
}

include '../../includes/header.php';

// Lấy danh sách nhân viên để gán việc
$sql_users = "SELECT id, ho_ten FROM users WHERE role = 'nguoi_thuc_hien'";
$result_users = mysqli_query($conn, $sql_users);

// Xử lý khi nhấn nút Lưu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_cv = mysqli_real_escape_string($conn, $_POST['ten_cong_viec']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $nguoi_lam_id = $_POST['nguoi_thuc_hien_id'];
    $ngay_nhan = $_POST['ngay_nhan'];
    $han_hoan_thanh = $_POST['han_hoan_thanh'];
    $nguoi_giao_id = $_SESSION['user_id'];

    $sql_insert = "INSERT INTO cong_viec (ten_cong_viec, mo_ta, nguoi_giao_id, nguoi_thuc_hien_id, ngay_nhan, han_hoan_thanh) 
                   VALUES ('$ten_cv', '$mo_ta', '$nguoi_giao_id', '$nguoi_lam_id', '$ngay_nhan', '$han_hoan_thanh')";

    if (mysqli_query($conn, $sql_insert)) {
        echo "<script>alert('Giao việc thành công!'); window.location.href='../../index.php';</script>";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">TẠO CÔNG VIỆC MỚI</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Tên công việc</label>
                <input type="text" name="ten_cong_viec" class="form-control" placeholder="Ví dụ: Soạn thảo báo cáo tháng 1" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả chi tiết</label>
                <textarea name="mo_ta" class="form-control" rows="3"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Người thực hiện</label>
                    <select name="nguoi_thuc_hien_id" class="form-select" required>
                        <option value="">-- Chọn nhân viên --</option>
                        <?php while ($user = mysqli_fetch_assoc($result_users)): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo $user['ho_ten']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Ngày giao</label>
                    <input type="date" name="ngay_nhan" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Hạn hoàn thành</label>
                    <input type="date" name="han_hoan_thanh" class="form-control" required>
                </div>
            </div>
            <hr>
            <div class="text-end">
                <a href="../../index.php" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-success px-4">Lưu công việc</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>