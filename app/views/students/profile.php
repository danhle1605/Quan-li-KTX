<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20">
    <div class="page-header margin-bottom-30">
        <h2><i class="fa-solid fa-id-card text-primary"></i> Hồ Sơ Cá Nhân Sinh Viên</h2>
        <p class="text-muted">Thông tin cá nhân, phòng ở hiện tại và bạn cùng phòng</p>
    </div>

    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 25px;">
        <!-- Card Thông tin cá nhân -->
        <div class="card-box text-center">
            <img src="<?= BASE_URL ?>uploads/avatars/<?= htmlspecialchars($student['avatar'] ?? 'default.png') ?>" id="avatarPreview" style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); margin: 0 auto 15px auto;">
            
            <h3 style="margin: 0;"><?= htmlspecialchars($student['fullname'] ?? '') ?></h3>
            <span class="badge badge-primary margin-top-5">MSSV: <?= htmlspecialchars($student['student_code'] ?? '') ?></span>

            <hr style="margin: 20px 0; border-color: var(--border-light);">

            <div style="text-align: left; font-size: 14px; display: flex; flex-direction: column; gap: 10px;">
                <div><i class="fa-solid fa-venus-mars text-muted"></i> <strong>Giới tính:</strong> <?= htmlspecialchars($student['gender'] ?? '') ?></div>
                <div><i class="fa-solid fa-calendar text-muted"></i> <strong>Ngày sinh:</strong> <?= date('d/m/Y', strtotime($student['dob'] ?? 'now')) ?></div>
                <div><i class="fa-solid fa-phone text-muted"></i> <strong>SĐT:</strong> <?= htmlspecialchars($student['phone'] ?? '') ?></div>
                <div><i class="fa-solid fa-envelope text-muted"></i> <strong>Email:</strong> <?= htmlspecialchars($student['email'] ?? '') ?></div>
                <div><i class="fa-solid fa-graduation-cap text-muted"></i> <strong>Khoa:</strong> <?= htmlspecialchars($student['faculty'] ?? '') ?></div>
                <div><i class="fa-solid fa-location-dot text-muted"></i> <strong>Quê quán:</strong> <?= htmlspecialchars($student['address'] ?? '') ?></div>
            </div>
        </div>

        <!-- Card Phòng ở & Bạn cùng phòng -->
        <div>
            <div class="card-box margin-bottom-25">
                <div class="d-flex justify-content-between align-items-center margin-bottom-15">
                    <h3 style="margin: 0;"><i class="fa-solid fa-door-open text-primary"></i> Thông Tin Phòng Ở Hiện Tại</h3>
                    <?php if (!empty($student['room_id'])): ?>
                        <a href="<?= BASE_URL ?>request/create" class="btn btn-outline btn-sm">
                            <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Chuyển Phòng
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($student['room_id'])): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; background: var(--bg-subtle); padding: 20px; border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
                        <div><strong>Số phòng:</strong> <span class="text-primary font-weight-bold" style="font-size: 18px;">Phòng <?= htmlspecialchars($student['room_number']) ?></span></div>
                        <div><strong>Tòa nhà:</strong> <?= htmlspecialchars($student['building']) ?></div>
                        <div><strong>Loại phòng:</strong> <?= htmlspecialchars($student['room_type'] ?? 'Thường') ?></div>
                        <div><strong>Giá phòng:</strong> <?= number_format($student['price'] ?? 0, 0, ',', '.') ?>đ/tháng</div>
                        <div><strong>Sức chứa:</strong> <?= $student['occupied'] ?? 0 ?> / <?= $student['capacity'] ?? 0 ?> người</div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted" style="background: var(--bg-subtle); border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
                        <i class="fa-solid fa-circle-info fa-2x margin-bottom-10"></i>
                        <p>Hiện tại bạn chưa được xếp phòng kí túc xá.</p>
                        <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-primary btn-sm margin-top-10">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Tìm phòng phù hợp ngay
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Danh sách Bạn Cùng Phòng -->
            <?php if (!empty($roommates)): ?>
                <div class="card-box">
                    <h3 class="margin-bottom-20"><i class="fa-solid fa-users text-primary"></i> Danh Sách Bạn Cùng Phòng (<?= count($roommates) ?>)</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                        <?php foreach ($roommates as $rm): ?>
                            <div style="display: flex; align-items: center; gap: 12px; background: var(--bg-subtle); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
                                <img src="<?= BASE_URL ?>uploads/avatars/<?= htmlspecialchars($rm['avatar']) ?>" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">
                                <div>
                                    <strong style="display: block; font-size: 14px;"><?= htmlspecialchars($rm['fullname']) ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars($rm['student_code']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
