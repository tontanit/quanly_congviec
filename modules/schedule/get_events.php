<?php
require_once '../../config/database.php';

// Thiết lập header trả về định dạng JSON
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

// Lấy tham số start và end do FullCalendar tự động gửi lên (dạng ISO 8601)
$start = $_GET['start'];
$end = $_GET['end'];

// Truy vấn dữ liệu lịch công tác kết hợp với tên lãnh đạo
$sql = "SELECT l.*, u.ho_ten 
        FROM lich_cong_tac l
        JOIN users u ON l.lanh_dao_id = u.id
        WHERE l.is_deleted = 0  -- CHỈ LẤY CÁC LỊCH CHƯA BỊ XÓA
        AND l.bat_dau >= '$start' AND l.ket_thuc <= '$end'";

$res = mysqli_query($conn, $sql);
$events = [];

while ($row = mysqli_fetch_assoc($res)) {
    // Định nghĩa màu sắc dựa trên loại lịch
    $color = '#0d6efd'; // Mặc định: Xanh dương (Họp)
    if ($row['loai_lich'] == 'Công tác') $color = '#198754'; // Xanh lá
    if ($row['loai_lich'] == 'Tiếp khách') $color = '#fd7e14'; // Cam
    if ($row['loai_lich'] == 'Khác') $color = '#6c757d'; // Xám

    $events[] = [
        'id'              => $row['id'],
        'title'           => "[" . $row['ho_ten'] . "] " . $row['tieu_de'],
        'start'           => $row['bat_dau'],
        'end'             => $row['ket_thuc'],
        'backgroundColor' => $color,
        'borderColor'     => $color,
        // Các thuộc tính mở rộng để hiển thị trong Popup
        'extendedProps'   => [
            'location'    => $row['dia_diem'],
            'description' => $row['noi_dung'],
            'leader'      => $row['ho_ten'],
            'type'        => $row['loai_lich']
        ]
    ];
}

echo json_encode($events);
