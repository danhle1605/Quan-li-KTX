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

-- 6. Bảng Yêu cầu Chuyển/Đăng ký phòng (Điểm sáng tạo 3)
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

-- Mật khẩu mặc định cho các tài khoản: password123 (hash bcrypt) hoặc '123'
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`) VALUES
(1, 'admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1q.uGq928uX/aL6jD3fM8dM6c9nC4y.', 'Quản Trị Viên KTX UTH', 'admin@uth.edu.vn', 'admin'),
(2, 'sv2026001', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1q.uGq928uX/aL6jD3fM8dM6c9nC4y.', 'Nguyễn Văn An', 'nguyenvana@gmail.com', 'student'),
(3, 'sv2026002', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1q.uGq928uX/aL6jD3fM8dM6c9nC4y.', 'Trần Thị Bình', 'tranthib@gmail.com', 'student'),
(4, 'sv2026003', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1q.uGq928uX/aL6jD3fM8dM6c9nC4y.', 'Đỗ Minh Khang', 'lehoangc@gmail.com', 'student'),
(5, 'sv2026004', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1q.uGq928uX/aL6jD3fM8dM6c9nC4y.', 'Phạm Minh Dũng', 'phamminhd@gmail.com', 'student')
ON DUPLICATE KEY UPDATE `fullname`=VALUES(`fullname`);

-- Chèn danh sách các phòng KTX UTH
INSERT INTO `rooms` (`id`, `room_number`, `building`, `floor`, `room_type`, `capacity`, `occupied`, `price`, `status`, `description`) VALUES
(1, 'A101', 'Tòa A (Nam)', 1, 'Máy lạnh', 4, 2, 600000.00, 'Available', 'Phòng máy lạnh Tòa A, 4 giường tầng cao cấp, quạt treo tường, bàn học cá nhân.'),
(2, 'A102', 'Tòa A (Nam)', 1, 'VIP', 4, 4, 850000.00, 'Full', 'Phòng VIP UTH ban công rộng rãi, máy lạnh Inverter, tủ lạnh riêng, vệ sinh khép kín.'),
(3, 'B201', 'Tòa B (Nam)', 2, 'Thường', 6, 2, 450000.00, 'Available', 'Phòng tiêu chuẩn Tòa B 6 giường quạt trần khép kín, sạch sẽ, thoáng mát, chi phí tiết kiệm.'),
(4, 'B202', 'Tòa B (Nam)', 2, 'Thường', 6, 0, 450000.00, 'Available', 'Phòng trống hoàn toàn (chưa có sinh viên ở), vừa dọn dẹp và sơn sửa dọn đồ sẵn sàng ở ngay.'),
(5, 'B203', 'Tòa B (Nam)', 2, 'Máy lạnh', 4, 0, 600000.00, 'Maintenance', 'Phòng đang bảo trì thiết bị máy lạnh và sơn lại tường, không cho đăng ký trong tuần này.'),
(6, 'C301', 'Tòa C (Nữ)', 3, 'VIP', 2, 2, 1200000.00, 'Full', 'Phòng VIP 2 giường cho nữ UTH, trang bị máy giặt riêng, tủ quần áo gỗ, khu an ninh.'),
(7, 'C302', 'Tòa C (Nữ)', 3, 'Máy lạnh', 4, 0, 700000.00, 'Available', 'Phòng nữ trống hoàn toàn (chưa có sinh viên ở), khép kín đầy đủ tiện nghi.')
ON DUPLICATE KEY UPDATE 
    room_number=VALUES(room_number), building=VALUES(building), floor=VALUES(floor),
    room_type=VALUES(room_type), capacity=VALUES(capacity), price=VALUES(price),
    status=VALUES(status), description=VALUES(description);

-- Chèn danh sách Sinh viên KTX UTH
INSERT INTO `students` (`id`, `user_id`, `student_code`, `fullname`, `gender`, `dob`, `phone`, `email`, `address`, `faculty`, `avatar`, `room_id`) VALUES
(1, 2, 'SV2026001', 'Nguyễn Văn An', 'Nam', '2004-05-12', '0912345678', 'nguyenvana@gmail.com', 'TP. Hồ Chí Minh', 'CNTT Giao thông UTH', 'default.png', 1),
(2, 3, 'SV2026002', 'Trần Thị Bình', 'Nữ', '2004-08-20', '0987654321', 'tranthib@gmail.com', 'Đà Nẵng', 'Khai thác Vận tải UTH', 'default.png', 6),
(3, 4, 'SV2026003', 'Đỗ Minh Khang', 'Nam', '2003-11-03', '0933445566', 'lehoangc@gmail.com', 'Cần Thơ', 'Kỹ thuật Ô tô UTH', 'default.png', 3),
(4, 5, 'SV2026004', 'Phạm Minh Dũng', 'Nam', '2004-02-14', '0977889900', 'phamminhd@gmail.com', 'Bình Dương', 'Logistics UTH', 'default.png', 2),
(5, NULL, 'SV2026005', 'Vũ Thị Ngọc E', 'Nữ', '2004-09-09', '0966554433', 'vuthingoce@gmail.com', 'Đồng Nai', 'Kinh tế Vận tải UTH', 'default.png', 6),
(6, NULL, 'SV2026006', 'Hoàng Văn F', 'Nam', '2004-01-25', '0944332211', 'hoangvanf@gmail.com', 'Long An', 'Kỹ thuật Xây dựng GTVT', 'default.png', 2),
(7, NULL, 'SV2026007', 'Trần Minh Đức', 'Nam', '2004-03-15', '0911223344', 'tranminhduc@gmail.com', 'Hà Nội', 'Kỹ thuật Điện UTH', 'default.png', 1),
(8, NULL, 'SV2026008', 'Đỗ Quang Huy', 'Nam', '2004-07-22', '0933221100', 'doquanghuy@gmail.com', 'Hải Phòng', 'Khoa học Máy tính UTH', 'default.png', 2),
(9, NULL, 'SV2026009', 'Lê Văn Hải', 'Nam', '2004-10-10', '0955667788', 'levanhai@gmail.com', 'Nghệ An', 'Kỹ thuật Ô tô UTH', 'default.png', 2),
(10, NULL, 'SV2026010', 'Nguyễn Tuấn Anh', 'Nam', '2004-12-05', '0988776655', 'nguyentuananh@gmail.com', 'Thanh Hóa', 'Khai thác Vận tải UTH', 'default.png', 3)
ON DUPLICATE KEY UPDATE fullname=VALUES(fullname), room_id=VALUES(room_id), faculty=VALUES(faculty), phone=VALUES(phone);

-- Chèn Hợp đồng (Gồm Hợp đồng Active, Expired, Expiring in 7 days)
INSERT INTO `contracts` (`id`, `student_id`, `room_id`, `start_date`, `end_date`, `deposit`, `status`) VALUES
(1, 1, 1, '2026-01-01', '2026-12-31', 600000.00, 'Active'),
(2, 2, 6, '2026-01-01', '2026-08-23', 1200000.00, 'Active'),
(3, 3, 3, '2026-02-15', '2026-08-25', 450000.00, 'Active'),
(4, 4, 2, '2026-01-10', '2026-12-31', 850000.00, 'Active'),
(5, 5, 6, '2025-01-01', '2026-01-01', 1200000.00, 'Expired')
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- Chèn Hóa đơn
INSERT INTO `invoices` (`id`, `invoice_code`, `room_id`, `billing_month`, `room_fee`, `electricity_fee`, `water_fee`, `total_amount`, `status`, `created_at`, `paid_at`) VALUES
(1, 'INV-202608-A101', 1, '08/2026', 600000.00, 180000.00, 70000.00, 850000.00, 'Unpaid', NOW(), NULL),
(2, 'INV-202608-A102', 2, '08/2026', 850000.00, 240000.00, 90000.00, 1180000.00, 'Paid', NOW(), NOW()),
(3, 'INV-202608-B201', 3, '08/2026', 450000.00, 120000.00, 50000.00, 620000.00, 'Unpaid', NOW(), NULL),
(4, 'INV-202608-C301', 6, '08/2026', 1200000.00, 200000.00, 100000.00, 1500000.00, 'Unpaid', NOW(), NULL)
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- Chèn Dữ liệu mẫu Yêu cầu chuyển phòng (Điểm sáng tạo 3)
INSERT INTO `room_requests` (`id`, `student_id`, `current_room_id`, `requested_room_id`, `request_type`, `reason`, `status`, `created_at`) VALUES
(1, 1, 1, 3, 'transfer', 'Muốn ở tầng 2 Tòa B gần bạn học cùng lớp.', 'Pending', NOW()),
(2, 3, 3, 4, 'transfer', 'Phòng B201 khá ồn, muốn chuyển sang phòng B202 rộng hơn.', 'Pending', NOW())
ON DUPLICATE KEY UPDATE status=VALUES(status);

SET FOREIGN_KEY_CHECKS = 1;
