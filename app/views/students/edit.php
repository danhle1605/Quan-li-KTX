<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="page-container container">
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-pen-to-square"></i> Cập Nhật Thông Tin Sinh Viên</h1>
            <p>MSSV: <strong><?= htmlspecialchars($student['student_code']) ?></strong></p>
        </div>
        <a href="<?= BASE_URL ?>student/index" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card-box">
        <form action="<?= BASE_URL ?>student/edit/<?= $student['id'] ?>" method="POST" enctype="multipart/form-data" id="studentEditForm">
            <div class="form-grid">
                <!-- Cột trái: Thông tin cá nhân -->
                <div class="form-section">
                    <h3><i class="fa-solid fa-address-card"></i> Thông Tin Cá Nhân</h3>
                    
                    <div class="form-group">
                        <label for="student_code">Mã Số Sinh Viên (MSSV) <span class="required">*</span></label>
                        <input type="text" id="student_code" name="student_code" class="form-control" 
                               value="<?= htmlspecialchars($student['student_code']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="fullname">Họ và Tên <span class="required">*</span></label>
                        <input type="text" id="fullname" name="fullname" class="form-control" 
                               value="<?= htmlspecialchars($student['fullname']) ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="gender">Giới tính <span class="required">*</span></label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="Nam" <?= $student['gender'] === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= $student['gender'] === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                            </select>
                        </div>

                        <div class="form-group flex-1">
                            <label for="dob">Ngày sinh <span class="required">*</span></label>
                            <input type="date" id="dob" name="dob" class="form-control" 
                                   value="<?= htmlspecialchars($student['dob']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="faculty">Khoa / Ngành học <span class="required">*</span></label>
                        <input type="text" id="faculty" name="faculty" class="form-control" 
                               value="<?= htmlspecialchars($student['faculty']) ?>" required>
                    </div>
                </div>

                <!-- Cột phải: Liên hệ & Ảnh đại diện -->
                <div class="form-section">
                    <h3><i class="fa-solid fa-phone"></i> Liên Hệ & Phòng Ở</h3>

                    <div class="form-group">
                        <label for="phone">Số điện thoại <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($student['phone']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email sinh viên <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($student['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Quê quán / Địa chỉ <span class="required">*</span></label>
                        <input type="text" id="address" name="address" class="form-control" 
                               value="<?= htmlspecialchars($student['address']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="room_id">Xếp / Đổi phòng KTX</label>
                        <select id="room_id" name="room_id" class="form-control">
                            <option value="">-- Chưa xếp phòng --</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= $room['id'] ?>" <?= $student['room_id'] == $room['id'] ? 'selected' : '' ?>>
                                    Phòng <?= htmlspecialchars($room['room_number']) ?> (<?= htmlspecialchars($room['building']) ?>) - Đã ở <?= $room['occupied'] ?>/<?= $room['capacity'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="avatar">Thay đổi Ảnh đại diện</label>
                        <input type="file" id="avatarInput" name="avatar" class="form-control-file" accept="image/*">
                        <div class="image-preview-wrapper">
                            <img id="avatarPreview" src="<?= BASE_URL ?>uploads/avatars/<?= htmlspecialchars($student['avatar']) ?>" 
                                 alt="Avatar hiện tại" class="preview-img">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-warning btn-lg">
                    <i class="fa-solid fa-pen-to-square"></i> Cập Nhật Sinh Viên
                </button>
                <a href="<?= BASE_URL ?>student/index" class="btn btn-secondary btn-lg">Hủy bỏ</a>
            </div>
        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
