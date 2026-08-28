<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="page-container container">
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-file-invoice-dollar"></i> Quản Lý Thanh Toán & Hóa Đơn</h1>
            <p>Thu tiền phòng, tiền điện, tiền nước và quản lý trạng thái thanh toán hàng tháng</p>
        </div>
        <div>
            <?php if (Session::has('user_id')): ?>
                <a href="<?= BASE_URL ?>payment/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Lập Hóa Đơn Mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Thanh Tìm kiếm và Lọc hóa đơn -->
    <div class="filter-card card-box">
        <form action="<?= BASE_URL ?>payment/index" method="GET" class="filter-form">
            <div class="form-group flex-2">
                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nhập mã hóa đơn (INV-...), số phòng, tháng..." 
                           value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>

            <div class="form-group flex-1">
                <select name="status" class="form-control">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Unpaid" <?= $status === 'Unpaid' ? 'selected' : '' ?>>Chưa thanh toán (Unpaid)</option>
                    <option value="Paid" <?= $status === 'Paid' ? 'selected' : '' ?>>Đã thanh toán (Paid)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Lọc</button>
            <a href="<?= BASE_URL ?>payment/index" class="btn btn-secondary"><i class="fa-solid fa-rotate"></i> Đặt lại</a>
        </form>
    </div>

    <!-- Bảng danh sách Hóa đơn -->
    <div class="card-box margin-top-20">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã Hóa Đơn</th>
                        <th>Phòng Ở</th>
                        <th>Tháng Đóng</th>
                        <th>Tiền Phòng</th>
                        <th>Tiền Điện</th>
                        <th>Tiền Nước</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoices)): ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($inv['invoice_code']) ?></strong></td>
                                <td>
                                    <span class="badge badge-info">
                                        Phòng <?= htmlspecialchars($inv['room_number']) ?> (<?= htmlspecialchars($inv['building']) ?>)
                                    </span>
                                </td>
                                <td><strong><?= htmlspecialchars($inv['billing_month']) ?></strong></td>
                                <td><?= number_format($inv['room_fee'], 0, ',', '.') ?>đ</td>
                                <td><?= number_format($inv['electricity_fee'], 0, ',', '.') ?>đ</td>
                                <td><?= number_format($inv['water_fee'], 0, ',', '.') ?>đ</td>
                                <td><strong class="text-primary" style="font-size: 1.05rem;"><?= number_format($inv['total_amount'], 0, ',', '.') ?> VNĐ</strong></td>
                                <td>
                                    <?php if ($inv['status'] === 'Paid'): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Đã thanh toán</span>
                                        <?php if ($inv['paid_at']): ?>
                                            <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($inv['paid_at'])) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fa-solid fa-clock"></i> Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td class="table-actions">
                                    <a href="<?= BASE_URL ?>payment/detail/<?= $inv['id'] ?>" class="btn btn-sm btn-secondary" title="Xem hóa đơn">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <?php if (Session::has('user_id')): ?>
                                        <?php if ($inv['status'] === 'Unpaid'): ?>
                                            <a href="<?= BASE_URL ?>payment/pay/<?= $inv['id'] ?>" class="btn btn-sm btn-success" title="Xác nhận thanh toán">
                                                <i class="fa-solid fa-hand-holding-dollar"></i> Thanh toán
                                            </a>
                                        <?php endif; ?>
                                        <?php if (Session::get('user_role') === 'admin'): ?>
                                            <a href="<?= BASE_URL ?>payment/delete/<?= $inv['id'] ?>" class="btn btn-sm btn-danger btn-delete-confirm" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">---</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Chưa có dữ liệu hóa đơn nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Phân trang Pagination -->
    <div class="pagination-container margin-top-30">
        <?php if ($totalPages > 1): ?>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="<?= $i == $page ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>payment/index?page=<?= $i ?>&search=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        <?php endif; ?>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
