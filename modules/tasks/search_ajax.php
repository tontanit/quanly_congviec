<?php
session_start();
require_once '../../config/database.php';

// Copy hàm getDeadlineStatus từ list.php để đồng bộ
function getDeadlineStatus($deadline_str, $trang_thai)
{
    if ($trang_thai === 'Đã hoàn thành') return ['text' => 'Hoàn thành', 'class' => 'text-success', 'alert' => false];
    $deadline = strtotime($deadline_str);
    $now = time();
    $diff = $deadline - $now;
    if ($diff < 0) return ['text' => 'Quá hạn', 'class' => 'text-danger fw-bold', 'alert' => 'danger'];
    if ($diff <= 172800) return ['text' => 'Sắp hết hạn (<48h)', 'class' => 'text-warning fw-bold', 'alert' => 'warning'];
    return ['text' => 'Đang tiến hành', 'class' => 'text-primary', 'alert' => false];
}

$limit = 15;
$page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
$offset = ($page - 1) * $limit;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$where = "WHERE 1=1";
if ($role !== 'admin') $where .= " AND cv.nguoi_thuc_hien_id = $user_id";

if (!empty($_POST['keyword'])) {
    $k = mysqli_real_escape_string($conn, $_POST['keyword']);
    $where .= " AND (cv.ten_cong_viec LIKE '%$k%' OR cv.mo_ta LIKE '%$k%')";
}
if (!empty($_POST['status_filter'])) {
    $s = mysqli_real_escape_string($conn, $_POST['status_filter']);
    $where .= " AND cv.trang_thai = '$s'";
}

// 1. Tính toán phân trang
$sql_count = "SELECT COUNT(*) as total FROM cong_viec cv $where";
$res_count = mysqli_query($conn, $sql_count);
$total_rows = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_rows / $limit);

// 2. Truy vấn dữ liệu trang hiện tại
$sql = "SELECT cv.*, u.ho_ten as nguoi_lam,
            CASE 
                WHEN cv.trang_thai != 'Đã hoàn thành' AND cv.han_hoan_thanh < CURDATE() THEN 'Quá hạn'
                ELSE cv.trang_thai
            END as trang_thai_hien_thi
        FROM cong_viec cv 
        LEFT JOIN users u ON cv.nguoi_thuc_hien_id = u.id 
        $where 
        ORDER BY 
            CASE 
                WHEN cv.trang_thai != 'Đã hoàn thành' AND cv.han_hoan_thanh < CURDATE() THEN 1
                WHEN cv.trang_thai = 'Chưa thực hiện' THEN 2
                WHEN cv.trang_thai = 'Đang thực hiện' THEN 3
                WHEN cv.trang_thai = 'Đã hoàn thành' THEN 4
                ELSE 5
            END ASC, cv.han_hoan_thanh ASC
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

// 3. Tạo HTML cho bảng dữ liệu
$table_html = "";
$stt = $offset + 1;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $deadline = getDeadlineStatus($row['han_hoan_thanh'], $row['trang_thai_hien_thi']);
        $is_overdue = ($row['trang_thai_hien_thi'] == 'Quá hạn');
        $is_done = ($row['trang_thai_hien_thi'] == 'Đã hoàn thành');
        $badge_color = $is_done ? "bg-success text-white" : ($is_overdue ? "bg-danger text-white" : ($row['trang_thai_hien_thi'] == 'Đang thực hiện' ? "bg-primary text-white" : "bg-light text-dark border"));

        $table_html .= "<tr class='" . ($is_overdue ? 'bg-priority' : '') . "'>
            <td class='text-center text-muted fw-bold'>$stt</td>
            <td class='px-3'>
                <div class='" . ($is_done ? 'fw-normal' : 'fw-bold') . " " . ($is_overdue ? 'text-danger' : 'text-dark') . "'>
                    " . htmlspecialchars($row['ten_cong_viec']) . " " . ($is_overdue ? '<i class="fa fa-exclamation-triangle ms-1"></i>' : '') . "
                </div>
                <div class='small text-muted text-truncate' style='max-width: 350px;'>" . mb_substr(strip_tags($row['mo_ta']), 0, 120) . "...</div>
            </td>
            <td class='text-center'><span class='small text-secondary'><i class='fa-regular fa-user me-1 text-primary'></i>" . htmlspecialchars($row['nguoi_lam']) . "</span></td>
            <td class='text-center'>
                <div class='{$deadline['class']} small mb-1'><i class='fa-regular fa-calendar me-1'></i>" . date('d/m/Y', strtotime($row['han_hoan_thanh'])) . "</div>
                " . ($deadline['alert'] ? "<span class='badge bg-{$deadline['alert']} animate-pulse shadow-sm' style='font-size: 0.65rem;'>{$deadline['text']}</span>" : "") . "
            </td>
            <td class='text-center'><span class='badge $badge_color rounded-pill px-3 py-2' style='font-size: 0.75rem;'>{$row['trang_thai_hien_thi']}</span></td>
            <td class='text-center pe-4'>
                <div class='btn-group shadow-sm'>
                    <a href='detail.php?id={$row['id']}' class='btn btn-sm btn-outline-primary'><i class='fa fa-eye'></i></a>";
        if ($role === 'admin') {
            $table_html .= "<a href='edit.php?id={$row['id']}' class='btn btn-sm btn-outline-warning'><i class='fa fa-edit'></i></a>
                            <a href='javascript:void(0)' onclick='confirmDelete({$row['id']})' class='btn btn-sm btn-outline-danger'><i class='fa fa-trash'></i></a>";
        }
        $table_html .= "</div></td></tr>";
        $stt++;
    }
} else {
    $table_html = "<tr><td colspan='6' class='text-center py-5 text-muted'>Không tìm thấy công việc nào.</td></tr>";
}

// 4. Tạo HTML cho thanh phân trang
$pagination_html = "";
if ($total_pages > 0) {
    $pagination_html .= "<div class='card-footer bg-white border-top-0 py-3'>
        <div class='d-flex justify-content-between align-items-center flex-wrap'>
            <div class='text-muted small'>Hiển thị từ " . (min($offset + 1, $total_rows)) . " đến " . (min($offset + $limit, $total_rows)) . " trên tổng số $total_rows công việc</div>";

    if ($total_pages > 1) {
        $pagination_html .= "<nav><ul class='pagination pagination-sm mb-0 shadow-sm'>";
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = ($i == $page) ? 'active' : '';
            $pagination_html .= "<li class='page-item $active'><a class='page-link px-3' href='javascript:void(0)' onclick='fetchData($i)'>$i</a></li>";
        }
        $pagination_html .= "</ul></nav>";
    }
    $pagination_html .= "</div></div>";
}

// Trả về JSON
header('Content-Type: application/json');
echo json_encode(['table' => $table_html, 'pagination' => $pagination_html]);
