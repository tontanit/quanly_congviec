<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'clear_all') {
        // Xóa toàn bộ các bản ghi đã được đánh dấu là xóa mềm
        $sql = "DELETE FROM lich_cong_tac WHERE is_deleted = 1";
        $msg = "Đã dọn sạch thùng rác vĩnh viễn!";
    } elseif ($id > 0) {
        // Chỉ xóa một bản ghi cụ thể vĩnh viễn
        $sql = "DELETE FROM lich_cong_tac WHERE id = $id AND is_deleted = 1";
        $msg = "Đã xóa vĩnh viễn bản ghi này!";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.']);
        exit();
    }

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success', 'message' => $msg]);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}
