<?php
$password_moi = '123456';
// Hàm này sẽ tạo ra một chuỗi hash mới hoàn toàn
$hash_moi = password_hash($password_moi, PASSWORD_DEFAULT);

echo "Chuỗi hash chuẩn trên máy bạn là: <br><strong>" . $hash_moi . "</strong><br><br>";
echo "Hãy copy chuỗi này dán vào câu lệnh SQL ở Bước 2.";
