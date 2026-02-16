<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Phiên đăng nhập hết hạn.']);
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
    exit();
}

/* =========================
   1. LẤY & KIỂM TRA DỮ LIỆU
========================= */

$event_id  = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
$tieu_de   = trim($_POST['tieu_de'] ?? '');
$bat_dau   = $_POST['bat_dau'] ?? '';
$ket_thuc  = $_POST['ket_thuc'] ?? '';
$dia_diem  = trim($_POST['dia_diem'] ?? '');
$loai_lich = $_POST['loai_lich'] ?? '';
$noi_dung  = trim($_POST['noi_dung'] ?? '');

$lanh_dao_id = $_SESSION['user_id'];

/* Validate required */
if (!$tieu_de || !$bat_dau || !$ket_thuc || !$dia_diem) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc.']);
    exit();
}

/* Validate datetime */
if (!strtotime($bat_dau) || !strtotime($ket_thuc)) {
    echo json_encode(['status' => 'error', 'message' => 'Định dạng thời gian không hợp lệ.']);
    exit();
}

if (strtotime($bat_dau) >= strtotime($ket_thuc)) {
    echo json_encode(['status' => 'error', 'message' => 'Thời gian bắt đầu phải trước thời gian kết thúc.']);
    exit();
}

try {

    if ($event_id > 0) {

        /* =========================
           UPDATE
        ========================== */

        // Kiểm tra tồn tại
        $check = $conn->prepare("SELECT id FROM lich_cong_tac WHERE id = ?");
        $check->bind_param("i", $event_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Lịch không tồn tại.']);
            exit();
        }

        $stmt = $conn->prepare("
            UPDATE lich_cong_tac 
            SET tieu_de = ?, 
                bat_dau = ?, 
                ket_thuc = ?, 
                dia_diem = ?, 
                loai_lich = ?, 
                noi_dung = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssssssi",
            $tieu_de,
            $bat_dau,
            $ket_thuc,
            $dia_diem,
            $loai_lich,
            $noi_dung,
            $event_id
        );

        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Cập nhật lịch thành công!'
        ]);
    } else {

        /* =========================
           INSERT
        ========================== */

        $stmt = $conn->prepare("
            INSERT INTO lich_cong_tac 
            (tieu_de, lanh_dao_id, bat_dau, ket_thuc, dia_diem, noi_dung, loai_lich, trang_thai)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Chính thức')
        ");

        $stmt->bind_param(
            "sisssss",
            $tieu_de,
            $lanh_dao_id,
            $bat_dau,
            $ket_thuc,
            $dia_diem,
            $noi_dung,
            $loai_lich
        );

        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Thêm lịch công tác mới thành công!'
        ]);
    }
} catch (Exception $e) {

    // Không lộ lỗi SQL ra ngoài
    echo json_encode([
        'status' => 'error',
        'message' => 'Có lỗi xảy ra trong quá trình xử lý.'
    ]);
}
