<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fa-solid fa-map-location-dot text-primary"></i> Sơ đồ phòng KTX</h2>
            <p class="text-muted">Theo dõi và quản lý trạng thái các phòng theo từng tòa nhà</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-outline">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Smart Match
            </a>
            <a href="<?= BASE_URL ?>room/index" class="btn btn-outline">
                <i class="fa-solid fa-list"></i> Xem dạng danh sách
            </a>
        </div>
    </div>

    <!-- Chú thích màu sắc (Legend) -->
    <div class="card margin-bottom-20" style="background: #ffffff; border-radius: 8px; border: 1px solid var(--border-color); padding: 15px;">
        <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
            <strong><i class="fa-solid fa-circle-info"></i> Chú thích trạng thái:</strong>
            <span class="badge badge-success" style="padding: 6px 10px; font-size: 13px;"><i class="fa-solid fa-check"></i> Available (Còn chỗ trống)</span>
            <span class="badge badge-danger" style="padding: 6px 10px; font-size: 13px;"><i class="fa-solid fa-user-group"></i> Full (Đã đầy)</span>
            <span class="badge badge-secondary" style="padding: 6px 10px; font-size: 13px; background: #64748b; color: white;"><i class="fa-solid fa-wrench"></i> Maintenance (Đang bảo trì)</span>
        </div>
    </div>

    <?php if (!empty($groupedRooms)): ?>
        <?php foreach ($groupedRooms as $buildingName => $rooms): ?>
            <div class="building-section margin-bottom-30">
                <div class="section-header d-flex align-items-center gap-2 margin-bottom-15" style="border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
                    <h3 style="margin: 0; color: #1e293b;">
                        <i class="fa-solid fa-building text-indigo"></i> <?= htmlspecialchars($buildingName) ?>
                    </h3>
                    <span class="badge badge-info"><?= count($rooms) ?> phòng</span>
                </div>

                <div class="room-map-grid">
                    <?php foreach ($rooms as $r): ?>
                        <?php 
                            $percent = $r['capacity'] > 0 ? min(100, round(($r['occupied'] / $r['capacity']) * 100)) : 0;
                            $statusClass = 'border-available';
                            $badgeClass = 'badge-success';

                            if ($r['status'] === 'Maintenance') {
                                $statusClass = 'border-maintenance';
                                $badgeClass = 'badge-secondary';
                            } else if ($r['occupied'] >= $r['capacity'] || $r['status'] === 'Full') {
                                $statusClass = 'border-full';
                                $badgeClass = 'badge-danger';
                            }
                        ?>
                        <div class="room-card-item btn-view-room-detail <?= $statusClass ?>" data-id="<?= $r['id'] ?>" style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-center margin-bottom-10">
                                <h4 class="font-weight-bold" style="margin: 0; font-size: 1rem;">
                                    Phòng <?= htmlspecialchars($r['room_number']) ?>
                                </h4>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r['status']) ?></span>
                            </div>

                            <div class="text-muted margin-bottom-10" style="font-size: 0.8rem;">
                                <div>Tầng <?= $r['floor'] ?> &nbsp;|&nbsp; <strong><?= htmlspecialchars($r['room_type']) ?></strong></div>
                                <div><strong class="text-primary"><?= number_format($r['price'], 0, ',', '.') ?> VNĐ</strong>/tháng</div>
                            </div>

                            <div class="occupancy-info">
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.775rem; margin-bottom: 3px;">
                                    <span>Sức chứa:</span>
                                    <strong><?= $r['occupied'] ?> / <?= $r['capacity'] ?> người</strong>
                                </div>
                                <div class="occ-bar-wrap">
                                    <div class="occ-bar-fill" style="width: <?= $percent ?>%; background: <?= $percent >= 100 ? '#dc2626' : ($r['status'] === 'Maintenance' ? '#64748b' : '#15803d') ?>;"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state text-center py-5">
            <i class="fa-solid fa-door-closed fa-3x text-muted"></i>
            <p class="margin-top-15 text-muted">Chưa có thông tin sơ đồ phòng kí túc xá.</p>
        </div>
    <?php endif; ?>
</main>

<!-- Modal Chi Tiết Phòng ở -->
<div class="modal" id="roomDetailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="roomModalTitle">Chi Tiết Phòng</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="roomModalBody">
                <p class="text-center py-4"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><br>Đang tải dữ liệu phòng...</p>
            </div>
        </div>
    </div>
</div>



<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
