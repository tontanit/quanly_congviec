<?php
require_once '../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

$id = $_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
$u = mysqli_fetch_assoc($res);

if (isset($_POST['update_user'])) {
    $ho_ten = mysqli_real_escape_string($conn, $_POST['ho_ten']);
    $role = $_POST['role'];

    $sql = "UPDATE users SET ho_ten = '$ho_ten', role = '$role' WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='manage.php';</script>";
    }
}

include '../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-warning"><strong>Sửa thông tin cán bộ</strong></div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $u['username']; ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Họ và tên</label>
                        <input type="text" name="ho_ten" class="form-control" value="<?php echo $u['ho_ten']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vai trò</label>
                        <select name="role" class="form-select">
                            <option value="nguoi_thuc_hien" <?php if ($u['role'] == 'nguoi_thuc_hien') echo 'selected'; ?>>Người thực hiện</option>
                            <option value="nguoi_giao_viec" <?php if ($u['role'] == 'nguoi_giao_viec') echo 'selected'; ?>>Người giao việc</option>
                            <option value="admin" <?php if ($u['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <a href="manage.php" class="btn btn-secondary">Hủy</a>
                        <button type="submit" name="update_user" class="btn btn-warning">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>