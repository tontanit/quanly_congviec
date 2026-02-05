<?php
// Nhúng file cấu hình vào
include 'config/database.php';

if ($conn) {
    echo "<h1 style='color: green;'>Chúc mừng! Bạn đã kết nối Cơ sở dữ liệu thành công.</h1>";
    echo "Hệ thống đã sẵn sàng để xây dựng các chức năng tiếp theo.";
}
