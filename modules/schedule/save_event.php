<?php
require_once '../../config/database.php';

// Chỉ cho phép truy cập qua POST và phải có quyền admin/thư ký
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {

    // 1. Kiểm tra quyền hạn (Chỉ admin mới được thêm/sửa)
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
        exit();
    }

    // 2. Nhận và làm sạch dữ liệu
    $event_id  = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $tieu_de   = mysqli_real_escape_string($conn, trim($_POST['tieu_de']));
    $bat_dau   = mysqli_real_escape_string($conn, $_POST['bat_dau']);
    $ket_thuc  = mysqli_real_escape_string($conn, $_POST['ket_thuc']);
    $dia_diem  = mysqli_real_escape_string($conn, trim($_POST['dia_diem']));
    $loai_lich = mysqli_real_escape_string($conn, $_POST['loai_lich']);
    $noi_dung  = mysqli_real_escape_string($conn, trim($_POST['noi_dung']));

    // Mặc định lãnh đạo là người đang đăng nhập (hoặc bạn có thể mở rộng chọn lanh_dao_id từ form)
    $lanh_dao_id = $_SESSION['user_id'];

    // 3. Kiểm tra logic thời gian cơ bản
    if (strtotime($bat_dau) >= strtotime($ket_thuc)) {
        echo json_encode(['status' => 'error', 'message' => 'Thời gian bắt đầu phải trước thời gian kết thúc!']);
        exit();
    }

    // 4. Xử lý logic SQL (Cập nhật hoặc Thêm mới)
    if ($event_id > 0) {
        // TRƯỜNG HỢP: CẬP NHẬT (UPDATE)
        $sql = "UPDATE lich_cong_tac SET 
                tieu_de = '$tieu_de', 
                bat_dau = '$bat_dau', 
                ket_thuc = '$ket_thuc', 
                dia_diem = '$dia_diem', 
                loai_lich = '$loai_lich', 
                noi_dung = '$noi_dung' 
                WHERE id = $event_id";
        $message = "Cập nhật lịch thành công!";
    } else {
        // TRƯỜNG HỢP: THÊM MỚI (INSERT)
        $sql = "INSERT INTO lich_cong_tac (tieu_de, lanh_dao_id, bat_dau, ket_thuc, dia_diem, noi_dung, loai_lich, trang_thai) 
                VALUES ('$tieu_de', $lanh_dao_id, '$bat_dau', '$ket_thuc', '$dia_diem', '$noi_dung', '$loai_lich', 'Chính thức')";
        $message = "Thêm lịch công tác mới thành công!";
    }

    // 5. Thực thi và phản hồi
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi database: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.']);
}
