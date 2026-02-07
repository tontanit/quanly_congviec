<?php
require_once '../../config/database.php';

// 1. Nhận ngày bắt đầu và ngày kết thúc
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');

// Chỉnh lại định dạng hiển thị cho tên file
$display_from = date('d-m-Y', strtotime($from_date));
$display_to = date('d-m-Y', strtotime($to_date));
$filename = "LichCongTac_{$display_from}_den_{$display_to}.xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
echo "\xEF\xBB\xBF"; // UTF-8 BOM

// 2. Câu lệnh SQL lọc trong khoảng (Sử dụng DATE() để so sánh chính xác ngày)
$sql = "SELECT * FROM lich_cong_tac 
        WHERE deleted_at IS NULL 
        AND DATE(bat_dau) BETWEEN ? AND ? 
        ORDER BY bat_dau ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2 style="text-align: center;">LỊCH CÔNG TÁC</h2>
<p style="text-align: center;">Từ ngày: <?php echo $display_from; ?> đến ngày: <?php echo $display_to; ?></p>

<table border="1">
    <thead>
        <tr style="background-color: #eee;">
            <th>STT</th>
            <th>Nội dung công việc</th>
            <th>Bắt đầu</th>
            <th>Kết thúc</th>
            <th>Địa điểm</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stt = 1;
        while ($row = $result->fetch_assoc()):
        ?>
            <tr>
                <td align="center"><?php echo $stt++; ?></td>
                <td><?php echo htmlspecialchars($row['tieu_de']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['bat_dau'])); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['ket_thuc'])); ?></td>
                <td><?php echo htmlspecialchars($row['dia_diem']); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>