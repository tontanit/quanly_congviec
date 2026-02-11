-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th2 11, 2026 lúc 04:00 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `db_quanly_cv`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `binh_luan`
--

CREATE TABLE `binh_luan` (
  `id` int(11) NOT NULL,
  `cong_viec_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `binh_luan`
--

INSERT INTO `binh_luan` (`id`, `cong_viec_id`, `user_id`, `noi_dung`, `created_at`) VALUES
(1, 11, 1, 'Báo cáo đã hoàn thành theo kế hoạch', '2026-02-05 13:15:13'),
(2, 11, 1, 'OK', '2026-02-05 13:15:25'),
(3, 10, 1, 'Đề nghị tiến hành gắp', '2026-02-05 13:18:39'),
(4, 11, 1, '📢 Hệ thống: Trạng thái thay đổi từ [Quá hạn] thành [Đang thực hiện].', '2026-02-05 13:29:47'),
(5, 10, 6, 'Đã hoànht hành\r\n', '2026-02-05 13:32:04'),
(6, 10, 1, 'chưa thấy file minh chứng', '2026-02-05 13:34:59'),
(7, 10, 1, '📢 Hệ thống: Trạng thái thay đổi từ [Quá hạn] thành [Đã hoàn thành].', '2026-02-05 13:37:47'),
(8, 9, 1, '📢 Hệ thống: Trạng thái thay đổi từ [Quá hạn] thành [Đang thực hiện].', '2026-02-05 13:52:29'),
(9, 9, 1, '📢 Hệ thống: Trạng thái thay đổi từ [Quá hạn] thành [Đang thực hiện].', '2026-02-05 14:04:53'),
(12, 10, 1, '📢 Hệ thống: Trạng thái thay đổi từ [Quá hạn] thành [Đang thực hiện].', '2026-02-06 14:44:48'),
(13, 10, 1, '📢 Hệ thống: Trạng thái thay đổi từ [Quá hạn] thành [Đã hoàn thành].', '2026-02-06 14:47:46'),
(14, 9, 1, '📢 Hệ thống: Trạng thái thay đổi từ [Quá hạn] thành [Đã hoàn thành].', '2026-02-06 14:48:50'),
(16, 10, 6, 'Báo cáo đã hoàn thành và có file minh chứng kèm theo', '2026-02-06 15:10:37'),
(17, 10, 1, '📢 Hệ thống: Trạng thái cập nhật thành [Chưa thực hiện]', '2026-02-06 15:16:13'),
(18, 7, 5, '📢 Hệ thống: Trạng thái cập nhật thành [Quá hạn]', '2026-02-06 15:16:50'),
(19, 7, 5, '📢 Hệ thống: Trạng thái cập nhật thành [Đã hoàn thành]', '2026-02-06 15:17:10'),
(20, 7, 5, '📢 Hệ thống: Trạng thái công việc đã được cập nhật thành [Chưa thực hiện]', '2026-02-06 15:18:29'),
(21, 7, 5, '📢 Hệ thống: Trạng thái cập nhật thành [Chưa thực hiện]', '2026-02-06 15:19:01'),
(22, 7, 5, '📢 Hệ thống: Trạng thái công việc đã được cập nhật thành [Đang thực hiện]', '2026-02-06 15:19:44'),
(23, 7, 5, '📢 Hệ thống: Trạng thái công việc đã được cập nhật thành [Đã hoàn thành]', '2026-02-06 15:19:47'),
(24, 7, 5, 'đã hoàn thành và có file minh chứng kèm theo', '2026-02-06 15:21:16'),
(25, 10, 1, 'cập nhật tiến độ cho phù hợp', '2026-02-06 15:21:51'),
(26, 10, 1, 'ff', '2026-02-06 09:27:44'),
(27, 10, 1, 'fdfdf', '2026-02-06 09:27:47'),
(28, 10, 1, 'dfdfdf', '2026-02-06 09:28:01'),
(29, 10, 1, 'dfdf', '2026-02-06 09:28:10'),
(33, 10, 1, '📢 Hệ thống: Tải lên minh chứng [Đặc tả web app.docx]', '2026-02-06 15:36:43'),
(34, 10, 6, '📢 Hệ thống: Cập nhật trạng thái thành [Đã hoàn thành]', '2026-02-07 14:46:59'),
(35, 11, 1, '📅 Hệ thống: Đã thay đổi hạn chót từ 23/01/2026 thành 12/01/2026.', '2026-02-11 14:50:36'),
(36, 11, 1, '📢 Hệ thống: Cập nhật trạng thái thành [Đang thực hiện]', '2026-02-11 14:50:58'),
(37, 10, 1, '📅 Hệ thống: Đã thay đổi hạn chót từ 19/01/2026 thành 13/02/2026.', '2026-02-11 14:52:16'),
(38, 11, 1, '📅 Hệ thống: Đã thay đổi hạn chót từ 12/01/2026 thành 12/02/2026.', '2026-02-11 14:52:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cong_viec`
--

CREATE TABLE `cong_viec` (
  `id` int(11) NOT NULL,
  `ten_cong_viec` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `nguoi_giao_id` int(11) DEFAULT NULL,
  `nguoi_thuc_hien_id` int(11) DEFAULT NULL,
  `ngay_nhan` date DEFAULT NULL,
  `han_hoan_thanh` date DEFAULT NULL,
  `ngay_hoan_thanh_thuc_te` date DEFAULT NULL,
  `trang_thai` enum('Chưa thực hiện','Đang thực hiện','Đã hoàn thành','Quá hạn') DEFAULT 'Chưa thực hiện',
  `san_pham_mo_ta` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cong_viec`
--

INSERT INTO `cong_viec` (`id`, `ten_cong_viec`, `mo_ta`, `nguoi_giao_id`, `nguoi_thuc_hien_id`, `ngay_nhan`, `han_hoan_thanh`, `ngay_hoan_thanh_thuc_te`, `trang_thai`, `san_pham_mo_ta`, `created_at`, `updated_at`) VALUES
(7, 'Triển khai thực hiện Đề án 05-ĐA/TU ngày 02/12/2025 của Tỉnh ủy về tăng cường bảo vệ, chăm sóc và nâng cao chất lượng khám chữa bệnh cho Nhân dân tỉnh Khánh Hòa trong tình hình mới', 'Triển khai thực hiện Đề án 05-ĐA/TU ngày 02/12/2025 của Tỉnh ủy về tăng cường bảo vệ, chăm sóc và nâng cao chất lượng khám chữa bệnh cho Nhân dân tỉnh Khánh Hòa trong tình hình mới', 1, 5, '2026-01-22', '2026-01-23', '2026-02-06', 'Đã hoàn thành', NULL, '2026-01-22 11:42:59', '2026-02-06 15:19:47'),
(9, 'Triển khai thực hiện Kế hoạch số 17-KH/TU, ngày 05/12/2025 của Tỉnh ủy thực hiện Chỉ thị số 38-CT/TW, ngày 30/7/2024 của Ban Bí thư về đẩy mạnh công tác tiểu chuẩn, đo lường, chất lượng quốc gia đến năm 2030 và các năm tiếp theo', 'Triển khai thực hiện Kế hoạch số 17-KH/TU, ngày 05/12/2025 của Tỉnh ủy\r\nthực hiện Chỉ thị số 38-CT/TW, ngày 30/7/2024 của Ban Bí thư\r\nvề đẩy mạnh công tác tiểu chuẩn, đo lường, chất lượng quốc gia\r\nđến năm 2030 và các năm tiếp theo\r\n', 1, 6, '2026-01-22', '2026-01-21', NULL, 'Đã hoàn thành', NULL, '2026-01-22 12:00:49', '2026-02-06 14:48:50'),
(10, ' Hướng dẫn đánh giá chỉ tiêu C-II thuộc bộ KPI đo lường, đánh giá kết quả, hiệu quả hoạt động của các cơ quan, đơn vị, khối Đảng, Mặt trận và đoàn thể', 'Hướng dẫn đánh giá chỉ tiêu C-II thuộc bộ KPI đo lường, đánh giá kết quả, hiệu quả hoạt động của các cơ quan, đơn vị, khối Đảng, Mặt trận và đoàn thể\r\n', 1, 6, '2026-02-10', '2026-02-13', '2026-02-07', 'Đã hoàn thành', NULL, '2026-01-22 12:04:18', '2026-02-11 14:52:16'),
(11, 'KẾ HOẠCH Số 29-KH/TU của Tỉnh ủy thực hiện Chỉ thị số 54-CT/TW, ngày 30/11/2025 của Bộ Chính trị về tăng cường sự lãnh đạo của Đảng đối với công tác giám định tư pháp và định giá tài sản', 'KẾ HOẠCH\r\nthực hiện Chỉ thị số 54-CT/TW, ngày 30/11/2025 của Bộ Chính trị\r\nvề tăng cường sự lãnh đạo của Đảng đối với công tác\r\ngiám định tư pháp và định giá tài sản', 1, 11, '2026-02-10', '2026-02-12', NULL, 'Quá hạn', NULL, '2026-01-22 12:08:16', '2026-02-11 14:52:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `file_uploads`
--

CREATE TABLE `file_uploads` (
  `id` int(11) NOT NULL,
  `cong_viec_id` int(11) DEFAULT NULL,
  `ten_file` varchar(255) DEFAULT NULL,
  `duong_dan` varchar(255) DEFAULT NULL,
  `loai_file` varchar(50) DEFAULT NULL,
  `ngay_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `file_uploads`
--

INSERT INTO `file_uploads` (`id`, `cong_viec_id`, `ten_file`, `duong_dan`, `loai_file`, `ngay_upload`) VALUES
(4, 7, '1C25TYY_00000033.pdf', 'assets/uploads/1769082264_69720d98c97ab.pdf', NULL, '2026-01-22 11:44:24'),
(6, 10, 'Đặc tả web app.docx', 'assets/uploads/1770392203_69860a8bbea8c.docx', NULL, '2026-02-06 15:36:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_cong_tac`
--

CREATE TABLE `lich_cong_tac` (
  `id` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `lanh_dao_id` int(11) NOT NULL,
  `bat_dau` datetime NOT NULL,
  `ket_thuc` datetime NOT NULL,
  `dia_diem` varchar(255) DEFAULT NULL,
  `noi_dung` text DEFAULT NULL,
  `loai_lich` enum('Họp','Công tác','Tiếp khách','Khác') DEFAULT 'Họp',
  `trang_thai` enum('Dự kiến','Chính thức','Đã xong','Hủy') DEFAULT 'Dự kiến',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_cong_tac`
--

INSERT INTO `lich_cong_tac` (`id`, `tieu_de`, `lanh_dao_id`, `bat_dau`, `ket_thuc`, `dia_diem`, `noi_dung`, `loai_lich`, `trang_thai`, `created_at`, `is_deleted`, `deleted_at`) VALUES
(1, '- Đ/c Bí thư dự Họp mặt chức sắc các tôn giáo, người có uy tín trong đồng bào các dân tộc nhân dịp Xuân Bính Ngọ năm 2026.', 1, '2026-02-09 07:53:00', '2026-02-09 10:53:00', 'Hội trường UBND xã', '', 'Họp', 'Chính thức', '2026-02-06 15:54:09', 0, NULL),
(2, 'Học nghị quyết Đại học 14', 1, '2026-02-06 22:53:00', '2026-02-07 22:53:00', 'Phòng hợp trực tuyến UBND xã', '', 'Họp', 'Chính thức', '2026-02-06 15:55:41', 1, '2026-02-06 17:16:01'),
(3, 'Đi chúc tết tại xã công hải', 1, '2026-02-10 08:01:00', '2026-02-10 18:01:00', 'Đảng ủy xã Côgn Hải', '', 'Khác', 'Chính thức', '2026-02-06 16:02:06', 0, NULL),
(4, 'Dự họp mặt các vị chức sắc tôn giáo', 1, '2026-02-09 07:15:00', '2026-02-09 11:15:00', 'Hội trường UBND xã', '', 'Họp', 'Chính thức', '2026-02-06 16:15:47', 0, NULL),
(5, '[Quản trị viên] [Quản trị viên] - Đ/c Bí thư thăm hỏi, tặng quà nhân dịp Tết Ramuwan', 1, '2026-02-11 07:45:00', '2026-02-11 11:50:00', 'Nhà 02 đ/c được đi thăm', '', 'Công tác', 'Chính thức', '2026-02-07 03:50:49', 0, NULL),
(6, '- Đ/c Phó Bí thư làm việc tại cơ quan', 1, '2026-02-11 07:51:00', '2026-02-11 11:30:00', 'Cơ quan Đảng ủy xã', '', 'Công tác', 'Chính thức', '2026-02-07 03:52:16', 0, NULL),
(7, '- Đ/c Bí thư tham dự “Xuân Khu 5 đoàn kết, Tết thắm tình quân dân và ấm áp nghĩa tình Mặt trận với đồng bào dân tộc thiểu số” (từ 17 giờ 00 - 20 giờ 30)', 1, '2026-02-11 17:00:00', '2026-02-11 19:53:00', 'thôn Đá Liệt', '', 'Công tác', 'Chính thức', '2026-02-07 03:53:44', 0, NULL),
(8, 'gggggggggggggggggg', 1, '2026-02-09 11:00:00', '2026-02-10 11:00:00', 'fffffff', '', 'Họp', 'Chính thức', '2026-02-07 04:01:08', 1, '2026-02-07 08:52:54'),
(9, 'gdfgfđfgd', 1, '2026-02-10 05:38:00', '2026-02-11 08:38:00', 'fdfdfdf', '', 'Họp', 'Chính thức', '2026-02-07 07:38:41', 1, '2026-02-07 09:57:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `role` enum('admin','nguoi_giao_viec','nguoi_thuc_hien') DEFAULT 'nguoi_thuc_hien',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `ho_ten`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$yg.K1DOsBJleASbrfkZEmeE36nQ5i7kJ.mmk8cikvuvjQbWNbTsIi', 'Quản trị viên', 'admin', '2026-01-21 04:22:32'),
(5, 'tvtrung', '$2y$10$6ihPak/gfo7jk9qAXEFCoOSI17C32akcDzZ4ZupTmAuIipU/hryZy', 'Trần văn Trung', 'nguoi_thuc_hien', '2026-01-21 14:57:56'),
(6, 'ntduy', '$2y$10$L5cmcu3heIwjZ0ZknB21EueouSrXMX8akfP92WKgFIvmOTTk.VLkG', 'Nguyễn Thành Duy', 'nguoi_thuc_hien', '2026-01-21 14:59:00'),
(10, 'ntthuy', '$2y$10$I1gppp44sgpZfOYCFj9Yo.yRRoE5qupiEjderlPng7s2RvivICigW', 'Nguyễn Thị Thủy', 'nguoi_thuc_hien', '2026-01-22 11:42:04'),
(11, 'ndlam', '$2y$10$Aa6DnDOcoIpOweHuuzbAfeRKJnU3O/p93D49OPXciucZFfdgjuS9K', 'Nguyễn Duy Lãm', 'nguoi_thuc_hien', '2026-01-22 11:42:21'),
(12, 'dttan', '$2y$10$JmAJ5ho8svE8nsp.rWeVautGllXZJxqU/I5R/h1fsOVp4NwIgwrza', 'Đậu Thị Tân', 'nguoi_thuc_hien', '2026-01-22 11:42:42'),
(13, 'bthu', '$2y$10$uExikx.mZY5dAPhlhoZG1eA6NEGgDPFuVFLaBKBp8A8MD6zm6Pki2', 'Đ/c Bí thư', 'nguoi_giao_viec', '2026-02-07 03:46:11'),
(14, 'pbthu', '$2y$10$I874m/AWoi.NES6uxQb7BOGzL7yk5XO17tNizbgw8WBi753AHdO0C', 'Đ/c Phó Bí thư', 'nguoi_giao_viec', '2026-02-07 03:46:33');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cong_viec_id` (`cong_viec_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `cong_viec`
--
ALTER TABLE `cong_viec`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nguoi_giao_id` (`nguoi_giao_id`),
  ADD KEY `nguoi_thuc_hien_id` (`nguoi_thuc_hien_id`);

--
-- Chỉ mục cho bảng `file_uploads`
--
ALTER TABLE `file_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cong_viec_id` (`cong_viec_id`);

--
-- Chỉ mục cho bảng `lich_cong_tac`
--
ALTER TABLE `lich_cong_tac`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lanh_dao_id` (`lanh_dao_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT cho bảng `cong_viec`
--
ALTER TABLE `cong_viec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `file_uploads`
--
ALTER TABLE `file_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `lich_cong_tac`
--
ALTER TABLE `lich_cong_tac`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD CONSTRAINT `binh_luan_ibfk_1` FOREIGN KEY (`cong_viec_id`) REFERENCES `cong_viec` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `binh_luan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `cong_viec`
--
ALTER TABLE `cong_viec`
  ADD CONSTRAINT `cong_viec_ibfk_1` FOREIGN KEY (`nguoi_giao_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cong_viec_ibfk_2` FOREIGN KEY (`nguoi_thuc_hien_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `file_uploads`
--
ALTER TABLE `file_uploads`
  ADD CONSTRAINT `file_uploads_ibfk_1` FOREIGN KEY (`cong_viec_id`) REFERENCES `cong_viec` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lich_cong_tac`
--
ALTER TABLE `lich_cong_tac`
  ADD CONSTRAINT `lich_cong_tac_ibfk_1` FOREIGN KEY (`lanh_dao_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
