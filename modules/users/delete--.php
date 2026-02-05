<?php
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    exit("Bạn không có quyền thực hiện thao tác này!");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ngăn chặn việc tự xóa chính mình
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('Bạn không thể tự xóa tài khoản của chính mình!'); window.location.href='manage.php';</script>";
        exit();
    }

    $sql = "DELETE FROM users WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        header("Location: manage.php?msg=deleted");
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
