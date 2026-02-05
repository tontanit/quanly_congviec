<?php
// 1. Khai báo các thông số kết nối
$host = "localhost";
$user = "root";      // Mặc định của XAMPP
$pass = "";          // Mặc định của XAMPP là để trống
$dbname = "db_quanly_cv";

// 2. Thực hiện kết nối
$conn = mysqli_connect($host, $user, $pass, $dbname);

// 3. Kiểm tra kết nối
if (!$conn) {
    // Nếu lỗi, dừng hệ thống và thông báo lỗi cụ thể
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}

// 4. Thiết lập font tiếng Việt để lưu dữ liệu không bị lỗi dấu
mysqli_set_charset($conn, "utf8mb4");

// 5. Khởi động Session (Phiên làm việc) 
// Dùng để ghi nhớ người dùng đã đăng nhập ở tất cả các trang
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Định nghĩa địa chỉ gốc của dự án
define('BASE_URL', 'http://localhost/quanly_congviec/');

// Cập nhật Logic Tự động kiểm tra Quá hạn
$today = date('Y-m-d');
$sql_auto_deadline = "UPDATE cong_viec 
                      SET trang_thai = 'Quá hạn' 
                      WHERE han_hoan_thanh < '$today' 
                      AND trang_thai != 'Đã hoàn thành'";
mysqli_query($conn, $sql_auto_deadline);
