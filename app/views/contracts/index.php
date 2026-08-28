<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="page-container container">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa-solid fa-file-contract text-primary"></i> Quản Lý Hợp Đồng Ở Kí Túc Xá UTH</h1>
            <p class="text-muted">Danh sách hợp đồng ở, thời hạn, tiền cọc và theo dõi trạng thái gia hạn hợp đồng</p>
        </div>
        <div>
            <?php if (Session::get('user_role') === 'admin'): ?>
                <a href="<?= BASE_URL ?>contract/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Tạo Hợp Đồng Mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Thanh Tìm kiếm và Lọc hợp đồng -->
    <div class="filter-card card-box margin-bottom-20">
        <form action="<?= BASE_URL ?>contract/index" method="GET" class="filter-form d-flex gap-3">
            <div class="form-group flex-grow-1" style="margin: 0;">
                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nhập tên sinh viên, MSSV, số phòng..." 
                           value="<?= htmlspecialchars($keyword ?? '') ?>">
                </div>
            </div>

            <div class="form-group" style="margin: 0; min-width: 180px;">
                <select name="status" class="form-control">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Active" <?= ($status ?? '') === 'Active' ? 'selected' : '' ?>>Hiệu lực (Active)</option>
                    <option value="Expired" <?= ($status ?? '') === 'Expired' ? 'selected' : '' ?>>Đã hết hạn (Expired)</option>
                    <option value="Cancelled" <?= ($status ?? '') === 'Cancelled' ? 'selected' : '' ?>>Đã hủy (Cancelled)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Lọc</button>
            <a href="<?= BASE_URL ?>contract/index" class="btn btn-outline"><i class="fa-solid fa-rotate"></i> Đặt lại</a>
        </form>
    </div>

    <!-- Bảng danh sách Hợp đồng -->
    <div class="card-box margin-top-20" style="background: #fff; border-radius: 12px; overflow: hidden; padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th>Mã HĐ</th>
                        <th>Sinh Viên</th>
                        <th>MSSV</th>
                        <th>Phòng Ở</th>
                        <th>Thời Hạn Hợp Đồng</th>
                        <th>Tiền Đặt Cọc</th>
                        <th>Trạng Thái</th>
                        <th class="text-center">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contracts)): ?>
                        <?php foreach ($contracts as $c): ?>
                            <tr>
                                <td><strong>#HĐ-<?= $c['id'] ?></strong></td>
                                <td><strong><?= htmlspecialchars($c['student_name']) ?></strong></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($c['student_code']) ?></span></td>
                                <td>
                                    <span class="badge badge-info">
                                        Phòng <?= htmlspecialchars($c['room_number']) ?> (<?= htmlspecialchars($c['building']) ?>)
                                    </span>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($c['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime($c['end_date'])) ?>
                                    <?php if ($c['status'] === 'Active' && isset($c['days_left']) && $c['days_left'] <= 30 && $c['days_left'] >= 0): ?>
                                        <br><span class="badge badge-warning"><i class="fa-solid fa-clock"></i> Còn <?= $c['days_left'] ?> ngày</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= number_format($c['deposit'], 0, ',', '.') ?> VNĐ</strong></td>
                                <td>
                                    <?php if ($c['status'] === 'Active'): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Hiệu lực</span>
                                    <?php elseif ($c['status'] === 'Expired'): ?>
                                        <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> Hết hạn</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fa-solid fa-ban"></i> Đã hủy</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>contract/detail/<?= $c['id'] ?>" class="btn btn-sm btn-outline" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i> Chi tiết
                                    </a>

                                    <?php if (Session::get('user_role') === 'admin'): ?>
                                        <a href="<?= BASE_URL ?>contract/edit/<?= $c['id'] ?>" class="btn btn-sm btn-info" title="Sửa">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <?php if ($c['status'] === 'Active'): ?>
                                            <button class="btn btn-sm btn-success btn-renew-contract" data-id="<?= $c['id'] ?>" data-end="<?= $c['end_date'] ?>" title="Gia hạn">
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>contract/cancel/<?= $c['id'] ?>" class="btn btn-sm btn-warning btn-delete-confirm" title="Hủy HĐ">
                                                <i class="fa-solid fa-ban"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>contract/delete/<?= $c['id'] ?>" class="btn btn-sm btn-danger btn-delete-confirm" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fa-solid fa-file-circle-xmark fa-2x margin-bottom-10"></i><br>
                                Không tìm thấy hợp đồng ở kí túc xá nào.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Phân trang Pagination -->
    <div class="pagination-container margin-top-30 text-center">
        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="<?= $i == $page ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>contract/index?page=<?= $i ?>&search=<?= urlencode($keyword ?? '') ?>&status=<?= urlencode($status ?? '') ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Pop-up Gia Hạn Hợp Đồng -->
<div class="modal" id="renewContractModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fa-solid fa-clock-rotate-left"></i> Gia Hạn Hợp Đồng Ở KTX</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="renewContractForm">
                    <div class="form-group margin-bottom-20">
                        <label for="renew_end_date"><strong>Ngày Kết Thúc Hợp Đồng Mới (*):</strong></label>
                        <input type="date" id="renew_end_date" name="end_date" class="form-control" required style="padding: 10px;">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Xác Nhận Gia Hạn</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
