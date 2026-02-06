<?php
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 1. LẤY THÔNG TIN CHI TIẾT
$sql = "SELECT cv.*, u1.ho_ten as nguoi_giao, u2.ho_ten as nguoi_lam 
        FROM cong_viec cv 
        JOIN users u1 ON cv.nguoi_giao_id = u1.id 
        JOIN users u2 ON cv.nguoi_thuc_hien_id = u2.id 
        WHERE cv.id = $id";
$res = mysqli_query($conn, $sql);
$task = mysqli_fetch_assoc($res);

if (!$task || ($role !== 'admin' && $task['nguoi_thuc_hien_id'] != $user_id)) {
    die("<div class='container mt-5 alert alert-danger shadow rounded-4'>Bạn không có quyền truy cập nhiệm vụ này.</div>");
}

// 2. XỬ LÝ XÓA FILE
if (isset($_GET['delete_file'])) {
    $file_id = (int)$_GET['delete_file'];
    $res_f = mysqli_query($conn, "SELECT duong_dan, ten_file FROM file_uploads WHERE id = $file_id AND cong_viec_id = $id");
    $file_data = mysqli_fetch_assoc($res_f);

    if ($file_data) {
        $physical_path = "../../" . $file_data['duong_dan'];
        if (file_exists($physical_path)) unlink($physical_path);
        mysqli_query($conn, "DELETE FROM file_uploads WHERE id = $file_id");

        // Log hệ thống
        $log_msg = "📢 Hệ thống: Đã xóa tệp đính kèm [" . $file_data['ten_file'] . "]";
        mysqli_query($conn, "INSERT INTO binh_luan (cong_viec_id, user_id, noi_dung) VALUES ($id, $user_id, '$log_msg')");

        $_SESSION['success'] = "Đã xóa tệp thành công!";
        header("Location: detail.php?id=$id");
        exit();
    }
}

// 3. XỬ LÝ UPLOAD FILE
if (isset($_POST['upload_file'])) {
    $target_dir = "../../assets/uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_ext = pathinfo($_FILES["file_san_pham"]["name"], PATHINFO_EXTENSION);
    $file_name = time() . "_" . uniqid() . "." . $file_ext;
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["file_san_pham"]["tmp_name"], $target_file)) {
        $clean_name = mysqli_real_escape_string($conn, $_FILES["file_san_pham"]["name"]);
        $path_save = "assets/uploads/" . $file_name;
        mysqli_query($conn, "INSERT INTO file_uploads (cong_viec_id, ten_file, duong_dan) VALUES ($id, '$clean_name', '$path_save')");

        $log_msg = "📢 Hệ thống: Tải lên minh chứng [" . $clean_name . "]";
        mysqli_query($conn, "INSERT INTO binh_luan (cong_viec_id, user_id, noi_dung) VALUES ($id, $user_id, '$log_msg')");

        $_SESSION['success'] = "Tải lên thành công!";
        header("Location: detail.php?id=$id");
        exit();
    }
}

// 4. XỬ LÝ CẬP NHẬT TRẠNG THÁI (PHP)
if (isset($_POST['update_status'])) {
    $status_moi = mysqli_real_escape_string($conn, $_POST['trang_thai']);
    if ($status_moi !== $task['trang_thai']) {
        $ngay_ht = ($status_moi == 'Đã hoàn thành') ? "'" . date('Y-m-d') . "'" : "NULL";
        mysqli_query($conn, "UPDATE cong_viec SET trang_thai = '$status_moi', ngay_hoan_thanh_thuc_te = $ngay_ht WHERE id = $id");

        $log_msg = "📢 Hệ thống: Cập nhật trạng thái thành [$status_moi]";
        mysqli_query($conn, "INSERT INTO binh_luan (cong_viec_id, user_id, noi_dung) VALUES ($id, $user_id, '$log_msg')");
        $_SESSION['success'] = "Đã cập nhật tiến độ!";
    } else {
        $_SESSION['info'] = "Trạng thái không thay đổi.";
    }
    header("Location: detail.php?id=$id");
    exit();
}

include '../../includes/header.php';
?>

<style>
    body {
        background-color: #f8f9fa;
    }

    .card {
        border-radius: 12px;
        border: none;
    }

    .task-desc {
        background: #fff;
        border-left: 5px solid #0d6efd;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .comment-item {
        position: relative;
        padding-left: 20px;
        transition: all 0.3s;
    }

    .comment-item::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .system-log {
        background-color: #fff8e1 !important;
        border: 1px dashed #ffc107 !important;
    }

    .comment-bubble {
        border-radius: 15px;
        border: 1px solid #eee;
        background: #fff;
    }
</style>

<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="list.php" class="btn btn-white shadow-sm rounded-circle me-3"><i class="fa fa-arrow-left"></i></a>
        <h3 class="fw-bold mb-0">Chi tiết nhiệm vụ <span class="text-muted small">#<?= $id ?></span></h3>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h4 class="fw-bold text-dark"><?= htmlspecialchars($task['ten_cong_viec']) ?></h4>
                        <?php
                        $badge = "bg-secondary";
                        if ($task['trang_thai'] == 'Đang thực hiện') $badge = "bg-primary";
                        if ($task['trang_thai'] == 'Đã hoàn thành') $badge = "bg-success";
                        if ($task['trang_thai'] == 'Quá hạn') $badge = "bg-danger";
                        ?>
                        <span class="badge <?= $badge ?> rounded-pill px-3 py-2 h-100"><?= $task['trang_thai'] ?></span>
                    </div>

                    <div class="task-desc mb-4">
                        <h6 class="text-muted fw-bold small text-uppercase mb-2"><i class="fa fa-align-left me-2"></i>Mô tả công việc</h6>
                        <div class="text-dark" style="white-space: pre-wrap; line-height: 1.6;"><?= nl2br(htmlspecialchars($task['mo_ta'])) ?: '<i>Không có mô tả chi tiết.</i>' ?></div>
                    </div>

                    <div class="row g-2 text-center">
                        <div class="col-4 p-3 border rounded-3 bg-light">
                            <small class="text-muted d-block small">NGƯỜI GIAO</small>
                            <span class="fw-bold small"><?= $task['nguoi_giao'] ?></span>
                        </div>
                        <div class="col-4 p-3 border rounded-3 bg-light">
                            <small class="text-muted d-block small">THỰC HIỆN</small>
                            <span class="fw-bold small"><?= $task['nguoi_lam'] ?></span>
                        </div>
                        <div class="col-4 p-3 border rounded-3 bg-light border-danger-subtle">
                            <small class="text-muted d-block small">HẠN CHÓT</small>
                            <span class="text-danger fw-bold small"><?= date('d/m/Y', strtotime($task['han_hoan_thanh'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fa fa-comments me-2 text-primary"></i>Trao đổi & Lịch sử</h5>

                    <form id="commentForm" class="mb-4 shadow-sm rounded-pill overflow-hidden border">
                        <input type="hidden" name="cong_viec_id" value="<?= $id ?>">
                        <div class="input-group">
                            <input type="text" name="noi_dung" id="commentInput" class="form-control border-0 px-4" placeholder="Nhập nội dung phản hồi..." required>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">GỬI</button>
                        </div>
                    </form>

                    <div id="commentContainer">
                        <?php
                        $sql_cm = "SELECT bl.*, u.ho_ten FROM binh_luan bl 
                                   JOIN users u ON bl.user_id = u.id 
                                   WHERE bl.cong_viec_id = $id ORDER BY bl.created_at DESC";
                        $res_cm = mysqli_query($conn, $sql_cm);
                        while ($cm = mysqli_fetch_assoc($res_cm)):
                            $is_sys = (strpos($cm['noi_dung'], '📢 Hệ thống') !== false);
                        ?>
                            <div class="comment-item mb-3">
                                <div class="p-3 shadow-sm comment-bubble <?= $is_sys ? 'system-log' : '' ?>">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-bold small <?= $is_sys ? 'text-dark' : 'text-primary' ?>">
                                            <i class="fa <?= $is_sys ? 'fa-robot' : 'fa-user-circle' ?> me-1"></i><?= htmlspecialchars($cm['ho_ten']) ?>
                                        </span>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?= date('H:i d/m/Y', strtotime($cm['created_at'])) ?></small>
                                    </div>
                                    <div class="small text-dark"><?= nl2br(htmlspecialchars($cm['noi_dung'])) ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold"><i class="fa fa-sync-alt me-2 text-success"></i>Cập nhật tiến độ</div>
                <div class="card-body">
                    <form method="POST" id="statusForm">
                        <select name="trang_thai" class="form-select mb-3 fw-bold border-primary-subtle text-primary">
                            <?php
                            foreach (["Chưa thực hiện", "Đang thực hiện", "Đã hoàn thành", "Quá hạn"] as $st) {
                                $sel = ($task['trang_thai'] == $st) ? "selected" : "";
                                echo "<option value='$st' $sel>$st</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="update_status" id="btnStatus" class="btn btn-success w-100 fw-bold shadow-sm">LƯU THAY ĐỔI</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between">
                    <span class="fw-bold"><i class="fa fa-paperclip me-2 text-danger"></i>Minh chứng</span>
                    <span class="badge bg-danger rounded-pill"><?= mysqli_num_rows(mysqli_query($conn, "SELECT id FROM file_uploads WHERE cong_viec_id = $id")) ?></span>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="mb-3">
                        <div class="input-group input-group-sm shadow-sm">
                            <input type="file" name="file_san_pham" class="form-control" required>
                            <button class="btn btn-danger" type="submit" name="upload_file"><i class="fa fa-upload"></i></button>
                        </div>
                    </form>
                    <div class="list-group list-group-flush border-top">
                        <?php
                        $res_files = mysqli_query($conn, "SELECT * FROM file_uploads WHERE cong_viec_id = $id");
                        while ($f = mysqli_fetch_assoc($res_files)):
                        ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <a href="<?= BASE_URL . $f['duong_dan'] ?>" target="_blank" class="text-decoration-none text-dark small text-truncate fw-bold" style="max-width: 70%;">
                                    <i class="fa-regular fa-file-lines me-2 text-primary"></i><?= htmlspecialchars($f['ten_file']) ?>
                                </a>
                                <button onclick="confirmDeleteFile(<?= $f['id'] ?>)" class="btn btn-link text-danger p-0"><i class="fa fa-trash-can"></i></button>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // 1. Cấu hình Toast thông báo
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // 2. Hiển thị thông báo Session
        <?php if (isset($_SESSION['success'])): ?>
            Toast.fire({
                icon: 'success',
                title: '<?= $_SESSION['success'] ?>'
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['info'])): ?>
            Toast.fire({
                icon: 'info',
                title: '<?= $_SESSION['info'] ?>'
            });
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>

        // 3. Xử lý AJAX Comment
        $('#commentForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $(this).find('button');
            let input = $('#commentInput');

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'process_comment.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        let html = `
                        <div class="comment-item mb-3" style="display:none;">
                            <div class="p-3 shadow-sm comment-bubble">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold small text-primary"><i class="fa fa-user-circle me-1"></i>${res.ho_ten}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">${res.thoi_gian}</small>
                                </div>
                                <div class="small text-dark">${res.noi_dung}</div>
                            </div>
                        </div>`;
                        $('#commentContainer').prepend(html);
                        $('.comment-item').first().fadeIn(500);
                        input.val('');
                        Toast.fire({
                            icon: 'success',
                            title: 'Đã gửi phản hồi!'
                        });
                    } else {
                        Swal.fire('Lỗi', res.message, 'error');
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).text('GỬI');
                }
            });
        });

        // 4. Loading khi đổi trạng thái
        $('#statusForm').on('submit', function() {
            $('#btnStatus').html('<span class="spinner-border spinner-border-sm me-2"></span> Đang lưu...').addClass('disabled');
        });
    });

    // 5. Xác nhận xóa file
    function confirmDeleteFile(fileId) {
        Swal.fire({
            title: 'Xóa file này?',
            text: "Bạn sẽ không thể khôi phục lại tệp này!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Đồng ý xóa'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'detail.php?id=<?= $id ?>&delete_file=' + fileId;
            }
        })
    }
</script>

<?php include '../../includes/footer.php'; ?>