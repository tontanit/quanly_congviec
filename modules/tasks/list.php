<?php
require_once '../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

/**
 * 1. HÀM HỖ TRỢ LOGIC DEADLINE
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
$page = isset($_GET['page']) ? max(1, (int)$GET['page']) : 1;
$offset = ($page - 1) * $limit;

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

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
 * 3. TRUY VẤN THỐNG KÊ (WIDGETS)
 */
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN trang_thai = 'Chưa thực hiện' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN trang_thai = 'Đang thực hiện' THEN 1 ELSE 0 END) as doing,
    SUM(CASE WHEN trang_thai = 'Đã hoàn thành' THEN 1 ELSE 0 END) as done,
    SUM(CASE WHEN trang_thai = 'Quá hạn' THEN 1 ELSE 0 END) as overdue
FROM cong_viec cv $where";
$res_stats = mysqli_query($conn, $sql_stats);
$stats = mysqli_fetch_assoc($res_stats);

/**
 * 4. TRUY VẤN DANH SÁCH
 */
$sql_count = "SELECT COUNT(*) as total FROM cong_viec cv $where";
$res_count = mysqli_query($conn, $sql_count);
$total_rows = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_rows / $limit);

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
    }

    body {
        background-color: #f4f7f6;
        font-family: 'Segoe UI', sans-serif;
    }

    .card-stats {
        transition: transform 0.2s;
        border: none;
    }

    .card-stats:hover {
        transform: translateY(-5px);
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

    #loading-bar {
        height: 3px;
        width: 0;
        background: var(--app-blue);
        transition: 0.4s;
        position: relative;
        top: 0;
    }
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

    <div class="row g-3 mb-4" id="stats-container">
        <div class="col-md-3">
            <div class="card card-stats shadow-sm bg-white border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Tổng số</div>
                    <h3 class="fw-bold mb-0 text-primary"><?= $stats['total'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats shadow-sm bg-white border-start border-info border-4">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Đang làm</div>
                    <h3 class="fw-bold mb-0 text-info"><?= $stats['doing'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats shadow-sm bg-white border-start border-success border-4">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Hoàn thành</div>
                    <h3 class="fw-bold mb-0 text-success"><?= $stats['done'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats shadow-sm bg-white border-start border-danger border-4">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Quá hạn</div>
                    <h3 class="fw-bold mb-0 text-danger"><?= $stats['overdue'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" id="search-input" name="keyword" class="form-control border-start-0"
                            placeholder="Tìm tên hoặc mô tả..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" id="status-filter" class="form-select">
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
        <div id="loading-bar"></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
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
                                    <?= htmlspecialchars($row['ten_cong_viec']) ?>
                                    <?php if ($is_overdue): ?> <i class="fa fa-exclamation-triangle ms-1"></i> <?php endif; ?>
                                </div>
                                <div class="small text-muted text-truncate" style="max-width: 350px;">
                                    <?= mb_substr(strip_tags($row['mo_ta']), 0, 80) ?>...
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="small text-secondary">
                                    <i class="fa-regular fa-user me-1 text-primary"></i><?= htmlspecialchars($row['nguoi_lam']) ?>
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
                                        <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Định nghĩa Toast thông báo dùng chung
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    $(document).ready(function() {
        var searchTimer;

        // Thông báo nếu có session success
        <?php if (isset($_SESSION['success'])): ?>
            Toast.fire({
                icon: 'success',
                title: '<?= $_SESSION['success'] ?>'
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        function fetchData() {
            var keyword = $('#search-input').val();
            var status = $('#status-filter').val();

            $('#loading-bar').css('width', '30%');
            $('tbody').css('opacity', '0.5');

            $.ajax({
                url: 'search_ajax.php',
                type: 'POST',
                data: {
                    keyword: keyword,
                    status_filter: status
                },
                success: function(data) {
                    $('#loading-bar').css('width', '100%');
                    $('tbody').html(data);
                    $('tbody').animate({
                        opacity: 1
                    }, 200);
                    setTimeout(() => {
                        $('#loading-bar').css('width', '0');
                    }, 500);
                }
            });
        }

        $('#search-input').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(fetchData, 300);
        });

        $('#status-filter').on('change', fetchData);
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            text: "Dữ liệu sẽ không thể khôi phục!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Xóa ngay',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'delete.php?id=' + id;
            }
        })
    }
</script>

<?php include '../../includes/footer.php'; ?>