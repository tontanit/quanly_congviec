<?php
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Nhận bộ lọc từ URL (nếu có)
$tu_ngay = $_GET['tu_ngay'] ?? '';
$den_ngay = $_GET['den_ngay'] ?? '';

// 1. Xây dựng câu lệnh SQL có điều kiện
$where = ($role !== 'admin') ? " WHERE cv.nguoi_thuc_hien_id = $user_id" : " WHERE 1=1";

if ($tu_ngay && $den_ngay) {
    $where .= " AND cv.han_hoan_thanh BETWEEN '$tu_ngay' AND '$den_ngay'";
}

$sql = "SELECT cv.*, u1.ho_ten as nguoi_giao, u2.ho_ten as nguoi_lam
        FROM cong_viec cv
        JOIN users u1 ON cv.nguoi_giao_id = u1.id
        JOIN users u2 ON cv.nguoi_thuc_hien_id = u2.id
        $where ORDER BY cv.id DESC";

$result = mysqli_query($conn, $sql);

// 2. Thiết lập Header Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Bao_cao_" . date('d-m-Y') . ".xls");

echo '<table border="1">';
echo '<tr style="background-color: #f2f2f2; font-weight: bold;">
        <th>STT</th>
        <th>Ten cong viec</th>
        <th>Nguoi thuc hien</th>
        <th>Han chot</th>
        <th>Trang thai</th>
      </tr>';

$stt = 1;
while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $stt++ . '</td>';
    // Ép chữ thường cho tên công việc theo yêu cầu
    echo '<td>' . mb_strtolower($row['ten_cong_viec'], 'UTF-8') . '</td>';
    echo '<td>' . $row['nguoi_lam'] . '</td>';
    echo '<td>' . date('d/m/Y', strtotime($row['han_hoan_thanh'])) . '</td>';
    echo '<td>' . $row['trang_thai'] . '</td>';
    echo '</tr>';
}
echo '</table>';
