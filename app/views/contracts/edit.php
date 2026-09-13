<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="page-container container">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fa-solid fa-file-pen text-primary"></i> Chỉnh Sửa & Gia Hạn Hợp Đồng #HĐ-<?= htmlspecialchars($contract['id']) ?></h2>
            <p class="text-muted">Cập nhật thời hạn hợp đồng, số tiền đặt cọc hoặc điều chuyển phòng ở KTX UTH</p>
        </div>
        <a href="<?= BASE_URL ?>contract/index" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <div class="card-box max-w-700 margin-auto">
        <!-- Tóm tắt thông tin hợp đồng hiện tại -->
        <div style="background: var(--bg-subtle); border-left: 4px solid var(--primary); border-radius: var(--radius-sm); padding: 14px 18px; margin-bottom: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span class="text-muted" style="font-size: 13px; text-transform: uppercase; font-weight: 600;">Mã hợp đồng:</span>
                    <strong style="font-size: 16px; margin-left: 6px;">#HĐ-<?= htmlspecialchars($contract['id']) ?> (HĐ-KTX-<?= str_pad($contract['id'], 6, '0', STR_PAD_LEFT) ?>)</strong>
                </div>
                <div>
                    <?php if ($contract['status'] === 'Active'): ?>
                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Đang hiệu lực</span>
                    <?php elseif ($contract['status'] === 'Expired'): ?>
                        <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> Đã hết hạn</span>
                    <?php else: ?>
                        <span class="badge badge-secondary"><i class="fa-solid fa-ban"></i> Đã hủy</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <form action="<?= BASE_URL ?>contract/edit/<?= htmlspecialchars($contract['id']) ?>" method="POST" id="editContractForm">
            <!-- 1. Chọn Sinh viên -->
            <div class="form-group">
                <label for="student_id">Sinh Viên Thuê Phòng <span class="required">*</span></label>
                <select id="student_id" name="student_id" class="form-control" required>
                    <option value="">-- Chọn sinh viên --</option>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($contract['student_id'] == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['fullname']) ?> - MSSV: <?= htmlspecialchars($s['student_code']) ?> (<?= htmlspecialchars($s['gender']) ?> - <?= htmlspecialchars($s['faculty']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- 2. Chọn Phòng ở KTX -->
            <div class="form-group margin-top-15">
                <label for="room_id">Phòng Ở KTX <span class="required">*</span></label>
                <select id="room_id" name="room_id" class="form-control" required>
                    <option value="">-- Chọn phòng kí túc xá --</option>
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $r): ?>
                            <?php 
                                $isCurrentRoom = ($contract['room_id'] == $r['id']);
                                $remaining = $r['capacity'] - $r['occupied'];
                                $isMaintenance = ($r['status'] === 'Maintenance');
                                $isFull = ($r['occupied'] >= $r['capacity']);
                            ?>
                            <option value="<?= $r['id'] ?>" 
                                    <?= $isCurrentRoom ? 'selected' : '' ?>
                                    <?= (!$isCurrentRoom && ($isMaintenance || $isFull)) ? 'disabled' : '' ?>>
                                Phòng <?= htmlspecialchars($r['room_number']) ?> - <?= htmlspecialchars($r['building']) ?> 
                                [<?= htmlspecialchars($r['room_type']) ?> - <?= number_format($r['price']) ?>đ/tháng]
                                <?php if ($isCurrentRoom): ?>
                                    - (Phòng hiện tại - Đang ở <?= $r['occupied'] ?>/<?= $r['capacity'] ?> chỗ)
                                <?php elseif ($isMaintenance): ?>
                                    - (Đang bảo trì)
                                <?php elseif ($isFull): ?>
                                    - (Đã đầy chỗ)
                                <?php else: ?>
                                    - (Còn <?= $remaining ?>/<?= $r['capacity'] ?> chỗ trống)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small class="text-muted" style="display: block; margin-top: 6px;">
                    <i class="fa-solid fa-circle-info"></i> Nếu giữ nguyên phòng hiện tại, số lượng người trong phòng sẽ không thay đổi. Hệ thống sẽ tự động kiểm tra sức chứa nếu đổi sang phòng khác.
                </small>
            </div>

            <!-- 3. Ngày bắt đầu & Ngày kết thúc -->
            <div class="form-row margin-top-15" style="display: flex; gap: 15px;">
                <div class="form-group flex-1" style="flex: 1;">
                    <label for="start_date">Ngày Bắt Đầu Hợp Đồng <span class="required">*</span></label>
                    <input type="date" id="start_date" name="start_date" class="form-control" 
                           value="<?= htmlspecialchars($contract['start_date']) ?>" required>
                </div>

                <div class="form-group flex-1" style="flex: 1;">
                    <label for="end_date">Ngày Kết Thúc (Gia Hạn) <span class="required">*</span></label>
                    <input type="date" id="end_date" name="end_date" class="form-control" 
                           value="<?= htmlspecialchars($contract['end_date']) ?>" required>
                </div>
            </div>
            <div class="margin-top-5">
                <small class="text-primary" style="font-weight: 500;">
                    <i class="fa-solid fa-clock-rotate-left"></i> 
                    <strong>Gia hạn hợp đồng:</strong> Để gia hạn, thay đổi ngày kết thúc (ví dụ thêm 1 năm) và nhấn "Lưu Thay Đổi & Gia Hạn".
                </small>
            </div>

            <!-- 4. Tiền đặt cọc -->
            <div class="form-group margin-top-15">
                <label for="deposit">Tiền Đặt Cọc (VNĐ) <span class="required">*</span></label>
                <input type="number" id="deposit" name="deposit" class="form-control" step="50000" 
                       value="<?= htmlspecialchars((int)$contract['deposit']) ?>" required>
            </div>

            <!-- 5. Trạng thái hợp đồng -->
            <div class="form-group margin-top-15">
                <label for="status">Trạng Thái Hợp Đồng</label>
                <select id="status" name="status" class="form-control">
                    <option value="Active" <?= ($contract['status'] === 'Active') ? 'selected' : '' ?>>Hiệu lực (Active)</option>
                    <option value="Expired" <?= ($contract['status'] === 'Expired') ? 'selected' : '' ?>>Đã hết hạn (Expired)</option>
                    <option value="Cancelled" <?= ($contract['status'] === 'Cancelled') ? 'selected' : '' ?>>Đã hủy (Cancelled)</option>
                </select>
                <small class="text-muted" style="display: block; margin-top: 4px;">
                    Hệ thống sẽ tự động cập nhật trạng thái thành Active nếu ngày kết thúc được gia hạn lớn hơn ngày hiện tại.
                </small>
            </div>

            <!-- 6. Nút thao tác -->
            <div class="form-actions margin-top-25" style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi & Gia Hạn
                </button>
                <a href="<?= BASE_URL ?>contract/index" class="btn btn-outline btn-lg">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
