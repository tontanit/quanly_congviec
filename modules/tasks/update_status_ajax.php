<?php
require_once '../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $id = (int)$_POST['id'];
    $trang_thai_moi = mysqli_real_escape_string($conn, $_POST['trang_thai']);
    $user_id = $_SESSION['user_id'];

    // 1. Lấy trạng thái cũ để ghi log
    $sql_old = "SELECT trang_thai FROM cong_viec WHERE id = $id";
    $res_old = mysqli_query($conn, $sql_old);
    $old_data = mysqli_fetch_assoc($res_old);
    $trang_thai_cu = $old_data['trang_thai'];

    if ($trang_thai_cu !== $trang_thai_moi) {
        // 2. Cập nhật trạng thái
        $sql_update = "UPDATE cong_viec SET trang_thai = '$trang_thai_moi' WHERE id = $id";

        if (mysqli_query($conn, $sql_update)) {
            // 3. Ghi log hệ thống tự động
            $noi_dung_log = "📢 Hệ thống: Trạng thái thay đổi từ [$trang_thai_cu] thành [$trang_thai_moi] (via Quick Update).";
            $sql_log = "INSERT INTO binh_luan (cong_viec_id, user_id, noi_dung) VALUES ($id, $user_id, '$noi_dung_log')";
            mysqli_query($conn, $sql_log);

            // Trả về phản hồi thành công cho AJAX
            echo json_encode(['status' => 'success', 'new_status' => $trang_thai_moi]);
            exit;
        }
    }
}
echo json_encode(['status' => 'error']);
