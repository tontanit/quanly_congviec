<?php
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Hết phiên làm việc, vui lòng đăng nhập lại.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $ho_ten = $_SESSION['ho_ten']; // Lấy tên để hiển thị ngay
    $cong_viec_id = (int)$_POST['cong_viec_id'];
    $noi_dung = mysqli_real_escape_string($conn, trim($_POST['noi_dung']));

    if (!empty($noi_dung) && $cong_viec_id > 0) {
        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO binh_luan (cong_viec_id, user_id, noi_dung, created_at) 
                VALUES ($cong_viec_id, $user_id, '$noi_dung', '$now')";

        if (mysqli_query($conn, $sql)) {
            // Trả về dữ liệu vừa thêm để JS vẽ lên màn hình ngay
            echo json_encode([
                'status' => 'success',
                'ho_ten' => $ho_ten,
                'noi_dung' => nl2br(htmlspecialchars($noi_dung)),
                'thoi_gian' => date('H:i - d/m/Y', strtotime($now))
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi DB: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nội dung không được để trống!']);
    }
    exit();
}
