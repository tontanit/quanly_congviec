<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

/**
 * 1. LOGIC PHÂN QUYỀN TRUY VẤN (SRS)
 * Nếu là 'user' hoặc 'Nguoi_thuc_hien', chỉ lấy dữ liệu của chính họ.
 */
$where_clause = "";
if ($role !== 'admin') {
    $where_clause = " WHERE nguoi_thuc_hien_id = $user_id";
}

// Thống kê số lượng công việc theo quyền truy cập
$sql_count = "SELECT 
    COUNT(*) as tong,
    SUM(CASE WHEN trang_thai = 'Chưa thực hiện' THEN 1 ELSE 0 END) as chua_lam,
    SUM(CASE WHEN trang_thai = 'Đang thực hiện' THEN 1 ELSE 0 END) as dang_lam,
    SUM(CASE WHEN trang_thai = 'Đã hoàn thành' THEN 1 ELSE 0 END) as xong,
    SUM(CASE WHEN trang_thai = 'Quá hạn' THEN 1 ELSE 0 END) as tre_han
FROM cong_viec $where_clause";

$result_count = mysqli_query($conn, $sql_count);
$counts = mysqli_fetch_assoc($result_count);

// 2. Lấy danh sách 5 công việc gần hạn (7 ngày tới) theo quyền truy cập
$sql_tasks = "SELECT cv.*, u.ho_ten as nguoi_lam 
              FROM cong_viec cv 
              LEFT JOIN users u ON cv.nguoi_thuc_hien_id = u.id 
              $where_clause
              ORDER BY cv.han_hoan_thanh ASC LIMIT 5";
$result_tasks = mysqli_query($conn, $sql_tasks);

include 'includes/header.php';
?>

<style>
    :root {
        --app-blue: #0056b3;
    }

    .stat-card {
        border: none;
        border-radius: 10px;
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .table thead th {
        color: var(--app-blue);
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold text-uppercase text-primary border-bottom pb-3">
                <i class="fa-solid fa-gauge-high me-2"></i>Bảng điều khiển hệ thống
            </h3>
        </div>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white shadow-sm">
                <div class="card-body py-4">
                    <h6 class="card-title text-uppercase opacity-75 small">Tổng công việc</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $counts['tong'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-dark shadow-sm">
                <div class="card-body py-4">
                    <h6 class="card-title text-uppercase opacity-75 small">Đang thực hiện</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $counts['dang_lam'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white shadow-sm">
                <div class="card-body py-4">
                    <h6 class="card-title text-uppercase opacity-75 small">Hoàn thành</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $counts['xong'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-danger text-white shadow-sm">
                <div class="card-body py-4">
                    <h6 class="card-title text-uppercase opacity-75 small">Quá hạn</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $counts['tre_han'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <?php if ($counts['tre_han'] > 0): ?>
        <div class="alert alert-danger shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-4 me-3"></i>
            <div>
                <strong>Chú ý:</strong> Bạn có <b><?php echo $counts['tre_han']; ?></b> công việc đã quá hạn. Vui lòng cập nhật tiến độ ngay!
            </div>
        </div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Công việc sắp đến hạn (7 ngày tới)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th class="text-center">Tên công việc</th>
                                    <th class="text-center">Người thực hiện</th>
                                    <th class="text-center">Hạn hoàn thành</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-center pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result_tasks) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result_tasks)):
                                        $is_overdue = ($row['trang_thai'] == 'Quá hạn');

                                        // Định dạng màu sắc badge
                                        $badge_class = "bg-secondary";
                                        if ($row['trang_thai'] == 'Đang thực hiện') $badge_class = "bg-primary";
                                        if ($row['trang_thai'] == 'Đã hoàn thành') $badge_class = "bg-success";
                                        if ($row['trang_thai'] == 'Quá hạn') $badge_class = "bg-danger";
                                    ?>
                                        <tr class="<?php echo $is_overdue ? 'table-danger' : ''; ?>">
                                            <td class="ps-4">
                                                <div class="ftext-lowercase"><?php echo $row['ten_cong_viec']; ?></div>
                                                <?php if ($is_overdue): ?>
                                                    <small class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i>Đã quá hạn</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="small text-muted"><?php echo $row['nguoi_lam']; ?></span>
                                            </td>
                                            <td class="text-center <?php echo $is_overdue ? 'fw-bold text-danger' : ''; ?>">
                                                <i class="fa-regular fa-calendar me-1"></i>
                                                <?php echo date('d/m/Y', strtotime($row['han_hoan_thanh'])); ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill <?php echo $badge_class; ?> px-3">
                                                    <?php echo $row['trang_thai']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <a href="modules/tasks/detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary border-0">
                                                    <i class="fa fa-eye me-1"></i>Xem
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted fst-italic">Không có công việc nào sắp đến hạn.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>