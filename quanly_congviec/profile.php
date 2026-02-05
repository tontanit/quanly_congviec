<?php
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// 1. Lấy thông tin hiện tại của người dùng
$sql = "SELECT * FROM users WHERE id = $user_id";
$res = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($res);

// 2. Xử lý khi người dùng nhấn Lưu thông tin
if (isset($_POST['update_info'])) {
    $ho_ten = mysqli_real_escape_string($conn, $_POST['ho_ten']);

    $sql_update = "UPDATE users SET ho_ten = '$ho_ten' WHERE id = $user_id";
    if (mysqli_query($conn, $sql_update)) {
        $_SESSION['ho_ten'] = $ho_ten; // Cập nhật lại session để hiển thị trên header
        $success = "Cập nhật thông tin thành công!";
    }
}

// 3. Xử lý khi người dùng đổi mật khẩu
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Kiểm tra mật khẩu cũ
    if (password_verify($old_pass, $user['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $sql_pw = "UPDATE users SET password = '$hashed_pass' WHERE id = $user_id";
            mysqli_query($conn, $sql_pw);
            $success = "Đổi mật khẩu thành công!";
        } else {
            $error = "Mật khẩu mới không khớp nhau!";
        }
    } else {
        $error = "Mật khẩu cũ không chính xác!";
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h3 class="mb-4"><i class="fa-solid fa-user-gear"></i> Quản lý hồ sơ cá nhân</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Thông tin tài khoản</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $user['username']; ?>" readonly>
                        <div class="form-text">Tên đăng nhập không thể thay đổi.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Họ và tên</label>
                        <input type="text" name="ho_ten" class="form-control" value="<?php echo $user['ho_ten']; ?>" required>
                    </div>
                    <button type="submit" name="update_info" class="btn btn-primary">Lưu thay đổi</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold text-danger">Bảo mật & Mật khẩu</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-danger">Đổi mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>