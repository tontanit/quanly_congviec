<?php
require_once '../../config/database.php';

// Kiểm tra nếu session chưa được khởi tạo thì mới start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra quyền hạn
if (!isset($_SESSION['user_id'])) {
    exit("Access Denied");
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Lấy dữ liệu từ AJAX gửi lên
$keyword = mysqli_real_escape_string($conn, $_POST['keyword'] ?? '');
$status_filter = mysqli_real_escape_string($conn, $_POST['status_filter'] ?? '');

/**
 * 2. CÁC HÀM HỖ TRỢ HIỂN THỊ
 */
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

function highlightKeyword($text, $keyword)
{
    if (empty($keyword)) return $text;
    return preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<span style="background-color: yellow; font-weight: bold;">$1</span>', $text);
}

/**
 * 3. XÂY DỰNG CÂU LỆNH SQL (TÍCH HỢP BỘ LỌC)
 */
$where = "WHERE 1=1";

// Lọc theo quyền (User chỉ thấy việc của mình)
if ($role !== 'admin') {
    $where .= " AND cv.nguoi_thuc_hien_id = $user_id";
}

// Lọc theo từ khóa (Keyword)
if (!empty($keyword)) {
    $where .= " AND (cv.ten_cong_viec LIKE '%$keyword%' OR cv.mo_ta LIKE '%$keyword%')";
}

// BỔ SUNG: Lọc theo trạng thái (Status)
if (!empty($status_filter)) {
    $where .= " AND cv.trang_thai = '$status_filter'";
}

$sql = "SELECT cv.*, u.ho_ten as nguoi_lam 
        FROM cong_viec cv 
        LEFT JOIN users u ON cv.nguoi_thuc_hien_id = u.id 
        $where 
        ORDER BY 
            CASE 
                WHEN cv.trang_thai = 'Quá hạn' THEN 1 
                WHEN cv.trang_thai = 'Đang thực hiện' THEN 2
                WHEN cv.trang_thai = 'Chưa thực hiện' THEN 3
                WHEN cv.trang_thai = 'Đã hoàn thành' THEN 4
                ELSE 5 
            END ASC, 
            cv.created_at DESC";

$result = mysqli_query($conn, $sql);

/**
 * 4. XUẤT DỮ LIỆU
 */
if (mysqli_num_rows($result) > 0) {
    $stt = 1;
    while ($row = mysqli_fetch_assoc($result)):
        $deadline = getDeadlineStatus($row['han_hoan_thanh'], $row['trang_thai']);
        $is_overdue = ($row['trang_thai'] == 'Quá hạn');
        $badge_color = "bg-light text-dark border";
        if ($row['trang_thai'] == 'Đang thực hiện') $badge_color = "bg-primary text-white";
        if ($row['trang_thai'] == 'Đã hoàn thành') $badge_color = "bg-success text-white";
        if ($is_overdue) $badge_color = "bg-danger text-white";
?>
        <tr class="<?= $is_overdue ? 'bg-priority' : '' ?>">
            <td class="text-center text-muted fw-bold"><?= $stt++ ?></td>
            <td class="px-3">
                <div class="fw-bold <?= $is_overdue ? 'text-danger' : 'text-dark' ?>">
                    <?= highlightKeyword(htmlspecialchars($row['ten_cong_viec']), $keyword) ?>
                    <?php if ($is_overdue): ?> <i class="fa fa-exclamation-triangle ms-1"></i> <?php endif; ?>
                </div>
                <div class="small text-muted text-truncate" style="max-width: 350px;">
                    <?php
                    $mo_ta_clean = strip_tags($row['mo_ta']);
                    $mo_ta_short = mb_substr($mo_ta_clean, 0, 80) . '...';
                    echo highlightKeyword($mo_ta_short, $keyword);
                    ?>
                </div>
            </td>
            <td class="text-center">
                <span class="small text-secondary">
                    <i class="fa-regular fa-user me-1 text-primary"></i><?= htmlspecialchars($row['nguoi_lam']) ?>
                </span>
            </td>
            <td class="text-center">
                <div class="<?= $deadline['class'] ?> small mb-1">
                    <i class="fa-regular fa-calendar me-1"></i><?= date('d/m/Y', strtotime($row['han_hoan_thanh'])) ?>
                </div>
                <?php if ($deadline['alert']): ?>
                    <span class="badge bg-<?= $deadline['alert'] ?> animate-pulse shadow-sm" style="font-size: 0.65rem;">
                        <?= $deadline['text'] ?>
                    </span>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <span class="badge <?= $badge_color ?> rounded-pill px-3 py-2" style="font-size: 0.75rem;">
                    <?= $row['trang_thai'] ?>
                </span>
            </td>
            <td class="text-center pe-4">
                <div class="btn-group shadow-sm">
                    <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></a>
                    <?php if ($role === 'admin'): ?>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fa fa-edit"></i></a>
                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xác nhận xóa?')"><i class="fa fa-trash"></i></a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
<?php endwhile;
} else {
    echo '<tr><td colspan="6" class="text-center py-5 text-muted fst-italic">Không tìm thấy công việc nào.</td></tr>';
}
?>