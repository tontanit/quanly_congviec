-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 21, 2026 lúc 12:26 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

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
(1, 'Công văn số 36 Đề nghị góp ý dự thảo các văn bản trình BTV Đảng ủy xã', 'Thực hiện Đề án 06 của Tỉnh ủy', 1, 2, '2026-01-21', '2026-01-22', '2026-01-21', 'Đã hoàn thành', NULL, '2026-01-21 06:59:26', '2026-01-21 07:48:34'),
(2, 'Làm hs ứng cử đại biểu HĐND xã cho chú nhá', 'HS ứng cử đại biểu HĐND xã', 1, 3, '2026-01-21', '2026-01-23', NULL, 'Quá hạn', NULL, '2026-01-21 07:06:01', '2026-01-21 07:48:47'),
(3, 'Yêu cầu thực hiện Đề án 05 - Tăng cường bảo vệ, chăm sóc và nâng cao chất lương khám chữa bệnh trên địa bàn xã', 'Yêu cầu thực hiện Đề án 05 - Tăng cường bảo vệ, chăm sóc và nâng cao chất lương khám chữa bệnh trên địa bàn xã', 1, 2, '2026-01-21', '2026-01-19', NULL, 'Quá hạn', NULL, '2026-01-21 07:52:53', '2026-01-21 07:52:54');

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
(1, 2, '1768980088_Công văn - Yêu cầu cấp mới chứng thư chữ ký số cá nhân - Nguyễn thị Thủy.signed.signed.pdf', 'assets/uploads/1768980088_Công văn - Yêu cầu cấp mới chứng thư chữ ký số cá nhân - Nguyễn thị Thủy.signed.signed.pdf', NULL, '2026-01-21 07:21:28'),
(2, 2, '1768980880_Công văn - Yêu cầu cấp mới chứng thư chữ ký số cá nhân - Nguyễn thị Thủy.signed.signed.pdf', 'assets/uploads/1768980880_Công văn - Yêu cầu cấp mới chứng thư chữ ký số cá nhân - Nguyễn thị Thủy.signed.signed.pdf', NULL, '2026-01-21 07:34:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `role` enum('admin','nguoi_giao','nguoi_thuc_hien') DEFAULT 'nguoi_thuc_hien',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `ho_ten`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$zZX/bPoO2E.LnddjX2qqJuQZj7Q3EXNT4s3Ww5/G7Hj6m/Qia14ZW', 'Quản trị viên', 'admin', '2026-01-21 04:22:32'),
(2, 'nguyenvana', '$2y$10$mC7GJC8pXGjGvH9E2N.vO.1V5F5gS5vUq/LzO7I8zL/y6K9P7V7m.', 'Nguyễn Văn An', 'nguoi_thuc_hien', '2026-01-21 06:57:27'),
(3, 'tranlhinh', '$2y$10$mC7GJC8pXGjGvH9E2N.vO.1V5F5gS5vUq/LzO7I8zL/y6K9P7V7m.', 'Trần Lê Hình', 'nguoi_thuc_hien', '2026-01-21 06:57:27'),
(4, 'phamthib', '$2y$10$mC7GJC8pXGjGvH9E2N.vO.1V5F5gS5vUq/LzO7I8zL/y6K9P7V7m.', 'Phạm Thị Bình', 'nguoi_thuc_hien', '2026-01-21 06:57:27');

--
-- Chỉ mục cho các bảng đã đổ
--

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
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cong_viec`
--
ALTER TABLE `cong_viec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `file_uploads`
--
ALTER TABLE `file_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
