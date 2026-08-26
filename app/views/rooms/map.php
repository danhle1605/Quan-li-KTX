<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fa-solid fa-map-location-dot text-primary"></i> Bản Đồ Trạng Thái Phòng Kí Túc Xá (Room Map)</h2>
            <p class="text-muted">Theo dõi và quản lý các phòng kí túc xá theo từng Tòa nhà thực tế</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-secondary">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Gợi ý phòng Smart
            </a>
            <a href="<?= BASE_URL ?>room/index" class="btn btn-outline">
                <i class="fa-solid fa-list"></i> Xem dạng danh sách
            </a>
        </div>
    </div>

    <!-- Chú thích màu sắc (Legend) -->
    <div class="card margin-bottom-20" style="background: #ffffff; border-radius: 12px; padding: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div style="display: flex; gap: 25px; align-items: center; flex-wrap: wrap;">
            <strong><i class="fa-solid fa-circle-info"></i> Chú thích trạng thái:</strong>
            <span class="badge badge-success" style="padding: 8px 12px; font-size: 14px;"><i class="fa-solid fa-check"></i> Available (Còn chỗ trống)</span>
            <span class="badge badge-danger" style="padding: 8px 12px; font-size: 14px;"><i class="fa-solid fa-user-group"></i> Full (Đã đầy)</span>
            <span class="badge badge-secondary" style="padding: 8px 12px; font-size: 14px; background: #64748b;"><i class="fa-solid fa-wrench"></i> Maintenance (Đang bảo trì)</span>
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

                <div class="room-map-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
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
                        <div class="room-card-item btn-view-room-detail <?= $statusClass ?>" data-id="<?= $r['id'] ?>" style="cursor: pointer; background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 6px solid #10b981; transition: transform 0.2s, box-shadow 0.2s;">
                            <div class="d-flex justify-content-between align-items-center margin-bottom-10">
                                <h4 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">
                                    Phòng <?= htmlspecialchars($r['room_number']) ?>
                                </h4>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r['status']) ?></span>
                            </div>

                            <div style="font-size: 13px; color: #64748b; margin-bottom: 10px;">
                                <div><i class="fa-solid fa-layer-group"></i> Tầng <?= $r['floor'] ?> | Loại: <strong><?= htmlspecialchars($r['room_type']) ?></strong></div>
                                <div><i class="fa-solid fa-tag"></i> <strong><?= number_format($r['price'], 0, ',', '.') ?> VNĐ</strong>/tháng</div>
                            </div>

                            <!-- Occupancy Progress Bar -->
                            <div class="occupancy-info margin-top-10">
                                <div class="d-flex justify-content-between text-muted" style="font-size: 12px; margin-bottom: 4px;">
                                    <span><i class="fa-solid fa-users"></i> Sức chứa:</span>
                                    <strong><?= $r['occupied'] ?> / <?= $r['capacity'] ?> người</strong>
                                </div>
                                <div class="progress-bar-bg" style="background: #e2e8f0; border-radius: 6px; height: 8px; overflow: hidden;">
                                    <div class="progress-bar-fill" style="width: <?= $percent ?>%; height: 100%; background: <?= $percent >= 100 ? '#ef4444' : ($r['status'] === 'Maintenance' ? '#64748b' : '#10b981') ?>;"></div>
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

<style>
.border-available { border-left-color: #10b981 !important; }
.border-full { border-left-color: #ef4444 !important; }
.border-maintenance { border-left-color: #64748b !important; }
.room-card-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
