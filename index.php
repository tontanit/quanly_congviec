<?php
require_once 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

/**
 * 1. LOGIC PHÂN QUYỀN TRUY VẤN
 */
$where_clause = "";
if ($role !== 'admin') {
    $where_clause = " WHERE nguoi_thuc_hien_id = $user_id";
}

// Thống kê tổng quan
$sql_count = "SELECT 
    COUNT(*) as tong,
    SUM(CASE WHEN trang_thai = 'Chưa thực hiện' THEN 1 ELSE 0 END) as chua_lam,
    SUM(CASE WHEN trang_thai = 'Đang thực hiện' THEN 1 ELSE 0 END) as dang_lam,
    SUM(CASE WHEN trang_thai = 'Đã hoàn thành' THEN 1 ELSE 0 END) as xong,
    SUM(CASE WHEN trang_thai = 'Quá hạn' THEN 1 ELSE 0 END) as tre_han
FROM cong_viec $where_clause";

$result_count = mysqli_query($conn, $sql_count);
$counts = mysqli_fetch_assoc($result_count);

/**
 * 2. LẤY DÂN DANH SÁCH CÔNG VIỆC TRONG 7 NGÀY TỚI
 * - Chỉ lấy việc chưa hoàn thành
 * - Hạn trong khoảng 7 ngày tới (bao gồm cả việc đã quá hạn nhưng chưa xong)
 */
$sql_tasks = "SELECT cv.*, u.ho_ten as nguoi_lam 
              FROM cong_viec cv 
              LEFT JOIN users u ON cv.nguoi_thuc_hien_id = u.id 
              WHERE cv.trang_thai != 'Đã hoàn thành' 
              AND cv.han_hoan_thanh <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";

if ($role !== 'admin') {
    $sql_tasks .= " AND cv.nguoi_thuc_hien_id = $user_id";
}

$sql_tasks .= " ORDER BY cv.han_hoan_thanh ASC LIMIT 10";
$result_tasks = mysqli_query($conn, $sql_tasks);

include 'includes/header.php';
?>

<style>
    .stat-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        border-top: none;
    }

    .deadline-urgent {
        color: #dc3545;
        font-weight: 700;
    }

    .deadline-soon {
        color: #fd7e14;
        font-weight: 600;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3">
                <h3 class="fw-bold text-primary mb-0">
                    <i class="fa-solid fa-gauge-high me-2"></i>BẢNG ĐIỀU KHIỂN
                </h3>
                <span class="text-muted small italic">Cập nhật: <?php echo date('H:i d/m/Y'); ?></span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white shadow-sm">
                <div class="card-body py-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Tổng công việc</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $counts['tong'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white shadow-sm">
                <div class="card-body py-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Đang thực hiện</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $counts['dang_lam'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white shadow-sm">
                <div class="card-body py-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Hoàn thành</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $counts['xong'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-danger text-white shadow-sm">
                <div class="card-body py-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Quá hạn</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $counts['tre_han'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <?php if ($counts['tre_han'] > 0): ?>
        <div class="alert alert-danger shadow-sm d-flex align-items-center mb-4 border-0 border-start border-5 border-danger" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-4 me-3"></i>
            <div>
                <strong>Cảnh báo:</strong> Bạn đang có <b><?php echo $counts['tre_han']; ?></b> công việc bị chậm tiến độ. Hãy ưu tiên xử lý ngay!
            </div>
        </div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-calendar-check me-2 text-primary"></i>Kế hoạch 7 ngày tới
                    </h6>
                    <span class="badge bg-light text-dark border">Chỉ hiển thị việc chưa xong</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Nội dung công việc</th>
                                    <th class="text-center">Người thực hiện</th>
                                    <th class="text-center">Hạn cuối</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-center pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result_tasks) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result_tasks)):
                                        // Tính toán số ngày còn lại
                                        $deadline = strtotime($row['han_hoan_thanh']);
                                        $today = strtotime(date('Y-m-d'));
                                        $days_diff = round(($deadline - $today) / (60 * 60 * 24));

                                        $row_class = "";
                                        $deadline_class = "";

                                        if ($days_diff < 0) {
                                            $row_class = "table-danger-light"; // Tùy chọn class CSS nếu cần
                                            $deadline_text = "Trễ " . abs($days_diff) . " ngày";
                                            $deadline_class = "deadline-urgent";
                                        } elseif ($days_diff == 0) {
                                            $deadline_text = "Hôm nay!";
                                            $deadline_class = "deadline-urgent";
                                        } elseif ($days_diff <= 3) {
                                            $deadline_text = "Còn $days_diff ngày";
                                            $deadline_class = "deadline-soon";
                                        } else {
                                            $deadline_text = "Còn $days_diff ngày";
                                        }

                                        $badge_class = ($row['trang_thai'] == 'Đang thực hiện') ? 'bg-primary' : 'bg-warning text-dark';
                                        if ($row['trang_thai'] == 'Quá hạn') $badge_class = 'bg-danger';
                                    ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark"><?php echo $row['ten_cong_viec']; ?></div>
                                                <small class="text-muted d-block mt-1">Mã: #CV-<?php echo $row['id']; ?></small>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="avatar-xs me-2 bg-light rounded-circle text-primary fw-bold" style="width:30px; height:30px; line-height:30px; font-size: 10px; border: 1px solid #eee;">
                                                        <?php echo strtoupper(substr($row['nguoi_lam'], 0, 1)); ?>
                                                    </div>
                                                    <span class="small"><?php echo $row['nguoi_lam']; ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="<?php echo $deadline_class; ?>">
                                                    <?php echo date('d/m/Y', $deadline); ?>
                                                </div>
                                                <small class="d-block <?php echo $deadline_class; ?> opacity-75">
                                                    <?php echo $deadline_text; ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill <?php echo $badge_class; ?> fw-medium py-2 px-2 text-nowrap">
                                                    <?php echo $row['trang_thai']; ?>
                                                </span>
                                            </td>

                                            <td class="text-center pe-4" style="width: 1%;">
                                                <a href="modules/tasks/detail.php?id=<?php echo $row['id']; ?>"
                                                    class="badge rounded-pill border border-primary text-primary text-decoration-none fw-medium py-2 px-2 text-nowrap shadow-sm">
                                                    Chi tiết <i class="fa-solid fa-chevron-right ms-1" style="font-size: 8px;"></i>
                                                </a>
                                            </td>

                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" width="60" class="opacity-25 mb-3">
                                            <p class="text-muted fst-italic">Chúc mừng! Không có công việc nào cần xử lý gấp trong 7 ngày tới.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-center py-3">
                    <a href="modules/tasks/list.php" class="text-decoration-none small fw-bold">Xem tất cả công việc <i class="fa-solid fa-angle-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include 'includes/footer.php'; ?>