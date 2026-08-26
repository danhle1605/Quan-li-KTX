<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20">
    <div class="page-header text-center margin-bottom-30">
        <h2 style="font-size: 28px; color: #4f46e5;">
            <i class="fa-solid fa-wand-magic-sparkles text-indigo"></i> Thuật Toán Gợi Ý Phòng Thông Minh (Smart Room Recommendation)
        </h2>
        <p class="text-muted" style="max-width: 650px; margin: 0 auto;">
            Hệ thống phân tích dữ liệu phòng thực tế theo công thức tính điểm (Scoring Algorithm 100đ) để đề xuất phòng KTX tối ưu nhất cho nhu cầu của bạn.
        </p>
    </div>

    <!-- Scoring Rules Banner -->
    <div class="card margin-bottom-25" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);">
        <h4 style="margin-top: 0; color: #fff;"><i class="fa-solid fa-calculator"></i> Công thức chấm điểm phù hợp (Tối đa 100 điểm):</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 15px; text-align: center;">
            <div style="background: rgba(255,255,255,0.15); padding: 12px; border-radius: 10px; backdrop-filter: blur(5px);">
                <div style="font-size: 20px; font-weight: 700;">+30 điểm</div>
                <div style="font-size: 13px;">Phòng còn chỗ trống</div>
            </div>
            <div style="background: rgba(255,255,255,0.15); padding: 12px; border-radius: 10px; backdrop-filter: blur(5px);">
                <div style="font-size: 20px; font-weight: 700;">+25 điểm</div>
                <div style="font-size: 13px;">Phù hợp giới tính (Nam/Nữ)</div>
            </div>
            <div style="background: rgba(255,255,255,0.15); padding: 12px; border-radius: 10px; backdrop-filter: blur(5px);">
                <div style="font-size: 20px; font-weight: 700;">+20 điểm</div>
                <div style="font-size: 13px;">Giá <= Ngân sách tối đa</div>
            </div>
            <div style="background: rgba(255,255,255,0.15); padding: 12px; border-radius: 10px; backdrop-filter: blur(5px);">
                <div style="font-size: 20px; font-weight: 700;">+15 điểm</div>
                <div style="font-size: 13px;">Đúng tòa nhà chọn trước</div>
            </div>
            <div style="background: rgba(255,255,255,0.15); padding: 12px; border-radius: 10px; backdrop-filter: blur(5px);">
                <div style="font-size: 20px; font-weight: 700;">+10 điểm</div>
                <div style="font-size: 13px;">Sức chứa / Loại phòng khớp</div>
            </div>
        </div>
    </div>

    <!-- Form Nhập Nhu Cầu -->
    <div class="card margin-bottom-30" style="background: #ffffff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <form action="<?= BASE_URL ?>room/smartMatch" method="GET" id="smartMatchForm">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label"><strong>Giới tính của bạn:</strong></label>
                    <select name="gender" class="form-control">
                        <option value="Nam" <?= ($gender ?? 'Nam') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                        <option value="Nữ" <?= ($gender ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Ngân sách tối đa (VNĐ):</strong></label>
                    <select name="price" class="form-control">
                        <option value="450000"  <?= ($price ?? 0) == 450000  ? 'selected' : '' ?>>Tối đa 450.000đ / tháng</option>
                        <option value="600000"  <?= ($price ?? 0) == 600000  ? 'selected' : '' ?>>Tối đa 600.000đ / tháng</option>
                        <option value="700000"  <?= ($price ?? 0) == 700000  ? 'selected' : '' ?>>Tối đa 700.000đ / tháng</option>
                        <option value="850000"  <?= ($price ?? 0) == 850000  ? 'selected' : '' ?>>Tối đa 850.000đ / tháng</option>
                        <option value="1000000" <?= ($price ?? 0) == 1000000 ? 'selected' : '' ?>>Tối đa 1.000.000đ / tháng</option>
                        <option value="1200000" <?= ($price ?? 0) == 1200000 ? 'selected' : '' ?>>Tối đa 1.200.000đ / tháng</option>
                        <option value="2000000" <?= ($price ?? 0) == 2000000 ? 'selected' : '' ?>>Không giới hạn ngân sách</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Tòa nhà mong muốn:</strong></label>
                    <select name="building" class="form-control">
                        <option value="">-- Tất cả các Tòa --</option>
                        <?php if (!empty($buildings)): ?>
                            <?php foreach ($buildings as $b): ?>
                                <option value="<?= htmlspecialchars($b) ?>" <?= ($building ?? '') === $b ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Số người trong phòng:</strong></label>
                    <select name="capacity" class="form-control">
                        <option value="0">Tất cả sức chứa</option>
                        <option value="2" <?= ($desiredCapacity ?? 0) == 2 ? 'selected' : '' ?>>Phòng 2 giường (VIP)</option>
                        <option value="4" <?= ($desiredCapacity ?? 0) == 4 ? 'selected' : '' ?>>Phòng 4 giường (Máy lạnh)</option>
                        <option value="6" <?= ($desiredCapacity ?? 0) == 6 ? 'selected' : '' ?>>Phòng 6 giường (Tiêu chuẩn)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Loại phòng:</strong></label>
                    <select name="room_type" class="form-control">
                        <option value="">Tất cả loại phòng</option>
                        <option value="Máy lạnh" <?= ($roomType ?? '') === 'Máy lạnh' ? 'selected' : '' ?>>Phòng Máy lạnh</option>
                        <option value="VIP" <?= ($roomType ?? '') === 'VIP' ? 'selected' : '' ?>>Phòng VIP</option>
                        <option value="Thường" <?= ($roomType ?? '') === 'Thường' ? 'selected' : '' ?>>Phòng Thường</option>
                    </select>
                </div>
            </div>

            <div class="margin-top-20 text-center">
                <button type="submit" class="btn btn-primary btn-lg" id="btnRunSmartMatch" style="padding: 12px 35px; font-weight: 700;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Phân Tích & Gợi Ý Phòng Tối Ưu
                </button>
            </div>
        </form>
    </div>

    <!-- Kết Quả Gợi Ý -->
    <div class="results-section">
        <h3 class="margin-bottom-20"><i class="fa-solid fa-star text-warning"></i> Phòng Phù Hợp Nhất Với Bạn</h3>

        <?php if (!empty($matchedRooms)): ?>
            <div class="matched-rooms-list" style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($matchedRooms as $idx => $r): ?>
                    <?php 
                        $score = $r['match_score'];
                        $badgeColor = '#10b981';
                        if ($score < 70) $badgeColor = '#f59e0b';
                        if ($score < 50) $badgeColor = '#ef4444';
                    ?>
                    <div class="card matched-room-card" style="background: #ffffff; border-radius: 16px; padding: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 2px solid <?= $idx === 0 ? '#6366f1' : '#f1f5f9' ?>;">
                        <div style="display: grid; grid-template-columns: 240px 1fr 200px; gap: 20px; align-items: center;">
                            <!-- Cột 1: Thông tin phòng -->
                            <div>
                                <?php if ($idx === 0): ?>
                                    <span class="badge badge-primary margin-bottom-10" style="background: #6366f1;"><i class="fa-solid fa-crown"></i> GỢI Ý HÀNG ĐẦU</span>
                                <?php endif; ?>
                                <h3 style="margin: 0; font-size: 22px; color: #1e293b;">
                                    Phòng <?= htmlspecialchars($r['room_number']) ?>
                                </h3>
                                <div class="text-muted margin-top-5">
                                    <i class="fa-solid fa-building"></i> <?= htmlspecialchars($r['building']) ?> (Tầng <?= $r['floor'] ?>)
                                </div>
                                <div class="margin-top-5">
                                    <span class="badge badge-info"><?= htmlspecialchars($r['room_type']) ?></span>
                                    <span class="text-primary font-weight-bold" style="font-size: 16px; margin-left: 5px;"><?= number_format($r['price'], 0, ',', '.') ?> VNĐ</span>
                                </div>
                            </div>

                            <!-- Cột 2: Lý do tính điểm -->
                            <div>
                                <h5 style="margin-top: 0; color: #475569;"><i class="fa-solid fa-list-check"></i> Chi tiết điểm đánh giá:</h5>
                                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #334155;">
                                    <?php foreach ($r['match_reasons'] as $reason): ?>
                                        <li style="margin-bottom: 4px;"><?= htmlspecialchars($reason) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <!-- Cột 3: Điểm số & Hành động -->
                            <div class="text-center" style="border-left: 1px dashed #cbd5e1; padding-left: 20px;">
                                <div style="font-size: 32px; font-weight: 800; color: <?= $badgeColor ?>;">
                                    <?= $score ?><span style="font-size: 18px; color: #94a3b8;">/100đ</span>
                                </div>
                                <div style="font-size: 13px; font-weight: 600; color: <?= $badgeColor ?>;" class="margin-bottom-15">
                                    <?= $score >= 85 ? 'RẤT PHÙ HỢP' : ($score >= 70 ? 'KHÁ PHÙ HỢP' : 'PHÙ HỢP TRUNG BÌNH') ?>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <button class="btn btn-outline btn-sm btn-view-room-detail" data-id="<?= $r['id'] ?>">
                                        <i class="fa-solid fa-eye"></i> Xem chi tiết
                                    </button>
                                    
                                    <?php if (Session::get('user_role') === 'student'): ?>
                                        <a href="<?= BASE_URL ?>request/create?requested_room_id=<?= $r['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-paper-plane"></i> Đăng ký phòng này
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card text-center py-5">
                <i class="fa-solid fa-circle-exclamation fa-3x text-muted"></i>
                <h4 class="margin-top-15">Không tìm thấy phòng phù hợp</h4>
                <p class="text-muted">Không có phòng trống nào thỏa mãn các điều kiện tìm kiếm hiện tại.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Pop-up -->
<div class="modal" id="roomDetailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="roomModalTitle">Chi Tiết Phòng</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="roomModalBody">
                <p class="text-center py-4"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><br>Đang tải thông tin phòng...</p>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
