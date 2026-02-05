<?php
require_once '../../config/database.php';

// Bảo mật: Chỉ Admin mới được quyền xóa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Bạn không có quyền thực hiện thao tác này!'); window.location.href='list.php';</script>";
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Lệnh xóa (Lưu ý: Do ta thiết kế ON DELETE CASCADE ở bước tạo DB, 
    // nên các file đính kèm liên quan trong bảng file_uploads sẽ tự động bị xóa theo)
    $sql_delete = "DELETE FROM cong_viec WHERE id = $id";

    if (mysqli_query($conn, $sql_delete)) {
        header("Location: list.php?msg=deleted");
    } else {
        echo "Lỗi khi xóa: " . mysqli_error($conn);
    }
}
