<?php
/**
 * FIX UTF-8 TOÀN DIỆN - KTX UTH
 * Truy cập: http://localhost:8080/fix_utf8.php
 * XÓA FILE NÀY SAU KHI CHẠY XONG!
 */

define('APPROOT', dirname(__DIR__) . '/app');
require_once APPROOT . '/config/Database.php';

header('Content-Type: text/html; charset=UTF-8');

$logs = [];
$errors = [];

try {
    $dsn = "mysql:host=" . DatabaseConfig::getHost() . ";dbname=" . DatabaseConfig::getName();
    $pdo = new PDO($dsn, DatabaseConfig::getUser(), DatabaseConfig::getPass(), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true,
    ]);

    // Bước 1: SET NAMES utf8mb4 NGAY ĐẦU TIÊN
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec("SET collation_connection = utf8mb4_unicode_ci");
    $logs[] = "✅ Đã thiết lập kết nối UTF-8MB4";

    // Bước 2: Chuyển database sang utf8mb4
    $pdo->exec("ALTER DATABASE `dormitory_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $logs[] = "✅ Đã đổi charset database sang utf8mb4_unicode_ci";

    // Bước 3: Chuyển tất cả bảng sang utf8mb4
    $tables = ['users', 'rooms', 'students', 'contracts', 'invoices', 'room_requests'];
    foreach ($tables as $tbl) {
        try {
            $pdo->exec("ALTER TABLE `$tbl` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $logs[] = "✅ Đã đổi charset bảng `$tbl` sang utf8mb4";
        } catch (Exception $e) {
            $logs[] = "⚠️ Bảng `$tbl` chưa tồn tại hoặc đã đúng charset";
        }
    }

    // Bước 4: Tắt FK checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Bước 5: XÓA DỮ LIỆU BỊ MÃ HÓA SAI và INSERT LẠI ĐÚNG UTF-8
    // Xóa theo thứ tự đúng FK
    $pdo->exec("DELETE FROM room_requests WHERE id IN (1,2)");
    $pdo->exec("DELETE FROM invoices WHERE id IN (1,2,3,4)");
    $pdo->exec("DELETE FROM contracts WHERE id IN (1,2,3,4,5)");
    $pdo->exec("DELETE FROM students WHERE id BETWEEN 1 AND 20");
    $pdo->exec("DELETE FROM users WHERE id BETWEEN 1 AND 10");
    $pdo->exec("DELETE FROM rooms WHERE id BETWEEN 1 AND 10");
    $logs[] = "✅ Đã xóa dữ liệu cũ bị mã hóa sai";

    // === INSERT USERS ===
    $bcrypt = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1q.uGq928uX/aL6jD3fM8dM6c9nC4y.'; // password123

    $stmtUser = $pdo->prepare(
        "INSERT INTO users (id, username, password, fullname, email, role) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $users = [
        [1, 'admin',     $bcrypt, 'Quản Trị Viên KTX UTH', 'admin@uth.edu.vn',       'admin'],
        [2, 'sv2026001', $bcrypt, 'Nguyễn Văn An',         'nguyenvana@gmail.com',   'student'],
        [3, 'sv2026002', $bcrypt, 'Trần Thị Bình',         'tranthib@gmail.com',     'student'],
        [4, 'sv2026003', $bcrypt, 'Đỗ Minh Khang',         'dominhkhang@gmail.com',  'student'],
        [5, 'sv2026004', $bcrypt, 'Phạm Minh Dũng',        'phamminhd@gmail.com',    'student'],
    ];
    foreach ($users as $u) $stmtUser->execute($u);
    $logs[] = "✅ Đã INSERT " . count($users) . " tài khoản users chuẩn UTF-8";

    // === INSERT ROOMS ===
    $stmtRoom = $pdo->prepare(
        "INSERT INTO rooms (id, room_number, building, floor, room_type, capacity, occupied, price, status, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $rooms = [
        [1, 'A101', 'Tòa A (Nam)', 1, 'Máy lạnh', 4, 2, 600000.00, 'Available',    'Phòng máy lạnh Tòa A, 4 giường tầng cao cấp, quạt treo tường, bàn học cá nhân.'],
        [2, 'A102', 'Tòa A (Nam)', 1, 'VIP',       4, 4, 850000.00, 'Full',         'Phòng VIP UTH ban công rộng rãi, máy lạnh Inverter, tủ lạnh riêng, vệ sinh khép kín.'],
        [3, 'B201', 'Tòa B (Nam)', 2, 'Thường',    6, 2, 450000.00, 'Available',    'Phòng tiêu chuẩn Tòa B 6 giường quạt trần khép kín, sạch sẽ, thoáng mát.'],
        [4, 'B202', 'Tòa B (Nam)', 2, 'Thường',    6, 0, 450000.00, 'Available',    'Phòng trống hoàn toàn, vừa dọn dẹp sơn sửa, sẵn sàng ở ngay.'],
        [5, 'B203', 'Tòa B (Nam)', 2, 'Máy lạnh',  4, 0, 600000.00, 'Maintenance', 'Phòng đang bảo trì thiết bị máy lạnh và sơn lại tường, không cho đăng ký.'],
        [6, 'C301', 'Tòa C (Nữ)', 3, 'VIP',        2, 2, 1200000.00,'Full',        'Phòng VIP 2 giường cho nữ UTH, máy giặt riêng, tủ quần áo gỗ, khu an ninh.'],
        [7, 'C302', 'Tòa C (Nữ)', 3, 'Máy lạnh',   4, 0, 700000.00, 'Available',   'Phòng nữ trống hoàn toàn, khép kín đầy đủ tiện nghi.'],
    ];
    foreach ($rooms as $r) $stmtRoom->execute($r);
    $logs[] = "✅ Đã INSERT " . count($rooms) . " phòng KTX chuẩn UTF-8";

    // === INSERT STUDENTS ===
    $stmtStu = $pdo->prepare(
        "INSERT INTO students (id, user_id, student_code, fullname, gender, dob, phone, email, address, faculty, avatar, room_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'default.png', ?)"
    );
    $students = [
        [1,  2,    'SV2026001', 'Nguyễn Văn An',      'Nam', '2004-05-12', '0912345678', 'nguyenvana@gmail.com',   'TP. Hồ Chí Minh',  'CNTT Giao thông UTH',        1],
        [2,  3,    'SV2026002', 'Trần Thị Bình',       'Nữ',  '2004-08-20', '0987654321', 'tranthib@gmail.com',     'Đà Nẵng',           'Khai thác Vận tải UTH',      6],
        [3,  4,    'SV2026003', 'Đỗ Minh Khang',       'Nam', '2003-11-03', '0933445566', 'dominhkhang@gmail.com',  'Cần Thơ',           'Kỹ thuật Ô tô UTH',          3],
        [4,  5,    'SV2026004', 'Phạm Minh Dũng',      'Nam', '2004-02-14', '0977889900', 'phamminhd@gmail.com',    'Bình Dương',        'Logistics UTH',               2],
        [5,  null, 'SV2026005', 'Vũ Thị Ngọc',         'Nữ',  '2004-09-09', '0966554433', 'vuthingoce@gmail.com',   'Đồng Nai',          'Kinh tế Vận tải UTH',        6],
        [6,  null, 'SV2026006', 'Hoàng Văn Phong',     'Nam', '2004-01-25', '0944332211', 'hoangvanf@gmail.com',    'Long An',           'Kỹ thuật Xây dựng GTVT',     2],
        [7,  null, 'SV2026007', 'Trần Minh Đức',       'Nam', '2004-03-15', '0911223344', 'tranminhduc@gmail.com',  'Hà Nội',            'Kỹ thuật Điện UTH',           1],
        [8,  null, 'SV2026008', 'Đỗ Quang Huy',        'Nam', '2004-07-22', '0933221100', 'doquanghuy@gmail.com',   'Hải Phòng',         'Khoa học Máy tính UTH',      2],
        [9,  null, 'SV2026009', 'Lê Văn Hải',          'Nam', '2004-10-10', '0955667788', 'levanhai@gmail.com',     'Nghệ An',           'Kỹ thuật Ô tô UTH',          2],
        [10, null, 'SV2026010', 'Nguyễn Tuấn Anh',     'Nam', '2004-12-05', '0988776655', 'nguyentuananh@gmail.com','Thanh Hóa',         'Khai thác Vận tải UTH',      3],
    ];
    foreach ($students as $s) $stmtStu->execute($s);
    $logs[] = "✅ Đã INSERT " . count($students) . " sinh viên chuẩn UTF-8";

    // === INSERT CONTRACTS ===
    $stmtCon = $pdo->prepare(
        "INSERT INTO contracts (id, student_id, room_id, start_date, end_date, deposit, status) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $contracts = [
        [1, 1, 1, '2026-01-01', '2026-12-31',  600000.00,  'Active'],
        [2, 2, 6, '2026-01-01', '2026-08-23',  1200000.00, 'Active'],
        [3, 3, 3, '2026-02-15', '2026-08-25',  450000.00,  'Active'],
        [4, 4, 2, '2026-01-10', '2026-12-31',  850000.00,  'Active'],
        [5, 5, 6, '2025-01-01', '2026-01-01',  1200000.00, 'Expired'],
    ];
    foreach ($contracts as $c) $stmtCon->execute($c);
    $logs[] = "✅ Đã INSERT " . count($contracts) . " hợp đồng";

    // === INSERT INVOICES ===
    $pdo->exec("INSERT INTO invoices (id, invoice_code, room_id, billing_month, room_fee, electricity_fee, water_fee, total_amount, status, created_at, paid_at) VALUES
        (1, 'INV-202608-A101', 1, '08/2026', 600000, 180000, 70000,  850000,  'Unpaid', NOW(), NULL),
        (2, 'INV-202608-A102', 2, '08/2026', 850000, 240000, 90000,  1180000, 'Paid',   NOW(), NOW()),
        (3, 'INV-202608-B201', 3, '08/2026', 450000, 120000, 50000,  620000,  'Unpaid', NOW(), NULL),
        (4, 'INV-202608-C301', 6, '08/2026', 1200000,200000, 100000, 1500000, 'Unpaid', NOW(), NULL)");
    $logs[] = "✅ Đã INSERT 4 hóa đơn";

    // === INSERT ROOM REQUESTS ===
    $pdo->exec("INSERT INTO room_requests (id, student_id, current_room_id, requested_room_id, request_type, reason, status, created_at) VALUES
        (1, 1, 1, 3, 'transfer', 'Muốn ở tầng 2 Tòa B gần bạn học cùng lớp.', 'Pending', NOW()),
        (2, 3, 3, 4, 'transfer', 'Phòng B201 khá ồn, muốn chuyển sang phòng B202 rộng hơn.', 'Pending', NOW())");
    $logs[] = "✅ Đã INSERT 2 yêu cầu chuyển phòng";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $logs[] = "✅ Đã bật lại Foreign Key checks";

    // === KIỂM TRA KẾT QUẢ ===
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    $stuResult = $pdo->query("SELECT student_code, fullname, gender, faculty FROM students ORDER BY id LIMIT 10")->fetchAll();
    $userResult = $pdo->query("SELECT username, fullname, role FROM users ORDER BY id")->fetchAll();

} catch (Exception $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix UTF-8 - KTX UTH</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Segoe UI", Arial, sans-serif; background: #f8fafc; color: #1e293b; padding: 30px; }
        h2 { font-size: 1.6rem; margin-bottom: 20px; }
        .success { background: #d1fae5; border: 1px solid #6ee7b7; border-left: 5px solid #10b981; padding: 16px 20px; border-radius: 10px; margin-bottom: 20px; }
        .error { background: #fee2e2; border: 1px solid #fca5a5; border-left: 5px solid #ef4444; padding: 16px 20px; border-radius: 10px; margin-bottom: 20px; }
        .log-item { padding: 5px 0; font-size: 0.9rem; color: #065f46; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin: 20px 0; }
        th { background: #4f46e5; color: white; padding: 12px 16px; text-align: left; font-size: 0.85rem; }
        td { padding: 10px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; }
        tr:last-child td { border-bottom: none; }
        .warn { background: #fef3c7; border: 1px solid #fcd34d; border-left: 5px solid #f59e0b; padding: 14px 18px; border-radius: 8px; margin-top: 20px; font-size: 0.9rem; }
        .btn { display: inline-block; padding: 12px 28px; background: #4f46e5; color: white; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 1rem; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>🛠️ KTX UTH – Fix UTF-8 Tiếng Việt Toàn Diện</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <strong>❌ Có lỗi xảy ra:</strong><br>
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="success">
            <strong>✅ Đã sửa hoàn toàn lỗi UTF-8 Tiếng Việt trong CSDL!</strong>
            <?php foreach ($logs as $log): ?>
                <p class="log-item"><?= $log ?></p>
            <?php endforeach; ?>
        </div>

        <h3 style="margin: 20px 0 10px;">📋 Kết quả: Tài khoản Users</h3>
        <table>
            <tr><th>Username</th><th>Họ và Tên</th><th>Vai trò</th></tr>
            <?php foreach ($userResult as $u): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                    <td><?= htmlspecialchars($u['fullname']) ?></td>
                    <td><?= $u['role'] === 'admin' ? '🔴 Admin' : '🔵 Student' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3 style="margin: 20px 0 10px;">👥 Kết quả: Sinh Viên</h3>
        <table>
            <tr><th>MSSV</th><th>Họ và Tên</th><th>Giới tính</th><th>Khoa</th></tr>
            <?php foreach ($stuResult as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['student_code']) ?></strong></td>
                    <td><?= htmlspecialchars($s['fullname']) ?></td>
                    <td><?= htmlspecialchars($s['gender']) ?></td>
                    <td><?= htmlspecialchars($s['faculty']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="warn">⚠️ <strong>Quan trọng:</strong> Hãy <strong>xóa file <code>fix_utf8.php</code></strong> sau khi xác nhận dữ liệu hiển thị đúng!</div>
        <a href="/dashboard/index" class="btn">← Vào Dashboard xem kết quả →</a>
    <?php endif; ?>
</body>
</html>
