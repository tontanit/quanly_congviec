<?php
require_once '../../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit(); }

$id = $_GET['id'];

// 1. Lấy dữ liệu cũ để đổ vào Form
$sql_old = "SELECT * FROM cong_viec WHERE id = $id";
$res_old = mysqli_query($conn, $sql_old);
$task = mysqli_fetch_assoc($res_old);

// 2. Lấy danh sách nhân viên để chọn lại nếu cần
$sql_users = "SELECT id, ho_ten FROM users WHERE role = 'nguoi_thuc_hien'";
$result_users = mysqli_query($conn, $sql_users);

// 3. Xử lý khi nhấn nút Lưu (Cập nhật)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_cv = mysqli_real_escape_string($conn, $_POST['ten_cong_viec']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $nguoi_lam_id = $_POST['nguoi_thuc_hien_id'];
    $ngay_nhan = $_POST['ngay_nhan'];
    $han_hoan_thanh = $_POST['han_hoan_thanh'];
    $trang_thai = $_POST['trang_thai'];

    $sql_update = "UPDATE cong_viec SET 
                   ten_cong_viec = '$ten_cv', 
                   mo_ta = '$mo_ta', 
                   nguoi_thuc_hien_id = '$nguoi_lam_id', 
                   ngay_nhan = '$ngay_nhan', 
                   han_hoan_thanh = '$han_hoan_thanh',
                   trang_thai = '$trang_thai'
                   WHERE id = $id";

    if (mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='list.php';</script>";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}

include '../../includes/header.php';
?>

<div class="card shadow">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fa fa-edit"></i> CHỈNH SỬA CÔNG VIỆC</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Tên công việc</label>
                <input type="text" name="ten_cong_viec" class="form-control" value="<?php echo $task['ten_cong_viec']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả chi tiết</label>
                <textarea name="mo_ta" class="form-control" rows="3"><?php echo $task['mo_ta']; ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Người thực hiện</label>
                    <select name="nguoi_thuc_hien_id" class="form-select" required>
                        <?php while($user = mysqli_fetch_assoc($result_users)): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo ($user['id'] == $task['nguoi_thuc_hien_id']) ? 'selected' : ''; ?>>
                                <?php echo $user['ho_ten']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="Chưa thực hiện" <?php if($task['trang_thai'] == 'Chưa thực hiện') echo 'selected'; ?>>Chưa thực hiện</option>
                        <option value="Đang thực hiện" <?php if($task['trang_thai'] == 'Đang thực hiện') echo 'selected'; ?>>Đang thực hiện</option>
                        <option value="Đã hoàn thành" <?php if($task['trang_thai'] == 'Đã hoàn thành') echo 'selected'; ?>>Đã hoàn thành</option>
                        <option value="Quá hạn" <?php if($task['trang_thai'] == 'Quá hạn') echo 'selected'; ?>>Quá hạn</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày giao</label>
                    <input type="date" name="ngay_nhan" class="form-control" value="<?php echo $task['ngay_nhan']; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Hạn hoàn thành</label>
                    <input type="date" name="han_hoan_thanh" class="form-control" value="<?php echo $task['han_hoan_thanh']; ?>" required>
                </div>
            </div>
            <hr>
            <div class="text-end">
                <a href="list.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-warning px-4">Cập nhật thay đổi</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>