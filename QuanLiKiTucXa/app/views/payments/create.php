<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="page-container container">
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-file-circle-plus"></i> Lập Hóa Đơn Tiền Phòng & Điện Nước</h1>
            <p>Tính tổng tiền điện, tiền nước và phí dịch vụ phòng ở KTX</p>
        </div>
        <a href="<?= BASE_URL ?>payment/index" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card-box max-w-700 margin-auto">
        <form action="<?= BASE_URL ?>payment/create" method="POST" id="paymentForm">
            <div class="form-group">
                <label for="room_id">Chọn Phòng Ở KTX <span class="required">*</span></label>
                <select id="room_id" name="room_id" class="form-control" required>
                    <option value="">-- Chọn phòng --</option>
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $r): ?>
                            <option value="<?= $r['id'] ?>" data-price="<?= $r['price'] ?>" <?= (isset($formData['room_id']) && $formData['room_id'] == $r['id']) ? 'selected' : '' ?>>
                                Phòng <?= htmlspecialchars($r['room_number']) ?> - <?= htmlspecialchars($r['building']) ?> (Giá phòng: <?= number_format($r['price']) ?>đ)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group margin-top-15">
                <label for="billing_month">Tháng Thanh Toán <span class="required">*</span></label>
                <input type="text" id="billing_month" name="billing_month" class="form-control" 
                       value="<?= htmlspecialchars($formData['billing_month'] ?? date('m/Y')) ?>" placeholder="Ví dụ: 08/2026" required>
            </div>

            <div class="form-row margin-top-15">
                <div class="form-group flex-1">
                    <label for="room_fee">Tiền Phòng (VNĐ) <span class="required">*</span></label>
                    <input type="number" id="room_fee" name="room_fee" class="form-control" step="10000" 
                           value="<?= htmlspecialchars($formData['room_fee'] ?? 600000) ?>" required>
                </div>

                <div class="form-group flex-1">
                    <label for="electricity_fee">Tiền Điện (VNĐ) <span class="required">*</span></label>
                    <input type="number" id="electricity_fee" name="electricity_fee" class="form-control" step="5000" 
                           value="<?= htmlspecialchars($formData['electricity_fee'] ?? 150000) ?>" required>
                </div>

                <div class="form-group flex-1">
                    <label for="water_fee">Tiền Nước (VNĐ) <span class="required">*</span></label>
                    <input type="number" id="water_fee" name="water_fee" class="form-control" step="5000" 
                           value="<?= htmlspecialchars($formData['water_fee'] ?? 60000) ?>" required>
                </div>
            </div>

            <div class="form-group margin-top-15">
                <label for="status">Trạng Thái Thanh Toán</label>
                <select id="status" name="status" class="form-control">
                    <option value="Unpaid" <?= (isset($formData['status']) && $formData['status'] === 'Unpaid') ? 'selected' : '' ?>>Unpaid (Chưa thanh toán)</option>
                    <option value="Paid" <?= (isset($formData['status']) && $formData['status'] === 'Paid') ? 'selected' : '' ?>>Paid (Đã thanh toán)</option>
                </select>
            </div>

            <div class="form-actions margin-top-25">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-floppy-disk"></i> Tạo & Xuất Hóa Đơn
                </button>
                <a href="<?= BASE_URL ?>payment/index" class="btn btn-secondary btn-lg">Hủy bỏ</a>
            </div>
        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
