<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="page-container container">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa-solid fa-door-open text-primary"></i> Quản Lý & Tìm Kiếm Phòng Kí Túc Xá</h1>
            <p class="text-muted">Danh sách các phòng ở, loại phòng, sức chứa và trạng thái chỗ trống</p>
        </div>
        <div class="header-actions d-flex gap-2">
            <a href="<?= BASE_URL ?>room/map" class="btn btn-outline">
                <i class="fa-solid fa-map-location-dot"></i> Xem Dạng Bản Đồ (Room Map)
            </a>
            <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-indigo">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Gợi Ý Phòng Smart Match
            </a>
            <?php if (Session::get('user_role') === 'admin'): ?>
                <a href="<?= BASE_URL ?>room/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Thêm Phòng Mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Thanh Tìm kiếm và Lọc phòng -->
    <div class="filter-card card-box margin-bottom-20">
        <form action="<?= BASE_URL ?>room/index" method="GET" class="filter-form d-flex gap-3 flex-wrap">
            <div class="form-group flex-grow-1" style="margin: 0;">
                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nhập số phòng (A101, B201), từ khóa..." 
                           value="<?= htmlspecialchars($keyword ?? '') ?>">
                </div>
            </div>

            <div class="form-group" style="margin: 0; min-width: 180px;">
                <select name="building" class="form-control">
                    <option value="">-- Tất cả tòa nhà --</option>
                    <?php if (!empty($buildings)): ?>
                        <?php foreach ($buildings as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>" <?= ($building ?? '') === $b ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0; min-width: 160px;">
                <select name="status" class="form-control">
                    <option value="">-- Trạng thái --</option>
                    <option value="Available" <?= ($status ?? '') === 'Available' ? 'selected' : '' ?>>Còn chỗ trống</option>
                    <option value="Full" <?= ($status ?? '') === 'Full' ? 'selected' : '' ?>>Đã lấp đầy</option>
                    <option value="Maintenance" <?= ($status ?? '') === 'Maintenance' ? 'selected' : '' ?>>Đang bảo trì</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Lọc dữ liệu</button>
            <a href="<?= BASE_URL ?>room/index" class="btn btn-outline"><i class="fa-solid fa-rotate"></i> Đặt lại</a>
        </form>
    </div>

    <!-- Danh sách phòng ở dạng Grid Cards -->
    <div class="rooms-grid margin-top-20" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                <div class="room-card card-box" style="background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div class="room-card-header d-flex justify-content-between align-items-center margin-bottom-10">
                            <span class="room-number" style="font-size: 20px; font-weight: 700; color: #1e293b;">Phòng <?= htmlspecialchars($room['room_number']) ?></span>
                            <span class="badge <?= $room['status'] === 'Available' ? 'badge-success' : ($room['status'] === 'Full' ? 'badge-danger' : 'badge-secondary') ?>">
                                <?= $room['status'] === 'Available' ? 'Còn ' . ($room['capacity'] - $room['occupied']) . ' chỗ' : ($room['status'] === 'Full' ? 'Đã đầy' : 'Bảo trì') ?>
                            </span>
                        </div>

                        <div class="room-card-body" style="font-size: 14px; color: #475569;">
                            <p style="margin-bottom: 6px;"><i class="fa-solid fa-building text-muted"></i> <?= htmlspecialchars($room['building']) ?> - Tầng <?= htmlspecialchars($room['floor'] ?? 1) ?></p>
                            <p style="margin-bottom: 6px;"><i class="fa-solid fa-tag text-muted"></i> Loại: <strong><?= htmlspecialchars($room['room_type'] ?? 'Thường') ?></strong></p>
                            <p style="margin-bottom: 12px;" class="text-primary font-weight-bold"><i class="fa-solid fa-money-bill-wave"></i> <?= number_format($room['price'], 0, ',', '.') ?> VNĐ / tháng</p>
                            
                            <div class="room-occupancy margin-top-10">
                                <div class="d-flex justify-content-between text-muted" style="font-size: 12px; margin-bottom: 4px;">
                                    <span>Sức chứa:</span>
                                    <strong><?= $room['occupied'] ?> / <?= $room['capacity'] ?> sinh viên</strong>
                                </div>
                                <div class="mini-progress-bar" style="background: #e2e8f0; border-radius: 6px; height: 8px; overflow: hidden;">
                                    <div class="bar-fill" style="height: 100%; width: <?= round(($room['occupied'] / $room['capacity']) * 100) ?>%; background: <?= $room['status'] === 'Maintenance' ? '#64748b' : ($room['occupied'] >= $room['capacity'] ? '#ef4444' : '#10b981') ?>;"></div>
                                </div>
                            </div>
                            <p class="room-desc margin-top-12 text-muted" style="font-size: 13px; line-height: 1.4;"><?= htmlspecialchars($room['description'] ?? 'Chưa có mô tả.') ?></p>
                        </div>
                    </div>

                    <div class="room-card-footer margin-top-15 d-flex justify-content-between align-items-center" style="border-top: 1px solid #f1f5f9; padding-top: 12px;">
                        <button class="btn btn-sm btn-info btn-view-room-detail" data-id="<?= $room['id'] ?>">
                            <i class="fa-solid fa-eye"></i> Chi tiết
                        </button>
                        
                        <?php if (Session::get('user_role') === 'admin'): ?>
                            <div>
                                <a href="<?= BASE_URL ?>room/edit/<?= $room['id'] ?>" class="btn btn-sm btn-warning" title="Sửa phòng">
                                    <i class="fa-solid fa-pen-to-square"></i> Sửa
                                </a>
                                <a href="<?= BASE_URL ?>room/delete/<?= $room['id'] ?>" class="btn btn-sm btn-danger btn-delete-confirm" title="Xóa phòng">
                                    <i class="fa-solid fa-trash"></i> Xóa
                                </a>
                            </div>
                        <?php elseif (Session::get('user_role') === 'student'): ?>
                            <?php if ($room['status'] === 'Available' && $room['occupied'] < $room['capacity']): ?>
                                <a href="<?= BASE_URL ?>request/create?requested_room_id=<?= $room['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-paper-plane"></i> Đăng ký
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Không thể đăng ký</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data card-box w-100 text-center py-5" style="grid-column: 1 / -1; background: #fff; border-radius: 12px;">
                <p class="text-muted"><i class="fa-solid fa-circle-exclamation fa-2x margin-bottom-10"></i><br>Không tìm thấy phòng kí túc xá nào khớp với điều kiện tìm kiếm.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Phân trang Pagination -->
    <div class="pagination-container margin-top-30 text-center">
        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="<?= $i == $page ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>room/index?page=<?= $i ?>&search=<?= urlencode($keyword ?? '') ?>&building=<?= urlencode($building ?? '') ?>&status=<?= urlencode($status ?? '') ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Pop-up Xem Chi Tiết Phòng & Sinh Viên Đang Ở -->
<div class="modal" id="roomDetailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="roomModalTitle"><i class="fa-solid fa-door-open"></i> Chi Tiết Phòng Kí Túc Xá</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="roomModalBody">
                <p class="text-center py-4">Đang tải thông tin phòng...</p>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
