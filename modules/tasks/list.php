<?php
require_once '../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

/**
 * 1. HÀM HỖ TRỢ LOGIC DEADLINE (SRS 48H)
 */
function getDeadlineStatus($deadline_str, $trang_thai)
{
    if ($trang_thai === 'Đã hoàn thành') {
        return ['text' => 'Hoàn thành', 'class' => 'text-success', 'alert' => false];
    }

    $deadline = strtotime($deadline_str);
    $now = time();
    $diff = $deadline - $now;

    if ($diff < 0) {
        return ['text' => 'Quá hạn', 'class' => 'text-danger fw-bold', 'alert' => 'danger'];
    }
    if ($diff <= 172800) { // 48 giờ
        return ['text' => 'Sắp hết hạn (<48h)', 'class' => 'text-warning fw-bold', 'alert' => 'warning'];
    }
    return ['text' => 'Đang tiến hành', 'class' => 'text-primary', 'alert' => false];
}

/**
 * 2. CẤU HÌNH PHÂN TRANG & BỘ LỌC
 */
$limit = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Khởi tạo điều kiện lọc
$where = "WHERE 1=1";

if ($role !== 'admin') {
    $where .= " AND cv.nguoi_thuc_hien_id = $user_id";
}

$keyword = $_GET['keyword'] ?? '';
if (!empty($keyword)) {
    $k = mysqli_real_escape_string($conn, $keyword);
    $where .= " AND (cv.ten_cong_viec LIKE '%$k%' OR cv.mo_ta LIKE '%$k%')";
}

$status_filter = $_GET['status'] ?? '';
if (!empty($status_filter)) {
    $s = mysqli_real_escape_string($conn, $status_filter);
    $where .= " AND cv.trang_thai = '$s'";
}

/**
 * 3. TRUY VẤN DỮ LIỆU VỚI LOGIC SẮP XẾP ƯU TIÊN
 */
// A. Đếm tổng số bản ghi
$sql_count = "SELECT COUNT(*) as total FROM cong_viec cv $where";
$res_count = mysqli_query($conn, $sql_count);
$total_rows = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_rows / $limit);

// B. Lấy dữ liệu: Sắp xếp Quá hạn lên đầu, sau đó mới đến ngày tạo mới nhất
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
            cv.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

include '../../includes/header.php';
?>

<style>
    :root {
        --app-blue: #0056b3;
        --app-light-blue: #f8fbff;
    }

    body {
        background-color: #f4f7f6;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    .table thead th {
        background-color: #ffffff;
        color: var(--app-blue) !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        border-bottom: 2px solid var(--app-blue) !important;
    }

    .animate-pulse {
        animation: pulse-soft 2s infinite;
    }

    @keyframes pulse-soft {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .table tbody tr:hover {
        background-color: var(--app-light-blue);
        transition: 0.2s;
    }

    .page-item.active .page-link {
        background-color: var(--app-blue);
        border-color: var(--app-blue);
    }

    .bg-priority {
        background-color: #fff5f5;
    }

    /* Màu nền nhẹ cho việc quá hạn */
</style>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0 text-primary text-uppercase">
            <i class="fa-solid fa-list-check me-2"></i>Quản lý công việc
        </h3>
        <?php if ($role === 'admin'): ?>
            <a href="add.php" class="btn btn-primary shadow-sm px-4">
                <i class="fa fa-plus-circle me-2"></i>Thêm việc mới
            </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" name="keyword" class="form-control border-start-0" placeholder="Tìm tên hoặc mô tả..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <?php
                        $opts = ['Chưa thực hiện', 'Đang thực hiện', 'Đã hoàn thành', 'Quá hạn'];
                        foreach ($opts as $o) {
                            $selected = ($status_filter == $o) ? 'selected' : '';
                            echo "<option value='$o' $selected>$o</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">LỌC DỮ LIỆU</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center py-3" style="width: 60px;">STT</th>
                        <th class="py-3">Nội dung công việc</th>
                        <th class="text-center py-3">Phụ trách</th>
                        <th class="text-center py-3">Hạn hoàn thành</th>
                        <th class="text-center py-3">Trạng thái</th>
                        <th class="text-center py-3 pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php
                    $stt = $offset + 1;
                    while ($row = mysqli_fetch_assoc($result)):
                        $deadline = getDeadlineStatus($row['han_hoan_thanh'], $row['trang_thai']);

                        // Màu sắc trạng thái
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
                                    <?= $row['ten_cong_viec'] ?>
                                    <?php if ($is_overdue): ?> <i class="fa fa-exclamation-triangle ms-1"></i> <?php endif; ?>
                                </div>
                                <div class="small text-muted text-truncate" style="max-width: 350px;">
                                    <?= mb_substr(strip_tags($row['mo_ta']), 0, 80) ?>...
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="small text-secondary">
                                    <i class="fa-regular fa-user me-1 text-primary"></i><?= $row['nguoi_lam'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="<?= $deadline['class'] ?> small mb-1">
                                    <i class="fa-regular fa-calendar me-1"></i>
                                    <?= date('d/m/Y', strtotime($row['han_hoan_thanh'])) ?>
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
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted fst-italic">Không có công việc nào thỏa mãn bộ lọc.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php $query_params = $_GET; ?>

                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <?php $query_params['page'] = $page - 1; ?>
                    <a class="page-link" href="?<?= http_build_query($query_params) ?>">Trước</a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php $query_params['page'] = $i; ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query($query_params) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <?php $query_params['page'] = $page + 1; ?>
                    <a class="page-link" href="?<?= http_build_query($query_params) ?>">Sau</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>