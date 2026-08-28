<?php
/**
 * KTX UTH - Data Seed & Fix Script
 * Truy cập: http://localhost:8080/seed.php
 * Xóa file này sau khi chạy xong!
 */

// Load app config
define('APPROOT', dirname(__DIR__) . '/app');
require_once APPROOT . '/config/Database.php';

try {
    // PDO::ATTR_EMULATE_PREPARES = true để tránh lỗi ENUM khi dùng prepared statements
    $dsn = "mysql:host=" . (DatabaseConfig::getHost()) . ";dbname=" . (DatabaseConfig::getName()) . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DatabaseConfig::getUser(), DatabaseConfig::getPass(), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true  // Bật emulate để tránh lỗi ENUM charset
    ]);
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec("SET collation_connection = utf8mb4_unicode_ci");

    $log = [];

    // ===== 0. Sửa cột gender: đổi ENUM sang VARCHAR(10) utf8mb4 để tránh lỗi Data truncated =====
    $pdo->exec("ALTER TABLE students MODIFY COLUMN gender VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nam'");
    $log[] = "✅ Đã đổi cột 'gender' sang VARCHAR utf8mb4 (fix lỗi Data truncated).";

    // ===== 0b. Đảm bảo bảng students dùng utf8mb4 =====
    $pdo->exec("ALTER TABLE students CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $log[] = "✅ Đã chuyển bảng students sang utf8mb4.";

    // ===== 1. Tắt Foreign Key Check tạm thời =====
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $log[] = "✅ Đã tắt Foreign Key Checks tạm thời.";

    // ===== 2. Xóa dữ liệu cũ sai mã hóa =====
    $pdo->exec("DELETE FROM students WHERE id <= 20");
    $log[] = "✅ Đã xóa sinh viên cũ sai mã hóa.";

    // ===== 2. Nạp lại phòng chuẩn =====
    $pdo->exec("INSERT INTO rooms (id, room_number, building, floor, room_type, capacity, occupied, price, status, description) VALUES
        (1, 'A101', 'Tòa A (Nam)', 1, 'Máy lạnh', 4, 0, 600000.00, 'Available', 'Phòng máy lạnh Tòa A, 4 giường tầng cao cấp, quạt treo tường, bàn học cá nhân.'),
        (2, 'A102', 'Tòa A (Nam)', 1, 'VIP', 4, 0, 850000.00, 'Available', 'Phòng VIP UTH ban công rộng rãi, máy lạnh Inverter, tủ lạnh riêng, vệ sinh khép kín.'),
        (3, 'B201', 'Tòa B (Nam)', 2, 'Thường', 6, 0, 450000.00, 'Available', 'Phòng tiêu chuẩn Tòa B 6 giường quạt trần khép kín, sạch sẽ, thoáng mát, chi phí tiết kiệm.'),
        (4, 'B202', 'Tòa B (Nam)', 2, 'Thường', 6, 0, 450000.00, 'Available', 'Phòng trống hoàn toàn (chưa có sinh viên ở), vừa dọn dẹp và sơn sửa, sẵn sàng ở ngay.'),
        (5, 'B203', 'Tòa B (Nam)', 2, 'Máy lạnh', 4, 0, 600000.00, 'Available', 'Phòng trống trang bị máy lạnh mới, ban công view thoáng mát (phòng trống đồ).'),
        (6, 'C301', 'Tòa C (Nữ)', 3, 'VIP', 2, 0, 1200000.00, 'Available', 'Phòng VIP 2 giường cho nữ UTH, trang bị máy giặt riêng, tủ quần áo gỗ, khu an ninh 24/7.'),
        (7, 'C302', 'Tòa C (Nữ)', 3, 'Máy lạnh', 4, 0, 700000.00, 'Available', 'Phòng nữ trống hoàn toàn (chưa có sinh viên ở), khép kín đầy đủ tiện nghi.')
        ON DUPLICATE KEY UPDATE
            building=VALUES(building), floor=VALUES(floor), room_type=VALUES(room_type),
            capacity=VALUES(capacity), price=VALUES(price), status=VALUES(status), description=VALUES(description)");
    $log[] = "✅ Đã đồng bộ 7 phòng KTX UTH.";

    // ===== 3. Nạp sinh viên chuẩn UTF-8 =====
    $students = [
        // Tất cả user_id = NULL để tránh Foreign Key Constraint với bảng users
        [1, null, 'SV2026001', 'Nguyễn Văn Long',        'Nam', '2004-05-12', '0912345678', 'nguyenvanlong@gmail.com',   'TP. Hồ Chí Minh', 'CNTT Giao thông UTH',         1],
        [2, null, 'SV2026002', 'Trần Ngọc Mai',           'Nữ',  '2004-08-20', '0987654321', 'tranngocmai@gmail.com',     'Đà Nẵng',         'Khai thác Vận tải UTH',       6],
        [3, null, 'SV2026003', 'Lê Văn Nam',              'Nam', '2003-11-03', '0933445566', 'levannam@gmail.com',        'Cần Thơ',         'Khoa Công nghệ thông tin',    1],
        [4, null, 'SV2026004', 'Phạm Minh Khoa',          'Nam', '2004-02-14', '0977889900', 'phamminkhoa@gmail.com',     'Bình Dương',      'Logistics & Vận tải UTH',     2],
        [5, null, 'SV2026005', 'Vũ Thị Hương',            'Nữ',  '2004-09-09', '0966554433', 'vuthihuong@gmail.com',      'Đồng Nai',        'Kinh tế Vận tải UTH',         6],
        [6, null, 'SV2026006', 'Hoàng Quốc Bảo',          'Nam', '2004-01-25', '0944332211', 'hoangquocbao@gmail.com',    'Long An',         'Kỹ thuật Xây dựng GTVT',      2],
        [7, null, 'SV2026007', 'Trần Minh Đức',           'Nam', '2004-03-15', '0911223344', 'tranminhduc@gmail.com',     'Hà Nội',          'Kỹ thuật Điện - Điện tử UTH', 1],
        [8, null, 'SV2026008', 'Đỗ Quang Huy',            'Nam', '2004-07-22', '0933221100', 'doquanghuy@gmail.com',      'Hải Phòng',       'Khoa học Máy tính UTH',       2],
        [9, null, 'SV2026009', 'Lê Văn Hải',              'Nam', '2004-10-10', '0955667788', 'levanhai@gmail.com',        'Nghệ An',         'Kỹ thuật Ô tô UTH',           3],
        [10,null, 'SV2026010', 'Nguyễn Tuấn Anh',         'Nam', '2004-12-05', '0988776655', 'nguyentuananh@gmail.com',   'Thanh Hóa',       'Khai thác Vận tải UTH',       3],
        [11,null, 'SV2026011', 'Võ Minh Thắng',           'Nam', '2004-04-18', '0933112233', 'vominhthang@gmail.com',     'Quảng Nam',       'Kỹ thuật Cơ khí UTH',         1],
        [12,null, 'SV2026012', 'Bùi Đức Tiến',            'Nam', '2004-06-30', '0977112233', 'buiductien@gmail.com',      'Khánh Hòa',       'Điện tử Viễn thông UTH',      2],
        [13,null, 'SV2026013', 'Đặng Hoàng Long',         'Nam', '2004-08-14', '0966223344', 'danghoanglong@gmail.com',   'Tiền Giang',      'Tự động hóa UTH',             3],
        [14,null, 'SV2026014', 'Nguyễn Ngọc Mai',         'Nữ',  '2004-11-20', '0988334455', 'nguyenngocmai@gmail.com',   'Bến Tre',         'Kinh tế Vận tải UTH',         7],
        [15,null, 'SV2026015', 'Phạm Thùy Linh',          'Nữ',  '2004-02-28', '0977445566', 'phamthuylinh@gmail.com',    'Cần Thơ',         'Quản trị Logistics UTH',      7],
        [16,null, 'SV2026016', 'Nguyễn Thị Hoàng Yến',   'Nữ',  '2004-05-05', '0966889900', 'hoangyen@gmail.com',        'Tây Ninh',        'Luật Giao thông UTH',         null],
    ];

    $stmt = $pdo->prepare("INSERT INTO students (id, user_id, student_code, fullname, gender, dob, phone, email, address, faculty, avatar, room_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'default.png', ?)
        ON DUPLICATE KEY UPDATE
            fullname=VALUES(fullname), gender=VALUES(gender), dob=VALUES(dob), phone=VALUES(phone),
            email=VALUES(email), address=VALUES(address), faculty=VALUES(faculty), room_id=VALUES(room_id)");

    foreach ($students as $s) {
        $stmt->execute($s);
    }
    $log[] = "✅ Đã nạp " . count($students) . " sinh viên chuẩn UTF-8.";

    // ===== 4. Tính toán lại số lượng người ở cho từng phòng =====
    $rooms = $pdo->query("SELECT id, capacity FROM rooms")->fetchAll();
    foreach ($rooms as $room) {
        $cnt = (int)$pdo->query("SELECT COUNT(*) as c FROM students WHERE room_id = " . $room['id'])->fetch()['c'];
        $status = ($cnt >= $room['capacity']) ? 'Full' : 'Available';
        $pdo->exec("UPDATE rooms SET occupied = $cnt, status = '$status' WHERE id = " . $room['id']);
    }
    $log[] = "✅ Đã cập nhật occupied/status cho tất cả phòng.";

    // ===== 5. Hiển thị kết quả =====
    $roomsResult = $pdo->query("SELECT id, room_number, building, room_type, capacity, occupied, status FROM rooms ORDER BY id")->fetchAll();
    $stuResult   = $pdo->query("SELECT s.id, s.student_code, s.fullname, s.gender, r.room_number FROM students s LEFT JOIN rooms r ON s.room_id = r.id ORDER BY s.id")->fetchAll();

} catch (Exception $e) {
    die("<pre style='color:red'>LỖI: " . $e->getMessage() . "</pre>");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Seed KTX UTH</title>
<style>
  body { font-family: Inter, sans-serif; max-width: 1000px; margin: 30px auto; background: #f8fafc; }
  h2 { color: #4f46e5; }
  .log { background: #d1fae5; border: 1px solid #6ee7b7; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
  .log p { margin: 4px 0; font-weight: 600; color: #065f46; }
  table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 30px; }
  th { background: #4f46e5; color: white; padding: 10px 14px; text-align: left; font-size: .85rem; }
  td { padding: 9px 14px; border-bottom: 1px solid #e2e8f0; font-size: .9rem; }
  tr:hover td { background: #f1f5f9; }
  .badge { padding: 3px 10px; border-radius: 999px; font-size: .78rem; font-weight: 700; }
  .nam { background: #dbeafe; color: #1e40af; }
  .nu { background: #fce7f3; color: #9d174d; }
  .full { background: #fee2e2; color: #991b1b; }
  .av { background: #d1fae5; color: #065f46; }
  .warn { background: #fef3c7; color: #92400e; padding: 14px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fcd34d; }
</style>
</head>
<body>
<h2>🏠 KTX UTH – Đồng bộ dữ liệu thành công</h2>
<div class="log">
  <?php foreach ($log as $l): ?><p><?= $l ?></p><?php endforeach; ?>
</div>

<div class="warn">⚠️ <strong>Quan trọng:</strong> Hãy <strong>xóa file <code>seed.php</code></strong> sau khi xác nhận dữ liệu đúng để bảo mật hệ thống!</div>

<h3>📋 Danh sách 7 phòng KTX UTH</h3>
<table>
  <tr><th>ID</th><th>Phòng</th><th>Tòa nhà</th><th>Loại</th><th>Sức chứa</th><th>Đang ở</th><th>Trạng thái</th></tr>
  <?php foreach ($roomsResult as $r): ?>
  <tr>
    <td><?= $r['id'] ?></td>
    <td><strong><?= $r['room_number'] ?></strong></td>
    <td><?= htmlspecialchars($r['building']) ?></td>
    <td><?= htmlspecialchars($r['room_type']) ?></td>
    <td><?= $r['capacity'] ?> người</td>
    <td><?= $r['occupied'] ?> người</td>
    <td><span class="badge <?= $r['status']==='Full'?'full':'av' ?>"><?= $r['status'] ?></span></td>
  </tr>
  <?php endforeach; ?>
</table>

<h3>👥 Danh sách <?= count($stuResult) ?> sinh viên</h3>
<table>
  <tr><th>ID</th><th>MSSV</th><th>Họ và Tên</th><th>Giới tính</th><th>Phòng</th></tr>
  <?php foreach ($stuResult as $s): ?>
  <tr>
    <td><?= $s['id'] ?></td>
    <td><strong><?= $s['student_code'] ?></strong></td>
    <td><?= htmlspecialchars($s['fullname']) ?></td>
    <td><span class="badge <?= $s['gender']==='Nam'?'nam':'nu' ?>"><?= htmlspecialchars($s['gender']) ?></span></td>
    <td><?= $s['room_number'] ? $s['room_number'] : '<span style="color:#999">Chưa xếp phòng</span>' ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<p>👉 <a href="/" style="font-weight:700;color:#4f46e5">← Quay về trang chủ KTX UTH</a></p>
</body>
</html>
