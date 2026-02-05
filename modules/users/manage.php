<?php
require_once '../../config/database.php';

// 1. KIỂM TRA QUYỀN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

$success = '';
$error = '';

// 2. XỬ LÝ XÓA CÁN BỘ
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];

    if ($del_id === (int)$_SESSION['user_id']) {
        $error = "Bạn không thể tự xóa tài khoản của chính mình!";
    } else {
        $check_task = mysqli_query($conn, "SELECT id FROM cong_viec WHERE nguoi_thuc_hien_id = $del_id LIMIT 1");

        if (mysqli_num_rows($check_task) > 0) {
            $error = "Không thể xóa! Cán bộ đang phụ trách công việc. Hãy bàn giao trước khi xóa.";
        } else {
            if (mysqli_query($conn, "DELETE FROM users WHERE id = $del_id")) {
                $success = "Đã xóa cán bộ khỏi hệ thống thành công.";
            } else {
                $error = "Lỗi hệ thống: Không thể thực hiện lệnh xóa.";
            }
        }
    }
}

// 3. XỬ LÝ THÊM NGƯỜI DÙNG MỚI
if (isset($_POST['add_user'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $ho_ten   = mysqli_real_escape_string($conn, trim($_POST['ho_ten']));
    $role     = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Tên đăng nhập '$username' đã tồn tại!";
    } else {
        $sql = "INSERT INTO users (username, password, ho_ten, role) VALUES ('$username', '$password', '$ho_ten', '$role')";
        if (mysqli_query($conn, $sql)) {
            $success = "Thêm cán bộ " . htmlspecialchars($ho_ten) . " thành công!";
        }
    }
}

// 4. LẤY DANH SÁCH
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY 
            CASE WHEN role = 'admin' THEN 1 WHEN role = 'nguoi_giao_viec' THEN 2 ELSE 3 END ASC, ho_ten ASC");

include '../../includes/header.php';
?>

<div class="container-fluid pt-3">
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm text-center fw-bold" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div id="auto-close-alert"
            class="alert alert-warning alert-dismissible fade show shadow-sm text-center fw-bold border-0 border-start border-5 border-warning"
            role="alert">
            <span style="color: #fd7e14;">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?php echo $success; ?>
            </span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0"><i class="fa fa-user-plus me-2"></i>Thêm cán bộ mới</h6>
                </div>
                <div class="card-body">
                    <form method="POST" autocomplete="off">
                        <div class="mb-2">
                            <label class="small fw-bold">Tên đăng nhập</label>
                            <input type="text" name="username" id="username_input" class="form-control" required autocomplete="off">
                            <div id="username_feedback" class="small mt-1 fw-bold"></div>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Họ và tên cán bộ</label>
                            <input type="text" name="ho_ten" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Vai trò hệ thống</label>
                            <select name="role" class="form-select">
                                <option value="nguoi_thuc_hien">Người thực hiện (Chuyên viên)</option>
                                <option value="nguoi_giao_viec">Người giao việc (Lãnh đạo)</option>
                                <option value="admin">Quản trị viên (Admin)</option>
                            </select>
                        </div>
                        <button type="submit" name="add_user" class="btn btn-primary w-100 fw-bold">Khởi tạo tài khoản</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold py-3 text-primary">
                    <i class="fa fa-users me-2"></i>Danh sách cán bộ trong hệ thống
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Họ tên</th>
                                <th>Username</th>
                                <th>Vai trò</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($u = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td><strong><?php echo $u['ho_ten']; ?></strong></td>
                                    <td><?php echo $u['username']; ?></td>
                                    <td>
                                        <?php
                                        $r = trim($u['role']);
                                        if ($r == 'admin') echo '<span class="badge bg-danger">Admin</span>';
                                        elseif ($r == 'nguoi_giao_viec') echo '<span class="badge bg-primary">Người giao việc</span>';
                                        else echo '<span class="badge bg-info text-dark">Người thực hiện</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="edit.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-warning" title="Sửa">
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <a href="manage.php?delete_id=<?php echo $u['id']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa cán bộ này?')"
                                                title="Xóa">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Bạn</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Tự động đóng Alert thành công sau 3 giây
        const alertTarget = document.getElementById('auto-close-alert');
        if (alertTarget) {
            setTimeout(() => {
                // Sử dụng Instance của Bootstrap để đóng an toàn
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertTarget);
                if (bsAlert) bsAlert.close();
            }, 3000);
        }

        // 2. Kiểm tra Username tồn tại qua AJAX
        const usernameInput = document.getElementById('username_input');
        const feedback = document.getElementById('username_feedback');
        const submitBtn = document.querySelector('button[name="add_user"]');

        if (usernameInput) {
            usernameInput.addEventListener('keyup', function() {
                const username = this.value.trim();

                if (username.length < 3) {
                    feedback.innerHTML = "";
                    if (submitBtn) submitBtn.disabled = false;
                    return;
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'check_username.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                xhr.onload = function() {
                    if (this.status === 200) {
                        const isExists = (this.responseText.trim() === 'exists');
                        feedback.innerHTML = isExists ?
                            '<i class="fa fa-times-circle"></i> Tài khoản đã tồn tại!' :
                            '<i class="fa fa-check-circle"></i> Tên đăng nhập hợp lệ';

                        feedback.className = "small mt-1 fw-bold " + (isExists ? "text-danger" : "text-success");
                        if (submitBtn) submitBtn.disabled = isExists;
                    }
                };
                xhr.send('username=' + encodeURIComponent(username));
            });
        }
    });
</script>
<?php include '../../includes/footer.php'; ?>