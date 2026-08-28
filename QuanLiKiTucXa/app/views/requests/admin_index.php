<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fa-solid fa-right-left text-primary"></i> Quản Lý Yêu Cầu Chuyển / Đăng Ký Phòng</h2>
            <p class="text-muted">Xem xét, duyệt hoặc từ chối các yêu cầu chuyển đổi phòng kí túc xá của sinh viên</p>
        </div>
        <div>
            <span class="badge badge-warning" style="font-size: 14px; padding: 10px 15px;">
                <i class="fa-solid fa-clock"></i> <?= $pendingCount ?? 0 ?> yêu cầu đang chờ duyệt
            </span>
        </div>
    </div>

    <!-- Bộ lọc & Tìm kiếm -->
    <div class="card margin-bottom-20" style="background: #fff; padding: 15px; border-radius: 12px;">
        <form action="<?= BASE_URL ?>request/index" method="GET" class="filter-form d-flex gap-3">
            <div class="form-group flex-grow-1" style="margin: 0;">
                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên SV, MSSV, số phòng..." value="<?= htmlspecialchars($keyword ?? '') ?>">
            </div>
            <div class="form-group" style="margin: 0; min-width: 180px;">
                <select name="status" class="form-control">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Pending" <?= ($status ?? '') === 'Pending' ? 'selected' : '' ?>>Chờ duyệt (Pending)</option>
                    <option value="Approved" <?= ($status ?? '') === 'Approved' ? 'selected' : '' ?>>Đã duyệt (Approved)</option>
                    <option value="Rejected" <?= ($status ?? '') === 'Rejected' ? 'selected' : '' ?>>Từ chối (Rejected)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Lọc</button>
            <a href="<?= BASE_URL ?>request/index" class="btn btn-outline"><i class="fa-solid fa-rotate-left"></i> Đặt lại</a>
        </form>
    </div>

    <!-- Bảng danh sách Yêu cầu -->
    <div class="card" style="background: #fff; border-radius: 12px; overflow: hidden; padding: 0;">
        <table class="table" style="margin: 0;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th>#ID</th>
                    <th>Sinh viên</th>
                    <th>MSSV</th>
                    <th>Phòng hiện tại</th>
                    <th>Phòng muốn chuyển</th>
                    <th>Lý do chuyển phòng</th>
                    <th>Ngày gửi</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td>#<?= $req['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($req['student_name']) ?></strong><br>
                                <small class="text-muted"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($req['phone']) ?></small>
                            </td>
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($req['student_code']) ?></span></td>
                            <td>
                                <?php if ($req['current_room_number']): ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($req['current_room_number']) ?> (<?= htmlspecialchars($req['current_building']) ?>)</span>
                                <?php else: ?>
                                    <span class="text-muted">Chưa ở phòng nào</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-primary">Phòng <?= htmlspecialchars($req['requested_room_number']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($req['requested_building']) ?> (Còn <?= $req['requested_capacity'] - $req['requested_occupied'] ?> chỗ)</small>
                            </td>
                            <td style="max-width: 250px;">
                                <em>"<?= htmlspecialchars($req['reason']) ?>"</em>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></td>
                            <td>
                                <?php if ($req['status'] === 'Pending'): ?>
                                    <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> Chờ duyệt</span>
                                <?php elseif ($req['status'] === 'Approved'): ?>
                                    <span class="badge badge-success"><i class="fa-solid fa-check"></i> Đã duyệt</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> Từ chối</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($req['status'] === 'Pending'): ?>
                                    <a href="<?= BASE_URL ?>request/approve/<?= $req['id'] ?>" class="btn btn-success btn-sm btn-delete-confirm" title="Duyệt yêu cầu">
                                        <i class="fa-solid fa-check"></i> Duyệt
                                    </a>
                                    <a href="<?= BASE_URL ?>request/reject/<?= $req['id'] ?>" class="btn btn-danger btn-sm btn-delete-confirm" title="Từ chối">
                                        <i class="fa-solid fa-xmark"></i> Từ chối
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Đã xử lý</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-inbox fa-2x"></i><br>Không có yêu cầu chuyển phòng nào.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
