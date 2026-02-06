<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

// 1. Kiểm tra quyền truy cập (Chỉ Admin mới có quyền khôi phục)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
        exit();
    }

    // 2. Nhận ID cần khôi phục
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        // 3. Thực hiện khôi phục (Set is_deleted về 0)
        $sql = "UPDATE lich_cong_tac SET is_deleted = 0, deleted_at = NULL WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Đã khôi phục lịch công tác thành công!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi database: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID không hợp lệ.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.']);
}
