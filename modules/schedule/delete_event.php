<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Không có quyền!']);
        exit();
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        // SOFT DELETE: Chỉ cập nhật trạng thái
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE lich_cong_tac SET is_deleted = 1, deleted_at = '$now' WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Lịch đã được đưa vào thùng rác.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    exit();
}
