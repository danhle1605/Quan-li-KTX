<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20" style="max-width: 700px;">
    <div class="page-header text-center margin-bottom-30">
        <h2><i class="fa-solid fa-paper-plane text-primary"></i> Gửi Yêu Cầu Chuyển / Đăng Ký Phòng</h2>
        <p class="text-muted">Chọn phòng kí túc xá bạn muốn chuyển đến và cung cấp lý do cho Ban Quản Lý KTX</p>
    </div>

    <div class="card" style="background: #ffffff; border-radius: 16px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <form action="<?= BASE_URL ?>request/create" method="POST">
            <!-- Thông tin sinh viên -->
            <div class="form-group margin-bottom-20" style="background: #f8fafc; padding: 15px; border-radius: 10px;">
                <label class="form-label"><strong>Sinh viên gửi yêu cầu:</strong></label>
                <div>
                    <strong><?= htmlspecialchars($student['fullname'] ?? Session::get('user_name')) ?></strong> 
                    (MSSV: <?= htmlspecialchars($student['student_code'] ?? 'Chưa cập nhật') ?>)
                </div>
                <div class="text-muted margin-top-5">
                    Phòng ở hiện tại: 
                    <strong><?= !empty($student['room_number']) ? htmlspecialchars($student['room_number']) . ' (' . htmlspecialchars($student['building']) . ')' : 'Chưa ở phòng nào' ?></strong>
                </div>
            </div>

            <!-- Chọn phòng muốn chuyển đến -->
            <div class="form-group margin-bottom-20">
                <label class="form-label"><strong>Chọn phòng muốn chuyển / đăng ký (*):</strong></label>
                <select name="requested_room_id" class="form-control" required style="font-size: 15px; padding: 10px;">
                    <option value="">-- Chọn phòng còn chỗ trống --</option>
                    <?php if (!empty($availableRooms)): ?>
                        <?php foreach ($availableRooms as $r): ?>
                            <?php 
                                $selected = (isset($_GET['requested_room_id']) && $_GET['requested_room_id'] == $r['id']) ? 'selected' : '';
                            ?>
                            <option value="<?= $r['id'] ?>" <?= $selected ?>>
                                Phòng <?= htmlspecialchars($r['room_number']) ?> (<?= htmlspecialchars($r['building']) ?> - Tầng <?= $r['floor'] ?>) - <?= htmlspecialchars($r['room_type']) ?> - <?= number_format($r['price'], 0, ',', '.') ?>đ/tháng (Còn <?= $r['remaining'] ?> chỗ)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small class="text-muted">Lưu ý: Bạn không thể gửi yêu cầu đến các phòng đã Đầy hoặc đang Bảo trì.</small>
            </div>

            <!-- Lý do chuyển phòng -->
            <div class="form-group margin-bottom-25">
                <label class="form-label"><strong>Lý do chuyển phòng / nguyện vọng (*):</strong></label>
                <textarea name="reason" class="form-control" rows="4" placeholder="Nhập lý do cụ thể (Ví dụ: Muốn ở cùng bạn học lớp CNTT, phòng cũ khá xa cầu thang,...)" required style="padding: 12px;"></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="<?= BASE_URL ?>request/index" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 25px;">
                    <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Chuyển Phòng
                </button>
            </div>
        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
