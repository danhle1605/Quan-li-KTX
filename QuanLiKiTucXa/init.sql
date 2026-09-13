-- Khởi tạo cơ sở dữ liệu Quản lý Kí túc xá UTH (Smart Dormitory System)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `dormitory_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `dormitory_db`;

-- 1. Bảng Người dùng (Tài khoản Đăng nhập / Quản trị viên / Sinh viên)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `fullname` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `role` ENUM('admin', 'student') DEFAULT 'student',
    `remember_token` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng Phòng kí túc xá
CREATE TABLE IF NOT EXISTS `rooms` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_number` VARCHAR(20) NOT NULL UNIQUE,
    `building` VARCHAR(50) NOT NULL,
    `floor` INT NOT NULL DEFAULT 1,
    `room_type` VARCHAR(50) NOT NULL DEFAULT 'Thường',
    `capacity` INT NOT NULL DEFAULT 4,
    `occupied` INT NOT NULL DEFAULT 0,
    `price` DECIMAL(12,2) NOT NULL DEFAULT 600000,
    `status` ENUM('Available', 'Full', 'Maintenance') DEFAULT 'Available',
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng Sinh viên
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `student_code` VARCHAR(20) NOT NULL UNIQUE,
    `fullname` VARCHAR(100) NOT NULL,
    `gender` ENUM('Nam', 'Nữ') NOT NULL,
    `dob` DATE NOT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `faculty` VARCHAR(100) NOT NULL,
    `avatar` VARCHAR(255) DEFAULT 'default.png',
    `room_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng Hợp đồng ở KTX UTH
CREATE TABLE IF NOT EXISTS `contracts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `room_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `deposit` DECIMAL(12,2) NOT NULL DEFAULT 1000000,
    `status` ENUM('Active', 'Expired', 'Cancelled') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng Hóa đơn & Thanh toán tiền điện nước phòng
CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_code` VARCHAR(50) NOT NULL UNIQUE,
    `room_id` INT NOT NULL,
    `billing_month` VARCHAR(20) NOT NULL,
    `room_fee` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `electricity_fee` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `water_fee` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `status` ENUM('Unpaid', 'Paid') DEFAULT 'Unpaid',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `paid_at` DATETIME NULL,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng Yêu cầu Chuyển/Đăng ký phòng
CREATE TABLE IF NOT EXISTS `room_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `current_room_id` INT NULL,
    `requested_room_id` INT NOT NULL,
    `request_type` ENUM('registration', 'transfer') DEFAULT 'transfer',
    `reason` TEXT NOT NULL,
    `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`current_room_id`) REFERENCES `rooms`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`requested_room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DỮ LIỆU MẪU: BẢNG USERS (Mật khẩu mặc định: password123)
-- ========================================================
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`) VALUES
(1, 'admin', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Quản Trị Viên KTX UTH', 'admin@uth.edu.vn', 'admin'),
(2, 'sv2026001', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Nguyễn Văn An', 'nguyenvana@gmail.com', 'student'),
(3, 'sv2026002', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Trần Thị Bình', 'tranthib@gmail.com', 'student'),
(4, 'sv2026003', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Đỗ Minh Khang', 'dominhkhang@gmail.com', 'student'),
(5, 'sv2026004', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Phạm Minh Dũng', 'phamminhd@gmail.com', 'student'),
(6, 'sv2026005', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Vũ Thị Ngọc E', 'vuthingoce@gmail.com', 'student'),
(7, 'sv2026006', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Hoàng Văn Phúc', 'hoangvanphuc@gmail.com', 'student'),
(8, 'sv2026007', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Trần Minh Đức', 'tranminhduc@gmail.com', 'student'),
(9, 'sv2026008', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Đỗ Quang Huy', 'doquanghuy@gmail.com', 'student'),
(10, 'sv2026009', '$2y$10$ByIMUXo07gB.FNKFC45GGu/tL3alcqhgUiZ0ImJB2sLyDn0oWv8hq', 'Lê Văn Hải', 'levanhai@gmail.com', 'student')
ON DUPLICATE KEY UPDATE `password`=VALUES(`password`), `fullname`=VALUES(`fullname`), `email`=VALUES(`email`);

-- ========================================================
-- DỮ LIỆU MẪU: BẢNG ROOMS (24 phòng phân bổ 3 tòa nhà)
-- Đầy đủ: Phòng trống, còn chỗ, gần đầy, đầy (Full), bảo trì
-- Sức chứa và số người ở (occupied) chuẩn xác 100%
-- ========================================================
INSERT INTO `rooms` (`id`, `room_number`, `building`, `floor`, `room_type`, `capacity`, `occupied`, `price`, `status`, `description`) VALUES
-- Tòa A (Nam) - 8 phòng
(1, 'A101', 'Tòa A (Nam)', 1, 'Máy lạnh', 4, 4, 600000.00, 'Full', 'Phòng máy lạnh Tòa A, 4 giường tầng cao cấp, quạt treo tường, bàn học cá nhân.'),
(2, 'A102', 'Tòa A (Nam)', 1, 'VIP', 4, 4, 850000.00, 'Full', 'Phòng VIP UTH ban công rộng rãi, máy lạnh Inverter, tủ lạnh riêng, vệ sinh khép kín.'),
(8, 'A103', 'Tòa A (Nam)', 1, 'Thường', 6, 4, 450000.00, 'Available', 'Phòng tiêu chuẩn 6 giường tầng, quạt trần, không gian rộng rãi thoáng mát.'),
(9, 'A201', 'Tòa A (Nam)', 2, 'Máy lạnh', 4, 4, 600000.00, 'Full', 'Phòng máy lạnh tầng 2 Tòa A, yên tĩnh, view khuôn viên đại học UTH.'),
(10, 'A202', 'Tòa A (Nam)', 2, 'VIP', 2, 2, 1100000.00, 'Full', 'Phòng VIP 2 giường đôi cao cấp cho nam sinh viên, đầy đủ tiện nghi sinh hoạt.'),
(11, 'A203', 'Tòa A (Nam)', 2, 'Thường', 6, 3, 450000.00, 'Available', 'Phòng tiêu chuẩn 6 giường Tòa A, thoáng mát, view khuôn viên KTX UTH.'),
(19, 'A301', 'Tòa A (Nam)', 3, 'Máy lạnh', 4, 0, 600000.00, 'Available', 'Phòng máy lạnh tầng 3 Tòa A mới nâng cấp, phòng trống sẵn sàng tiếp nhận sinh viên.'),
(20, 'A302', 'Tòa A (Nam)', 3, 'VIP', 2, 1, 1100000.00, 'Available', 'Phòng VIP tầng 3 Tòa A, view đẹp, hiện có 1 sinh viên (còn 1 chỗ trống).'),

-- Tòa B (Nam) - 8 phòng
(12, 'B101', 'Tòa B (Nam)', 1, 'Thường', 6, 4, 450000.00, 'Available', 'Phòng tiêu chuẩn tầng trệt Tòa B, tiện di chuyển, gần căng tin KTX.'),
(13, 'B102', 'Tòa B (Nam)', 1, 'Máy lạnh', 4, 2, 600000.00, 'Available', 'Phòng 4 giường máy lạnh tầng 1 Tòa B, sạch sẽ, thoáng mát.'),
(3, 'B201', 'Tòa B (Nam)', 2, 'Thường', 6, 4, 450000.00, 'Available', 'Phòng tiêu chuẩn Tòa B 6 giường quạt trần khép kín, sạch sẽ, chi phí tiết kiệm.'),
(4, 'B202', 'Tòa B (Nam)', 2, 'Thường', 6, 0, 450000.00, 'Available', 'Phòng trống chưa có sinh viên ở, dọn đồ sẵn sàng ở ngay.'),
(5, 'B203', 'Tòa B (Nam)', 2, 'Máy lạnh', 4, 0, 600000.00, 'Maintenance', 'Phòng đang bảo trì thiết bị máy lạnh và sơn lại tường, không cho đăng ký trong tuần này.'),
(21, 'B204', 'Tòa B (Nam)', 2, 'Thường', 6, 5, 450000.00, 'Available', 'Phòng tiêu chuẩn tầng 2 Tòa B, hiện có 5 sinh viên (gần đầy, còn đúng 1 chỗ).'),
(14, 'B301', 'Tòa B (Nam)', 3, 'VIP', 2, 2, 1000000.00, 'Full', 'Phòng VIP 2 người tầng 3 Tòa B, ban công hướng mát, trang bị máy lạnh và bàn tự học.'),
(22, 'B302', 'Tòa B (Nam)', 3, 'Thường', 4, 0, 450000.00, 'Available', 'Phòng tiêu chuẩn 4 giường tầng 3 Tòa B, thoáng mát, phòng trống mới dọn dẹp.'),

-- Tòa C (Nữ) - 8 phòng
(15, 'C101', 'Tòa C (Nữ)', 1, 'Thường', 6, 5, 450000.00, 'Available', 'Phòng nữ tiêu chuẩn tầng 1, an ninh bảo vệ 24/7, có ban công phơi đồ riêng (gần đầy).'),
(16, 'C102', 'Tòa C (Nữ)', 1, 'Máy lạnh', 4, 3, 700000.00, 'Available', 'Phòng nữ máy lạnh 4 giường mới nâng cấp hè 2026, khép kín sạch đẹp (còn 1 chỗ).'),
(17, 'C201', 'Tòa C (Nữ)', 2, 'VIP', 2, 2, 1200000.00, 'Full', 'Phòng VIP 2 nữ sinh viên, tủ gỗ âm tường, máy giặt riêng, góc học tập hiện đại.'),
(18, 'C202', 'Tòa C (Nữ)', 2, 'Máy lạnh', 4, 3, 700000.00, 'Available', 'Phòng nữ 4 giường máy lạnh tầng 2, khép kín, thoáng mát (còn 1 chỗ).'),
(23, 'C203', 'Tòa C (Nữ)', 2, 'VIP', 2, 0, 1200000.00, 'Available', 'Phòng VIP nữ tầng 2, máy lạnh, tủ lạnh, phòng trống sẵn sàng nhận đăng ký.'),
(6, 'C301', 'Tòa C (Nữ)', 3, 'VIP', 2, 2, 1200000.00, 'Full', 'Phòng VIP 2 giường cho nữ UTH, trang bị máy giặt riêng, tủ quần áo gỗ, khu an ninh.'),
(7, 'C302', 'Tòa C (Nữ)', 3, 'Máy lạnh', 4, 4, 700000.00, 'Full', 'Phòng nữ máy lạnh tiện nghi, khép kín đầy đủ tiện nghi học tập và sinh hoạt.'),
(24, 'C303', 'Tòa C (Nữ)', 3, 'Thường', 4, 4, 450000.00, 'Full', 'Phòng nữ tiêu chuẩn tầng 3 Tòa C, đã đầy đủ 4 sinh viên sinh hoạt nền nếp.')
ON DUPLICATE KEY UPDATE 
    room_number=VALUES(room_number), building=VALUES(building), floor=VALUES(floor),
    room_type=VALUES(room_type), capacity=VALUES(capacity), occupied=VALUES(occupied),
    price=VALUES(price), status=VALUES(status), description=VALUES(description);

-- ========================================================
-- DỮ LIỆU MẪU: BẢNG STUDENTS (Đúng 70 sinh viên)
-- 62 sinh viên đã xếp phòng (Khớp 100% occupied của 24 rooms)
-- 8 sinh viên chưa xếp phòng (room_id = NULL)
-- ========================================================
INSERT INTO `students` (`id`, `user_id`, `student_code`, `fullname`, `gender`, `dob`, `phone`, `email`, `address`, `faculty`, `avatar`, `room_id`) VALUES
-- Phòng 1: A101 (4 sinh viên Nam - Full)
(1, 2, 'SV2026001', 'Nguyễn Văn An', 'Nam', '2004-05-12', '0912345678', 'nguyenvana@gmail.com', 'TP. Hồ Chí Minh', 'CNTT Giao thông UTH', 'default.png', 1),
(7, 8, 'SV2026007', 'Trần Minh Đức', 'Nam', '2004-03-15', '0911223344', 'tranminhduc@gmail.com', 'Hà Nội', 'Kỹ thuật Điện UTH', 'default.png', 1),
(11, NULL, 'SV2026011', 'Bùi Tuấn Kiệt', 'Nam', '2004-04-18', '0938112233', 'buituankiet@gmail.com', 'Bình Định', 'Kỹ thuật Ô tô UTH', 'default.png', 1),
(36, NULL, 'SV2026036', 'Huỳnh Quốc Anh', 'Nam', '2004-05-14', '0903112233', 'huynhquocanh@gmail.com', 'TP. Hồ Chí Minh', 'CNTT Giao thông UTH', 'default.png', 1),

-- Phòng 2: A102 (4 sinh viên Nam - Full)
(4, 5, 'SV2026004', 'Phạm Minh Dũng', 'Nam', '2004-02-14', '0977889900', 'phamminhd@gmail.com', 'Bình Dương', 'Logistics UTH', 'default.png', 2),
(6, 7, 'SV2026006', 'Hoàng Văn Phúc', 'Nam', '2004-01-25', '0944332211', 'hoangvanphuc@gmail.com', 'Long An', 'Kỹ thuật Xây dựng GTVT', 'default.png', 2),
(8, 9, 'SV2026008', 'Đỗ Quang Huy', 'Nam', '2004-07-22', '0933221100', 'doquanghuy@gmail.com', 'Hải Phòng', 'Khoa học Máy tính UTH', 'default.png', 2),
(9, 10, 'SV2026009', 'Lê Văn Hải', 'Nam', '2004-10-10', '0955667788', 'levanhai@gmail.com', 'Nghệ An', 'Kỹ thuật Ô tô UTH', 'default.png', 2),

-- Phòng 8: A103 (4 sinh viên Nam)
(12, NULL, 'SV2026012', 'Võ Thành Nam', 'Nam', '2004-06-20', '0968123456', 'vothanhnam@gmail.com', 'Quảng Ngãi', 'Khai thác Vận tải UTH', 'default.png', 8),
(13, NULL, 'SV2026013', 'Đặng Quốc Bảo', 'Nam', '2003-09-14', '0978654321', 'dangquocbao@gmail.com', 'Khánh Hòa', 'Kinh tế Xây dựng UTH', 'default.png', 8),
(37, NULL, 'SV2026037', 'Phan Minh Triết', 'Nam', '2004-08-22', '0914223344', 'phanminhtriet@gmail.com', 'Bình Dương', 'Kỹ thuật Ô tô UTH', 'default.png', 8),
(38, NULL, 'SV2026038', 'Đặng Hữu Phước', 'Nam', '2003-11-30', '0925334455', 'danghuuphuoc@gmail.com', 'Đồng Nai', 'Khai thác Vận tải UTH', 'default.png', 8),

-- Phòng 9: A201 (4 sinh viên Nam - Full)
(14, NULL, 'SV2026014', 'Phan Đình Trọng', 'Nam', '2004-08-30', '0989112244', 'phandinhtrong@gmail.com', 'Thừa Thiên Huế', 'CNTT Giao thông UTH', 'default.png', 9),
(15, NULL, 'SV2026015', 'Nguyễn Hữu Thắng', 'Nam', '2004-11-12', '0903334455', 'nguyenhuuthang@gmail.com', 'Lâm Đồng', 'Logistics UTH', 'default.png', 9),
(16, NULL, 'SV2026016', 'Trịnh Gia Huy', 'Nam', '2004-12-25', '0918776655', 'trinhgiahuy@gmail.com', 'Đắk Lắk', 'Kỹ thuật Cơ khí UTH', 'default.png', 9),
(17, NULL, 'SV2026017', 'Lâm Tấn Phát', 'Nam', '2004-03-08', '0948998877', 'lamtanphat@gmail.com', 'Tiền Giang', 'Khoa học Hàng hải UTH', 'default.png', 9),

-- Phòng 10: A202 (2 sinh viên Nam - Full)
(18, NULL, 'SV2026018', 'Mai Đức Chung', 'Nam', '2003-05-19', '0932445566', 'maiducchung@gmail.com', 'Bà Rịa - Vũng Tàu', 'Kỹ thuật Cầu đường UTH', 'default.png', 10),
(39, NULL, 'SV2026039', 'Hồ Văn Cường', 'Nam', '2004-02-18', '0936445566', 'hovancuong@gmail.com', 'Tiền Giang', 'Logistics UTH', 'default.png', 10),

-- Phòng 11: A203 (3 sinh viên Nam)
(40, NULL, 'SV2026040', 'Vũ Trọng Phụng', 'Nam', '2004-07-09', '0947556677', 'vutrongphung@gmail.com', 'Bến Tre', 'Khoa học Máy tính UTH', 'default.png', 11),
(41, NULL, 'SV2026041', 'Trương Gia Bình', 'Nam', '2004-12-03', '0958667788', 'truonggiabinh@gmail.com', 'Long An', 'Kỹ thuật Điện UTH', 'default.png', 11),
(42, NULL, 'SV2026042', 'Bùi Quang Dũng', 'Nam', '2003-09-25', '0969778899', 'buiquangdung@gmail.com', 'Vĩnh Long', 'Kỹ thuật Xây dựng GTVT', 'default.png', 11),

-- Phòng 20: A302 (1 sinh viên Nam - Còn 1 chỗ)
(33, NULL, 'SV2026033', 'Đoàn Văn Hậu', 'Nam', '2004-04-19', '0983112200', 'doanvanhau@gmail.com', 'Thái Bình', 'Kỹ thuật Cầu đường UTH', 'default.png', 20),

-- Phòng 12: B101 (4 sinh viên Nam)
(19, NULL, 'SV2026019', 'Dương Nhật Minh', 'Nam', '2004-07-07', '0922334455', 'duongnhatminh@gmail.com', 'Vĩnh Long', 'Kỹ thuật Ô tô UTH', 'default.png', 12),
(20, NULL, 'SV2026020', 'Cao Văn Sơn', 'Nam', '2004-02-28', '0981223399', 'caovanson@gmail.com', 'An Giang', 'Khai thác Vận tải UTH', 'default.png', 12),
(21, NULL, 'SV2026021', 'Hà Minh Trí', 'Nam', '2004-10-02', '0973445511', 'haminhtri@gmail.com', 'Đồng Tháp', 'CNTT Giao thông UTH', 'default.png', 12),
(43, NULL, 'SV2026043', 'Ngô Đình Nam', 'Nam', '2004-04-12', '0971889900', 'ngodinhnam@gmail.com', 'Cần Thơ', 'Kỹ thuật Cầu đường UTH', 'default.png', 12),

-- Phòng 13: B102 (2 sinh viên Nam)
(44, NULL, 'SV2026044', 'Đỗ Thành Đạt', 'Nam', '2004-10-15', '0982990011', 'dothanhdat@gmail.com', 'An Giang', 'Kinh tế Vận tải UTH', 'default.png', 13),
(45, NULL, 'SV2026045', 'Dương Văn Hùng', 'Nam', '2004-01-29', '0904113355', 'duongvanhung@gmail.com', 'Đồng Tháp', 'Khoa học Hàng hải UTH', 'default.png', 13),

-- Phòng 3: B201 (4 sinh viên Nam)
(3, 4, 'SV2026003', 'Đỗ Minh Khang', 'Nam', '2003-11-03', '0933445566', 'dominhkhang@gmail.com', 'Cần Thơ', 'Kỹ thuật Ô tô UTH', 'default.png', 3),
(10, NULL, 'SV2026010', 'Nguyễn Tuấn Anh', 'Nam', '2004-12-05', '0988776655', 'nguyentuananh@gmail.com', 'Thanh Hóa', 'Khai thác Vận tải UTH', 'default.png', 3),
(46, NULL, 'SV2026046', 'Lâm Hoàng Quân', 'Nam', '2004-06-08', '0915224466', 'lamhoangquan@gmail.com', 'Kiên Giang', 'Kỹ thuật Cơ khí UTH', 'default.png', 3),
(47, NULL, 'SV2026047', 'Võ Tấn Tài', 'Nam', '2004-03-17', '0926335577', 'votantai@gmail.com', 'Hậu Giang', 'CNTT Giao thông UTH', 'default.png', 3),

-- Phòng 21: B204 (5 sinh viên Nam - Gần đầy, còn 1 chỗ)
(48, NULL, 'SV2026048', 'Nguyễn Duy Mạnh', 'Nam', '2004-11-21', '0937446688', 'nguyenduymanh@gmail.com', 'Sóc Trăng', 'Kỹ thuật Ô tô UTH', 'default.png', 21),
(49, NULL, 'SV2026049', 'Trần Quốc Toản', 'Nam', '2003-08-14', '0948557799', 'tranquoctoan@gmail.com', 'Bạc Liêu', 'Logistics UTH', 'default.png', 21),
(50, NULL, 'SV2026050', 'Lê Hữu Trác', 'Nam', '2004-05-27', '0959668800', 'lehuutrac@gmail.com', 'Cà Mau', 'Kỹ thuật Điện UTH', 'default.png', 21),
(51, NULL, 'SV2026051', 'Phạm Văn Đồng', 'Nam', '2004-09-03', '0961779911', 'phamvandong@gmail.com', 'Tây Ninh', 'Khai thác Vận tải UTH', 'default.png', 21),
(52, NULL, 'SV2026052', 'Hoàng Xuân Vinh', 'Nam', '2004-02-05', '0972880022', 'hoangxuanvinh@gmail.com', 'Bình Phước', 'Khoa học Máy tính UTH', 'default.png', 21),

-- Phòng 14: B301 (2 sinh viên Nam - Full)
(22, NULL, 'SV2026022', 'Đinh Trọng Nhân', 'Nam', '2004-04-05', '0967554422', 'dinhtrongnhan@gmail.com', 'Tây Ninh', 'Logistics UTH', 'default.png', 14),
(23, NULL, 'SV2026023', 'Vũ Hoàng Long', 'Nam', '2004-09-18', '0943221188', 'vuhoanglong@gmail.com', 'Bình Thuận', 'Kỹ thuật Điện UTH', 'default.png', 14),

-- Phòng 15: C101 (5 sinh viên Nữ - Gần đầy, còn 1 chỗ)
(24, NULL, 'SV2026024', 'Lê Thị Thu Hà', 'Nữ', '2004-01-16', '0908112233', 'lethithuha@gmail.com', 'Bình Định', 'Kinh tế Vận tải UTH', 'default.png', 15),
(25, NULL, 'SV2026025', 'Phạm Quỳnh Nga', 'Nữ', '2004-06-28', '0919445566', 'phamquynhnga@gmail.com', 'Gia Lai', 'Khai thác Vận tải UTH', 'default.png', 15),
(26, NULL, 'SV2026026', 'Ngô Phương Thảo', 'Nữ', '2004-11-09', '0979887766', 'ngophuongthao@gmail.com', 'Quảng Nam', 'CNTT Giao thông UTH', 'default.png', 15),
(54, NULL, 'SV2026054', 'Huỳnh Như Quỳnh', 'Nữ', '2004-03-08', '0905224488', 'huynhnhuquynh@gmail.com', 'TP. Hồ Chí Minh', 'Kinh tế Vận tải UTH', 'default.png', 15),
(55, NULL, 'SV2026055', 'Phan Thị Thanh Nga', 'Nữ', '2004-06-12', '0916335599', 'phanthithanhnga@gmail.com', 'Bình Định', 'Khai thác Vận tải UTH', 'default.png', 15),

-- Phòng 16: C102 (3 sinh viên Nữ - Còn 1 chỗ)
(56, NULL, 'SV2026056', 'Đặng Thu Thảo', 'Nữ', '2004-10-20', '0927446600', 'dangthuthao@gmail.com', 'Phú Yên', 'Logistics UTH', 'default.png', 16),
(57, NULL, 'SV2026057', 'Hồ Ngọc Hà', 'Nữ', '2003-12-15', '0938557711', 'hongocha@gmail.com', 'Khánh Hòa', 'CNTT Giao thông UTH', 'default.png', 16),
(58, NULL, 'SV2026058', 'Vũ Thùy Linh', 'Nữ', '2004-04-01', '0949668822', 'vuthuylinh@gmail.com', 'Ninh Thuận', 'Khoa học Hàng hải UTH', 'default.png', 16),

-- Phòng 17: C201 (2 sinh viên Nữ - Full)
(27, NULL, 'SV2026027', 'Đào Mai Linh', 'Nữ', '2004-08-14', '0982334411', 'daomailinh@gmail.com', 'Phú Yên', 'Logistics UTH', 'default.png', 17),
(28, NULL, 'SV2026028', 'Hoàng Yến Nhi', 'Nữ', '2004-03-22', '0937665544', 'hoangyennhi@gmail.com', 'Ninh Thuận', 'Khoa học Máy tính UTH', 'default.png', 17),

-- Phòng 18: C202 (3 sinh viên Nữ - Còn 1 chỗ)
(29, NULL, 'SV2026029', 'Trương Mỹ Duyên', 'Nữ', '2004-05-30', '0945112299', 'truongmyduyen@gmail.com', 'Bến Tre', 'Kinh tế Vận tải UTH', 'default.png', 18),
(59, NULL, 'SV2026059', 'Trương Bảo Anh', 'Nữ', '2004-09-09', '0951779933', 'truongbaoanh@gmail.com', 'Bình Thuận', 'Kinh tế Vận tải UTH', 'default.png', 18),
(60, NULL, 'SV2026060', 'Bùi Bích Phương', 'Nữ', '2004-01-24', '0962880044', 'buibichphuong@gmail.com', 'Lâm Đồng', 'Logistics UTH', 'default.png', 18),

-- Phòng 6: C301 (2 sinh viên Nữ - Full)
(2, 3, 'SV2026002', 'Trần Thị Bình', 'Nữ', '2004-08-20', '0987654321', 'tranthib@gmail.com', 'Đà Nẵng', 'Khai thác Vận tải UTH', 'default.png', 6),
(30, NULL, 'SV2026030', 'Nguyễn Thảo My', 'Nữ', '2004-12-11', '0961889922', 'nguyenthaomy@gmail.com', 'Kiên Giang', 'CNTT Giao thông UTH', 'default.png', 6),

-- Phòng 7: C302 (4 sinh viên Nữ - Full)
(5, 6, 'SV2026005', 'Vũ Thị Ngọc E', 'Nữ', '2004-09-09', '0966554433', 'vuthingoce@gmail.com', 'Đồng Nai', 'Kinh tế Vận tải UTH', 'default.png', 7),
(61, NULL, 'SV2026061', 'Ngô Thanh Vân', 'Nữ', '2004-05-18', '0973991155', 'ngothanhvan@gmail.com', 'Đắk Lắk', 'Khai thác Vận tải UTH', 'default.png', 7),
(62, NULL, 'SV2026062', 'Đỗ Mỹ Linh', 'Nữ', '2004-08-31', '0984002266', 'domylinh@gmail.com', 'Gia Lai', 'Kinh tế Xây dựng UTH', 'default.png', 7),
(63, NULL, 'SV2026063', 'Dương Tú Anh', 'Nữ', '2003-11-10', '0906336699', 'duongtuanh@gmail.com', 'Kon Tum', 'CNTT Giao thông UTH', 'default.png', 7),

-- Phòng 24: C303 (4 sinh viên Nữ - Full)
(64, NULL, 'SV2026064', 'Lâm Khánh Chi', 'Nữ', '2004-02-23', '0917447700', 'lamkhanhchi@gmail.com', 'Quảng Ngãi', 'Logistics UTH', 'default.png', 24),
(65, NULL, 'SV2026065', 'Võ Hạ Trâm', 'Nữ', '2004-07-04', '0928558811', 'vohatram@gmail.com', 'Quảng Nam', 'Khoa học Máy tính UTH', 'default.png', 24),
(66, NULL, 'SV2026066', 'Nguyễn Phương Anh', 'Nữ', '2004-12-19', '0939669922', 'nguyenphuonganh@gmail.com', 'Thừa Thiên Huế', 'Kỹ thuật Môi trường UTH', 'default.png', 24),
(67, NULL, 'SV2026067', 'Trần Tiểu Vy', 'Nữ', '2004-03-28', '0941770033', 'trantieuvy@gmail.com', 'Quảng Trị', 'Kinh tế Vận tải UTH', 'default.png', 24),

-- 8 Sinh viên chưa xếp phòng (room_id = NULL)
(31, NULL, 'SV2026031', 'Tạ Quang Đạt', 'Nam', '2004-02-17', '0909554433', 'taquangdat@gmail.com', 'Hà Tĩnh', 'Kỹ thuật Ô tô UTH', 'default.png', NULL),
(32, NULL, 'SV2026032', 'Lý Gia Hân', 'Nữ', '2004-07-21', '0912887766', 'lygiahan@gmail.com', 'Sóc Trăng', 'Logistics UTH', 'default.png', NULL),
(34, NULL, 'SV2026034', 'Chu Bảo Ngọc', 'Nữ', '2004-10-05', '0931223377', 'chubaongoc@gmail.com', 'Bắc Ninh', 'Kinh tế Xây dựng UTH', 'default.png', NULL),
(35, NULL, 'SV2026035', 'Lương Gia Bảo', 'Nam', '2004-09-27', '0947665511', 'luonggiabao@gmail.com', 'Cà Mau', 'Khoa học Hàng hải UTH', 'default.png', NULL),
(53, NULL, 'SV2026053', 'Đinh Tiên Hoàng', 'Nam', '2004-07-19', '0983991133', 'dinhtienhoang@gmail.com', 'Bà Rịa - Vũng Tàu', 'Kinh tế Xây dựng UTH', 'default.png', NULL),
(68, NULL, 'SV2026068', 'Lê Âu Ngân Anh', 'Nữ', '2004-10-07', '0952881144', 'leaungananh@gmail.com', 'Quảng Bình', 'Khai thác Vận tải UTH', 'default.png', NULL),
(69, NULL, 'SV2026069', 'Phạm Hương Tràm', 'Nữ', '2003-06-16', '0963992255', 'phamhuongtram@gmail.com', 'Hà Tĩnh', 'Logistics UTH', 'default.png', NULL),
(70, NULL, 'SV2026070', 'Hoàng Thùy Linh', 'Nữ', '2004-08-02', '0974003366', 'hoangthuylinh@gmail.com', 'Nghệ An', 'CNTT Giao thông UTH', 'default.png', NULL)
ON DUPLICATE KEY UPDATE 
    student_code=VALUES(student_code), fullname=VALUES(fullname), gender=VALUES(gender), 
    dob=VALUES(dob), phone=VALUES(phone), email=VALUES(email), 
    address=VALUES(address), faculty=VALUES(faculty), room_id=VALUES(room_id);

-- ========================================================
-- DỮ LIỆU MẪU: BẢNG CONTRACTS (Hợp đồng mẫu)
-- Đầy đủ: Active, Sắp hết hạn (7 ngày), Expired, Cancelled
-- ========================================================
INSERT INTO `contracts` (`id`, `student_id`, `room_id`, `start_date`, `end_date`, `deposit`, `status`) VALUES
-- 1. Hợp đồng Active dài hạn
(1, 1, 1, '2026-01-01', '2026-12-31', 600000.00, 'Active'),
-- 2. Hợp đồng sắp hết hạn trong 6 ngày (Cảnh báo Dashboard)
(2, 2, 6, '2026-01-01', '2026-09-15', 1200000.00, 'Active'),
-- 3. Hợp đồng sắp hết hạn trong 5 ngày (Cảnh báo Dashboard)
(3, 3, 3, '2026-02-15', '2026-09-14', 450000.00, 'Active'),
-- 4. Hợp đồng Active
(4, 4, 2, '2026-01-10', '2026-12-31', 850000.00, 'Active'),
-- 5. Hợp đồng cũ đã hết hạn
(5, 5, 6, '2025-01-01', '2026-01-01', 1200000.00, 'Expired'),
-- 6. Hợp đồng HD-6 (Khớp kịch bản: Vũ Thị Ngọc E - Phòng C302)
(6, 5, 7, '2026-09-08', '2027-09-08', 700000.00, 'Active'),
-- 7 đến 10: Tòa A
(7, 6, 2, '2026-01-15', '2026-12-31', 850000.00, 'Active'),
(8, 7, 1, '2026-02-01', '2027-01-31', 600000.00, 'Active'),
(9, 8, 2, '2026-01-10', '2026-12-31', 850000.00, 'Active'),
(10, 9, 2, '2026-01-10', '2026-12-31', 850000.00, 'Active'),
-- 11. Hợp đồng sắp hết hạn trong 7 ngày
(11, 10, 3, '2026-02-01', '2026-09-16', 450000.00, 'Active'),
-- 12 đến 20: Hợp đồng sinh viên các phòng khác
(12, 11, 1, '2026-03-01', '2027-02-28', 600000.00, 'Active'),
(13, 12, 8, '2026-01-15', '2026-12-31', 450000.00, 'Active'),
(14, 13, 8, '2026-02-01', '2027-01-31', 450000.00, 'Active'),
(15, 14, 9, '2026-01-01', '2026-12-31', 600000.00, 'Active'),
(16, 15, 9, '2026-01-01', '2026-12-31', 600000.00, 'Active'),
(17, 16, 9, '2026-02-15', '2027-02-14', 600000.00, 'Active'),
(18, 17, 9, '2026-03-01', '2027-02-28', 600000.00, 'Active'),
(19, 18, 10, '2026-01-10', '2026-12-31', 1100000.00, 'Active'),
(20, 19, 12, '2026-01-01', '2026-12-31', 450000.00, 'Active'),
(21, 20, 12, '2026-02-01', '2027-01-31', 450000.00, 'Active'),
(22, 22, 14, '2026-01-05', '2026-12-31', 1000000.00, 'Active'),
-- 23, 24: Hợp đồng đã hết hạn
(23, 24, 15, '2025-02-01', '2026-02-01', 450000.00, 'Expired'),
(24, 27, 17, '2025-03-01', '2026-03-01', 1200000.00, 'Expired'),
-- 25: Hợp đồng đã hủy
(25, 30, 7, '2026-01-01', '2026-06-30', 700000.00, 'Cancelled'),

-- Hợp đồng cho các sinh viên tiếp theo
(26, 36, 1, '2026-09-01', '2027-08-31', 600000.00, 'Active'),
(27, 37, 8, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(28, 38, 8, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(29, 39, 10, '2026-09-01', '2027-08-31', 1100000.00, 'Active'),
(30, 40, 11, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(31, 41, 11, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(32, 42, 11, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(33, 43, 12, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(34, 44, 13, '2026-09-01', '2027-08-31', 600000.00, 'Active'),
(35, 45, 13, '2026-09-01', '2027-08-31', 600000.00, 'Active'),
(36, 46, 3, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(37, 47, 3, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(38, 48, 21, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(39, 49, 21, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(40, 50, 21, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(41, 51, 21, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(42, 52, 21, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(43, 54, 15, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(44, 55, 15, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(45, 56, 16, '2026-09-01', '2027-08-31', 700000.00, 'Active'),
(46, 57, 16, '2026-09-01', '2027-08-31', 700000.00, 'Active'),
(47, 58, 16, '2026-09-01', '2027-08-31', 700000.00, 'Active'),
(48, 59, 18, '2026-09-01', '2027-08-31', 700000.00, 'Active'),
(49, 60, 18, '2026-09-01', '2027-08-31', 700000.00, 'Active'),
(50, 61, 7, '2026-09-01', '2027-08-31', 700000.00, 'Active'),
(51, 62, 7, '2026-09-01', '2027-08-31', 700000.00, 'Active'),
(52, 63, 7, '2026-09-01', '2027-08-31', 700000.00, 'Active'),
(53, 64, 24, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(54, 65, 24, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(55, 66, 24, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(56, 67, 24, '2026-09-01', '2027-08-31', 450000.00, 'Active'),
(57, 33, 20, '2026-09-01', '2027-08-31', 1100000.00, 'Active')
ON DUPLICATE KEY UPDATE 
    student_id=VALUES(student_id), room_id=VALUES(room_id),
    start_date=VALUES(start_date), end_date=VALUES(end_date),
    deposit=VALUES(deposit), status=VALUES(status);

-- ========================================================
-- DỮ LIỆU MẪU: BẢNG INVOICES (Hóa đơn phòng)
-- ========================================================
INSERT INTO `invoices` (`id`, `invoice_code`, `room_id`, `billing_month`, `room_fee`, `electricity_fee`, `water_fee`, `total_amount`, `status`, `created_at`, `paid_at`) VALUES
(1, 'INV-202608-A101', 1, '08/2026', 600000.00, 180000.00, 70000.00, 850000.00, 'Unpaid', NOW(), NULL),
(2, 'INV-202608-A102', 2, '08/2026', 850000.00, 240000.00, 90000.00, 1180000.00, 'Paid', NOW(), NOW()),
(3, 'INV-202608-B201', 3, '08/2026', 450000.00, 120000.00, 50000.00, 620000.00, 'Unpaid', NOW(), NULL),
(4, 'INV-202608-C301', 6, '08/2026', 1200000.00, 200000.00, 100000.00, 1500000.00, 'Unpaid', NOW(), NULL),
(5, 'INV-202608-C302', 7, '08/2026', 700000.00, 150000.00, 60000.00, 910000.00, 'Paid', NOW(), NOW()),
(6, 'INV-202608-B301', 14, '08/2026', 1000000.00, 220000.00, 80000.00, 1300000.00, 'Unpaid', NOW(), NULL)
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- ========================================================
-- DỮ LIỆU MẪU: BẢNG ROOM_REQUESTS (Yêu cầu chuyển phòng)
-- ========================================================
INSERT INTO `room_requests` (`id`, `student_id`, `current_room_id`, `requested_room_id`, `request_type`, `reason`, `status`, `created_at`) VALUES
(1, 1, 1, 3, 'transfer', 'Muốn ở tầng 2 Tòa B gần bạn học cùng lớp.', 'Pending', NOW()),
(2, 3, 3, 4, 'transfer', 'Phòng B201 khá ồn, muốn chuyển sang phòng B202 rộng hơn.', 'Pending', NOW()),
(3, 11, 1, 8, 'transfer', 'Muốn chuyển sang phòng thường để tiết kiệm chi phí sinh hoạt.', 'Pending', NOW())
ON DUPLICATE KEY UPDATE status=VALUES(status);

SET FOREIGN_KEY_CHECKS = 1;
