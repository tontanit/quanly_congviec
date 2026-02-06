<?php
require_once '../../config/database.php';

// 1. Bảo mật: Chỉ Admin mới được quyền xóa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Nếu không phải admin, tạo session lỗi và đẩy về trang danh sách
    $_SESSION['error'] = "Bạn không có quyền thực hiện thao tác này!";
    header("Location: list.php");
    exit();
}

// 2. Kiểm tra ID và thực hiện xóa
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Lệnh xóa (Tận dụng ON DELETE CASCADE để xóa các bảng liên quan như bình luận, file)
    $sql_delete = "DELETE FROM cong_viec WHERE id = $id";

    if (mysqli_query($conn, $sql_delete)) {
        // Kiểm tra xem có thực sự xóa được dòng nào không (tránh ID không tồn tại)
        if (mysqli_affected_rows($conn) > 0) {
            $_SESSION['success'] = "Đã xóa công việc vĩnh viễn khỏi hệ thống!";
        } else {
            $_SESSION['error'] = "Không tìm thấy công việc để xóa hoặc dữ liệu đã bị xóa trước đó.";
        }
    } else {
        // Trường hợp lỗi cơ sở dữ liệu
        $_SESSION['error'] = "Lỗi hệ thống khi xóa: " . mysqli_error($conn);
    }
} else {
    $_SESSION['error'] = "ID công việc không hợp lệ!";
}

// 3. Quay trở lại trang danh sách (Nơi SweetAlert2 đang chờ sẵn)
header("Location: list.php");
exit();
