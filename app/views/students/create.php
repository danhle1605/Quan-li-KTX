<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="page-container container">
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-plus-circle"></i> Thêm Phòng Kí Túc Xá Mới</h1>
            <p>Tạo thông tin phòng ở, tòa nhà và thiết lập sức chứa</p>
        </div>
        <a href="<?= BASE_URL ?>room/index" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card-box max-w-700 margin-auto">
        <form action="<?= BASE_URL ?>room/create" method="POST" id="roomForm">
            <div class="form-group">
                <label for="room_number">Số Phòng <span class="required">*</span></label>
                <input type="text" id="room_number" name="room_number" class="form-control" 
                       value="<?= htmlspecialchars($formData['room_number'] ?? '') ?>" placeholder="A101, B202, C301..." required>
            </div>

            <div class="form-group">
                <label for="building">Tòa Nhà / Dãy KTX <span class="required">*</span></label>
                <input type="text" id="building" name="building" class="form-control" 
                       value="<?= htmlspecialchars($formData['building'] ?? '') ?>" placeholder="Tòa A, Tòa B..." required>
            </div>

            <div class="form-row">
                <div class="form-group flex-1">
                    <label for="capacity">Sức chứa tối đa (Sinh viên) <span class="required">*</span></label>
                    <input type="number" id="capacity" name="capacity" class="form-control" min="1" max="10" 
                           value="<?= htmlspecialchars($formData['capacity'] ?? 4) ?>" required>
                </div>

                <div class="form-group flex-1">
                    <label for="price">Giá phòng (VNĐ / tháng) <span class="required">*</span></label>
                    <input type="number" id="price" name="price" class="form-control" step="10000" 
                           value="<?= htmlspecialchars($formData['price'] ?? 600000) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Trạng thái phòng</label>
                <select id="status" name="status" class="form-control">
                    <option value="Available">Available (Còn chỗ trống)</option>
                    <option value="Full">Full (Đã lấp đầy)</option>
                    <option value="Maintenance">Maintenance (Đang bảo trì)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Mô tả tiện nghi phòng</label>
                <textarea id="description" name="description" class="form-control" rows="4" 
                          placeholder="Mô tả máy lạnh, giường tầng, công trình khép kín..."><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Phòng
                </button>
                <a href="<?= BASE_URL ?>room/index" class="btn btn-secondary btn-lg">Hủy bỏ</a>
            </div>
        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
