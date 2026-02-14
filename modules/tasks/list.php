<?php
session_start();
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
 * 2. CẤU HÌNH BỘ LỌC CƠ BẢN
 */
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$where = "WHERE 1=1";
if ($role !== 'admin') {
    $where .= " AND cv.nguoi_thuc_hien_id = $user_id";
}

$keyword = $_GET['keyword'] ?? '';
$status_filter = $_GET['status'] ?? '';

/**
 * 3. TRUY VẤN THỐNG KÊ (WIDGETS) - Luôn hiển thị tổng quát
 */
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN cv.trang_thai = 'Chưa thực hiện' THEN 1 ELSE 0 END) as waiting,
    SUM(CASE WHEN cv.trang_thai = 'Đang thực hiện' AND cv.han_hoan_thanh >= CURDATE() THEN 1 ELSE 0 END) as doing,
    SUM(CASE WHEN cv.trang_thai = 'Đã hoàn thành' THEN 1 ELSE 0 END) as done,
    SUM(CASE WHEN cv.trang_thai != 'Đã hoàn thành' AND cv.han_hoan_thanh < CURDATE() THEN 1 ELSE 0 END) as overdue
FROM cong_viec cv $where";

$res_stats = mysqli_query($conn, $sql_stats);
$stats = mysqli_fetch_assoc($res_stats);

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
        will-change: transform;
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
        position: absolute;
        /* Thay đổi để nằm sát mép trên card */
        top: 0;
        left: 0;
        z-index: 10;
    }

    .bg-priority {
        background-color: #fff5f5 !important;
        /* Đã sửa từ ! femin */
    }

    /* Bổ sung cho tính năng tìm kiếm */
    .highlight {
        background-color: #ffeb3b;
        color: #000;
        padding: 0 2px;
        border-radius: 2px;
        font-weight: bold;
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
        <div class="col">
            <div class="card card-stats shadow-sm bg-white border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Tổng số</div>
                    <h3 class="fw-bold mb-0 text-primary" id="stat-total"><?= $stats['total'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card card-stats shadow-sm bg-white border-start border-secondary border-4">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Chưa thực hiện</div>
                    <h3 class="fw-bold mb-0 text-secondary" id="stat-waiting"><?= $stats['waiting'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card card-stats shadow-sm bg-white border-start border-info border-4">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Đang làm</div>
                    <h3 class="fw-bold mb-0 text-info" id="stat-doing"><?= $stats['doing'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card card-stats shadow-sm bg-white border-start border-success border-4">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Hoàn thành</div>
                    <h3 class="fw-bold mb-0 text-success" id="stat-done"><?= $stats['done'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card card-stats shadow-sm bg-white border-start border-danger border-4">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Quá hạn</div>
                    <h3 class="fw-bold mb-0 text-danger" id="stat-overdue"><?= $stats['overdue'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form id="filter-form" class="row g-2">
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
                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                        <i class="fa fa-filter me-2"></i>LỌC DỮ LIỆU
                    </button>
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
                <tbody id="data-table-body" class="bg-white">
                </tbody>
            </table>
        </div>
        <div id="pagination-container">
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var searchTimer;

        // Hàm gọi AJAX chính
        function fetchData(page = 1) {
            var keyword = $('#search-input').val();
            var status = $('#status-filter').val();

            $('#loading-bar').css('width', '30%');
            $('tbody').css('opacity', '0.5');

            $.ajax({
                url: 'search_ajax.php',
                type: 'POST',
                data: {
                    keyword: keyword,
                    status_filter: status,
                    page: page
                },
                dataType: 'json',
                success: function(res) {
                    $('#loading-bar').css('width', '100%');

                    // Cập nhật bảng và phân trang
                    $('#data-table-body').html(res.table);
                    $('#pagination-container').html(res.pagination);

                    $('tbody').animate({
                        opacity: 1
                    }, 200);
                    setTimeout(() => {
                        $('#loading-bar').css('width', '0');
                    }, 500);
                },
                error: function() {
                    $('#loading-bar').css('width', '0');
                    $('tbody').css('opacity', '1');
                }
            });
        }

        // BẮT SỰ KIỆN SUBMIT FORM (Nhấn nút Lọc hoặc phím Enter)
        $('#filter-form').on('submit', function(e) {
            e.preventDefault(); // Chặn load lại trang
            fetchData(1); // Thực hiện lọc từ trang 1
        });

        // Tự động lọc khi gõ phím (Live search sau 400ms)
        $('#search-input').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => fetchData(1), 400);
        });

        // Tự động lọc khi thay đổi trạng thái trong Dropdown
        $('#status-filter').on('change', () => fetchData(1));

        // Xuất hàm ra phạm vi global để nút phân trang có thể gọi
        window.fetchData = fetchData;

        // Tải dữ liệu lần đầu khi vào trang
        fetchData(1);
    });

    // Hàm xác nhận xóa công việc
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