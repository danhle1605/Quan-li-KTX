<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="page-container container">
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-file-signature"></i> Tạo Hợp Đồng Kí Túc Xá Mới</h1>
            <p>Thiết lập thông tin sinh viên, chọn phòng và quy định thời hạn ở KTX</p>
        </div>
        <a href="<?= BASE_URL ?>contract/index" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card-box max-w-700 margin-auto">
        <form action="<?= BASE_URL ?>contract/create" method="POST" id="contractForm">
            <div class="form-group">
                <label for="student_id">Chọn Sinh Viên <span class="required">*</span></label>
                <select id="student_id" name="student_id" class="form-control" required>
                    <option value="">-- Chọn sinh viên --</option>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (isset($formData['student_id']) && $formData['student_id'] == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['fullname']) ?> - MSSV: <?= htmlspecialchars($s['student_code']) ?> (<?= htmlspecialchars($s['gender']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group margin-top-15">
                <label for="room_id">Chọn Phòng Ở KTX (Còn chỗ) <span class="required">*</span></label>
                <select id="room_id" name="room_id" class="form-control" required>
                    <option value="">-- Chọn phòng kí túc xá --</option>
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= (isset($_GET['room_id']) && $_GET['room_id'] == $r['id']) || (isset($formData['room_id']) && $formData['room_id'] == $r['id']) ? 'selected' : '' ?>>
                                Phòng <?= htmlspecialchars($r['room_number']) ?> - <?= htmlspecialchars($r['building']) ?> (Còn <?= $r['capacity'] - $r['occupied'] ?>/<?= $r['capacity'] ?> chỗ - <?= number_format($r['price']) ?>đ/tháng)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-row margin-top-15">
                <div class="form-group flex-1">
                    <label for="start_date">Ngày Bắt Đầu Hợp Đồng <span class="required">*</span></label>
                    <input type="date" id="start_date" name="start_date" class="form-control" 
                           value="<?= htmlspecialchars($formData['start_date'] ?? date('Y-m-d')) ?>" required>
                </div>

                <div class="form-group flex-1">
                    <label for="end_date">Ngày Kết Thúc Hợp Đồng <span class="required">*</span></label>
                    <input type="date" id="end_date" name="end_date" class="form-control" 
                           value="<?= htmlspecialchars($formData['end_date'] ?? date('Y-m-d', strtotime('+1 year'))) ?>" required>
                </div>
            </div>

            <div class="form-group margin-top-15">
                <label for="deposit">Tiền Đặt Cọc (VNĐ) <span class="required">*</span></label>
                <input type="number" id="deposit" name="deposit" class="form-control" step="50000" 
                       value="<?= htmlspecialchars($formData['deposit'] ?? 600000) ?>" required>
            </div>

            <div class="form-actions margin-top-25">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu & Kích Hoạt Hợp Đồng
                </button>
                <a href="<?= BASE_URL ?>contract/index" class="btn btn-secondary btn-lg">Hủy bỏ</a>
            </div>
        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
