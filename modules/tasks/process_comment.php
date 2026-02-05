<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cong_viec_id = (int)$_POST['cong_viec_id'];
    $noi_dung = mysqli_real_escape_string($conn, $_POST['noi_dung']);

    if (!empty($noi_dung)) {
        $sql = "INSERT INTO binh_luan (cong_viec_id, user_id, noi_dung) 
                VALUES ($cong_viec_id, $user_id, '$noi_dung')";
        mysqli_query($conn, $sql);
    }

    header("Location: detail.php?id=$cong_viec_id");
    exit();
}
