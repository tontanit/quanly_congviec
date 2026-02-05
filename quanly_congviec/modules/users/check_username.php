<?php
require_once '../../config/database.php';

if (isset($_POST['username'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));

    // Kiểm tra trong database
    $sql = "SELECT id FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Trả về số 1 nếu đã tồn tại
        echo "exists";
    } else {
        // Trả về số 0 nếu hợp lệ
        echo "available";
    }
}
