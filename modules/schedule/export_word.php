<?php
require_once '../../config/database.php';

// 1. Nhận ngày lọc
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');

$display_from = date('d-m-Y', strtotime($from_date));
$display_to = date('d-m-Y', strtotime($to_date));
$filename = "LichCongTac_{$display_from}_den_{$display_to}.doc";

// 2. HEADER QUAN TRỌNG: Ép kiểu chuẩn Microsoft Word
header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// 3. Truy vấn dữ liệu
$sql = "SELECT * FROM lich_cong_tac 
        WHERE deleted_at IS NULL 
        AND DATE(bat_dau) BETWEEN ? AND ? 
        ORDER BY bat_dau ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$result = $stmt->get_result();

// 4. Nội dung HTML chuẩn Word (Cần có thẻ xml để Word không nhận nhầm sang Excel)
echo "
<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset='utf-8'>
    <style>
        body { font-family: 'Times New Roman', Times, serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; font-size: 12pt; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header-title { text-align: center; text-transform: uppercase; font-weight: bold; font-size: 16pt; }
    </style>
</head>
<body>
    <div class='header-title'>BÁO CÁO LỊCH CÔNG TÁC</div>
    <p style='text-align: center;'>Từ ngày: $display_from đến ngày: $display_to</p>
    <br>
    <table>
        <thead>
            <tr>
                <th width='5%'>STT</th>
                <th width='40%'>Nội dung công việc</th>
                <th width='25%'>Thời gian</th>
                <th width='30%'>Địa điểm</th>
            </tr>
        </thead>
        <tbody>";

$stt = 1;
while ($row = $result->fetch_assoc()) {
    $start = date('H:i d/m/Y', strtotime($row['bat_dau']));
    $end = date('H:i d/m/Y', strtotime($row['ket_thuc']));
    echo "
            <tr>
                <td align='center'>$stt</td>
                <td><b>{$row['tieu_de']}</b><br><small>{$row['noi_dung']}</small></td>
                <td>$start - $end</td>
                <td>{$row['dia_diem']}</td>
            </tr>";
    $stt++;
}

echo "
        </tbody>
    </table>
</body>
</html>";
