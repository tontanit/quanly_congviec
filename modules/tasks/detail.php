<?php
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 1. Lấy thông tin chi tiết công việc + Kiểm tra quyền
$sql = "SELECT cv.*, u1.ho_ten as nguoi_giao, u2.ho_ten as nguoi_lam 
        FROM cong_viec cv 
        JOIN users u1 ON cv.nguoi_giao_id = u1.id 
        JOIN users u2 ON cv.nguoi_thuc_hien_id = u2.id 
        WHERE cv.id = $id";
$res = mysqli_query($conn, $sql);
$task = mysqli_fetch_assoc($res);

if (!$task || ($role !== 'admin' && $task['nguoi_thuc_hien_id'] != $user_id)) {
    echo "<div class='container mt-5 alert alert-danger text-center'>Bạn không có quyền xem hoặc công việc không tồn tại.</div>";
    exit();
}

// 2. XỬ LÝ XÓA FILE (Mới thêm)
if (isset($_GET['delete_file'])) {
    $file_id = (int)$_GET['delete_file'];

    // Lấy thông tin file để xóa file vật lý
    $res_f = mysqli_query($conn, "SELECT duong_dan FROM file_uploads WHERE id = $file_id AND cong_viec_id = $id");
    $file_data = mysqli_fetch_assoc($res_f);

    if ($file_data) {
        $physical_path = "../../" . $file_data['duong_dan'];
        if (file_exists($physical_path)) unlink($physical_path); // Xóa file trên server

        mysqli_query($conn, "DELETE FROM file_uploads WHERE id = $file_id"); // Xóa trong DB
        header("Location: detail.php?id=$id&status=deleted");
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
        header("Location: detail.php?id=$id&status=uploaded");
        exit();
    }
}

// 4. XỬ LÝ CẬP NHẬT TRẠNG THÁI
if (isset($_POST['update_status'])) {
    $status_moi = mysqli_real_escape_string($conn, $_POST['trang_thai']);
    $ngay_ht = ($status_moi == 'Đã hoàn thành') ? "'" . date('Y-m-d') . "'" : "NULL";
    mysqli_query($conn, "UPDATE cong_viec SET trang_thai = '$status_moi', ngay_hoan_thanh_thuc_te = $ngay_ht WHERE id = $id");
    header("Location: detail.php?id=$id&status=updated");
    exit();
}

include '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-success alert-dismissible fade show small py-2">
            <i class="fa fa-check me-2"></i> Thao tác thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="padding: 0.5rem;"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between">
                    <h6 class="text-primary text-uppercase fw-bold mb-0">Chi tiết nhiệm vụ</h6>
                    <span class="badge bg-light text-dark border">ID: #<?= $id ?></span>
                </div>
                <div class="card-body">
                    <h4 class="text-lowercase mb-3" style="font-weight: 400; color: #333;">
                        <?= htmlspecialchars($task['ten_cong_viec']) ?>
                    </h4>

                    <div class="bg-light p-3 rounded mb-4 shadow-sm" style="min-height: 100px; white-space: pre-wrap;">
                        <?= htmlspecialchars($task['mo_ta']) ?: '<i class="text-muted">Không có mô tả.</i>' ?>
                    </div>

                    <div class="row g-3 text-center border-top pt-3">
                        <div class="col-md-4 border-end">
                            <small class="text-muted d-block text-uppercase">Người giao</small>
                            <span class="small"><?= $task['nguoi_giao'] ?></span>
                        </div>
                        <div class="col-md-4 border-end">
                            <small class="text-muted d-block text-uppercase">Hạn chót</small>
                            <span class="text-danger small fw-bold"><?= date('d/m/Y', strtotime($task['han_hoan_thanh'])) ?></span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block text-uppercase mb-2">Trạng thái</small>
                            <form method="POST" class="d-flex justify-content-center gap-1">
                                <select name="trang_thai" class="form-select form-select-sm" style="width: auto;">
                                    <?php
                                    $arr = ["Chưa thực hiện", "Đang thực hiện", "Đã hoàn thành", "Quá hạn"];
                                    foreach ($arr as $st) {
                                        $sel = ($task['trang_thai'] == $st) ? "selected" : "";
                                        echo "<option value='$st' $sel>$st</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-success px-3">Lưu</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold"><i class="fa fa-paperclip me-2 text-primary"></i>Tệp đính kèm</h6>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="mb-4">
                        <div class="input-group input-group-sm">
                            <input type="file" name="file_san_pham" class="form-control" required>
                            <button class="btn btn-primary px-3" type="submit" name="upload_file">Tải lên</button>
                        </div>
                    </form>

                    <div class="list-group list-group-flush border-top mt-2">
                        <?php
                        $res_files = mysqli_query($conn, "SELECT * FROM file_uploads WHERE cong_viec_id = $id");
                        if (mysqli_num_rows($res_files) > 0):
                            while ($f = mysqli_fetch_assoc($res_files)):
                        ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <a href="<?= BASE_URL . $f['duong_dan'] ?>" target="_blank" class="text-decoration-none text-lowercase small text-truncate" style="max-width: 70%;">
                                        <i class="fa-regular fa-file-pdf me-2 text-danger"></i><?= htmlspecialchars($f['ten_file']) ?>
                                    </a>
                                    <div class="btn-group">
                                        <a href="<?= BASE_URL . $f['duong_dan'] ?>" download class="btn btn-link btn-sm text-muted p-1"><i class="fa fa-download"></i></a>
                                        <a href="detail.php?id=<?= $id ?>&delete_file=<?= $f['id'] ?>"
                                            class="btn btn-link btn-sm text-danger p-1"
                                            onclick="return confirm('Bạn chắc chắn muốn xóa file này?')">
                                            <i class="fa fa-trash-can"></i>
                                        </a>
                                    </div>
                                </div>
                        <?php
                            endwhile;
                        else:
                            echo "<p class='text-center text-muted small mt-3 italic'>Chưa có file minh chứng.</p>";
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>