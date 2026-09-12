<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20">
    <div class="page-header">
        <div>
            <h2><i class="fa-solid fa-wand-magic-sparkles text-primary"></i> Gợi ý phòng – Smart Match</h2>
            <p class="text-muted">Hệ thống phân tích và xếp hạng phòng theo tiêu chí bạn chọn (tối đa 100 điểm)</p>
        </div>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>room/index" class="btn btn-outline"><i class="fa-solid fa-door-open"></i> Danh sách phòng</a>
            <a href="<?= BASE_URL ?>room/map" class="btn btn-outline"><i class="fa-solid fa-map-location-dot"></i> Sơ đồ phòng</a>
        </div>
    </div>

    <!-- Bảng chấm điểm -->
    <div class="card-box smart-match-banner margin-bottom-20">
        <h4><i class="fa-solid fa-calculator"></i> Công thức chấm điểm (Tối đa 100 điểm)</h4>
        <div class="smart-match-grid">
            <div class="smart-match-item">
                <div style="font-size: 1.1rem; font-weight: 700;">+30 điểm</div>
                <div style="font-size: 0.8rem; opacity: 0.85;">Phòng còn chỗ trống</div>
            </div>
            <div class="smart-match-item">
                <div style="font-size: 1.1rem; font-weight: 700;">+25 điểm</div>
                <div style="font-size: 0.8rem; opacity: 0.85;">Phù hợp giới tính</div>
            </div>
            <div class="smart-match-item">
                <div style="font-size: 1.1rem; font-weight: 700;">+20 điểm</div>
                <div style="font-size: 0.8rem; opacity: 0.85;">Giá &le; ngân sách</div>
            </div>
            <div class="smart-match-item">
                <div style="font-size: 1.1rem; font-weight: 700;">+15 điểm</div>
                <div style="font-size: 0.8rem; opacity: 0.85;">Đúng tòa nhà chọn</div>
            </div>
            <div class="smart-match-item">
                <div style="font-size: 1.1rem; font-weight: 700;">+10 điểm</div>
                <div style="font-size: 0.8rem; opacity: 0.85;">Khớp loại phòng</div>
            </div>
        </div>
    </div>

    <!-- Form Nhập Nhu Cầu -->
    <div class="card-box margin-bottom-25">
        <form action="<?= BASE_URL ?>room/smartMatch" method="GET" id="smartMatchForm">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
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
                        <option value="">-- Tất cả các tòa --</option>
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
                        <option value="2" <?= ($desiredCapacity ?? 0) == 2 ? 'selected' : '' ?>>Phòng 2 người (VIP)</option>
                        <option value="4" <?= ($desiredCapacity ?? 0) == 4 ? 'selected' : '' ?>>Phòng 4 người (Máy lạnh)</option>
                        <option value="6" <?= ($desiredCapacity ?? 0) == 6 ? 'selected' : '' ?>>Phòng 6 người (Tiêu chuẩn)</option>
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

            <div class="margin-top-15 text-center">
                <button type="submit" class="btn btn-primary btn-lg" id="btnRunSmartMatch">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Phân tích & Gợi ý phòng
                </button>
            </div>
        </form>
    </div>

    <!-- Kết Quả Gợi Ý -->
    <div class="results-section">
        <?php if (!empty($matchedRooms)): ?>
            <div class="d-flex justify-content-between align-items-center margin-bottom-15">
                <h3 style="font-size: 1rem; font-weight: 700;"><i class="fa-solid fa-check-circle text-success"></i> Kết quả phù hợp (<?= count($matchedRooms) ?> phòng)</h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($matchedRooms as $idx => $r): ?>
                    <?php 
                        $score = $r['match_score'];
                        $scoreColor = $score >= 85 ? '#15803d' : ($score >= 70 ? '#d97706' : '#dc2626');
                        $scoreLabel = $score >= 85 ? 'Rất phù hợp' : ($score >= 70 ? 'Khá phù hợp' : 'Trung bình');
                    ?>
                    <div class="smart-room-card <?= $idx === 0 ? 'top-match' : '' ?>">
                        <!-- Điểm số -->
                        <div class="smart-score-badge" style="border-color: <?= $scoreColor ?>; color: <?= $scoreColor ?>; background: none;">
                            <span style="font-size: 1.2rem; font-weight: 700; line-height: 1;"><?= $score ?></span>
                            <span style="font-size: 0.65rem; color: var(--text-muted);">/100</span>
                        </div>

                        <!-- Thông tin phòng -->
                        <div style="flex: 1; min-width: 0;">
                            <div class="d-flex align-items-center gap-2 margin-bottom-5">
                                <?php if ($idx === 0): ?>
                                    <span class="badge badge-primary"><i class="fa-solid fa-crown"></i> Gợi ý hàng đầu</span>
                                <?php endif; ?>
                                <span class="badge" style="background: <?= $scoreColor ?>10; color: <?= $scoreColor ?>; border: 1px solid <?= $scoreColor ?>40;"><?= $scoreLabel ?></span>
                            </div>
                            <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0 0 4px;">Phòng <?= htmlspecialchars($r['room_number']) ?></h3>
                            <div class="text-muted" style="font-size: 0.8rem;">
                                <i class="fa-solid fa-building"></i> Tòa <?= htmlspecialchars($r['building']) ?> – Tầng <?= $r['floor'] ?>
                                &nbsp;|&nbsp;
                                <span class="badge badge-secondary"><?= htmlspecialchars($r['room_type']) ?></span>
                                &nbsp;|&nbsp;
                                <strong class="text-primary"><?= number_format($r['price'], 0, ',', '.') ?> VNĐ/tháng</strong>
                            </div>
                            <div class="margin-top-10">
                                <div style="font-size: 0.775rem; color: var(--text-muted); margin-bottom: 4px; font-weight: 600;">Chi tiết điểm đánh giá:</div>
                                <ul style="margin: 0; padding-left: 16px; font-size: 0.8rem; color: var(--text-main); line-height: 1.6;">
                                    <?php foreach ($r['match_reasons'] as $reason): ?>
                                        <li><?= htmlspecialchars($reason) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <!-- Hành động -->
                        <div class="d-flex flex-column gap-2" style="min-width: 130px;">
                            <button class="btn btn-sm btn-outline btn-view-room-detail" data-id="<?= $r['id'] ?>">
                                <i class="fa-solid fa-eye"></i> Xem chi tiết
                            </button>
                            <?php if (Session::get('user_role') === 'student'): ?>
                                <a href="<?= BASE_URL ?>request/create?requested_room_id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-paper-plane"></i> Đăng ký phòng
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_GET['gender'])): ?>
            <div class="card-box text-center py-5">
                <i class="fa-solid fa-circle-exclamation fa-2x text-muted"></i>
                <p class="text-muted margin-top-15">Không có phòng trống nào thỏa mãn các điều kiện tìm kiếm.<br>Hãy thử điều chỉnh ngân sách hoặc tòa nhà.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Chi Tiết Phòng -->
<div class="modal" id="roomDetailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="roomModalTitle">Chi tiết phòng</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="roomModalBody">
                <p class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải thông tin phòng...</p>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
