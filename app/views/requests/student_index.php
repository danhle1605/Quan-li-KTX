<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fa-solid fa-paper-plane text-primary"></i> Yêu Cầu Chuyển Phòng Của Tôi</h2>
            <p class="text-muted">Theo dõi trạng thái xử lý các yêu cầu chuyển/đăng ký phòng kí túc xá</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>request/create" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Gửi Yêu Cầu Mới
            </a>
        </div>
    </div>

    <div class="card" style="background: #fff; border-radius: 12px; overflow: hidden; padding: 0;">
        <table class="table" style="margin: 0;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th>Mã Yêu Cầu</th>
                    <th>Phòng Hiện Tại</th>
                    <th>Phòng Mong Muốn</th>
                    <th>Lý Do Chuyển</th>
                    <th>Ngày Gửi</th>
                    <th>Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td>#REQ-<?= $req['id'] ?></td>
                            <td>
                                <?php if ($req['current_room_number']): ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($req['current_room_number']) ?> (<?= htmlspecialchars($req['current_building']) ?>)</span>
                                <?php else: ?>
                                    <span class="text-muted">Chưa ở phòng nào</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-primary">Phòng <?= htmlspecialchars($req['requested_room_number']) ?></strong>
                                <small class="text-muted">(<?= htmlspecialchars($req['requested_building']) ?>)</small>
                            </td>
                            <td><em>"<?= htmlspecialchars($req['reason']) ?>"</em></td>
                            <td><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></td>
                            <td>
                                <?php if ($req['status'] === 'Pending'): ?>
                                    <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> Đang chờ Ban Quản Lý duyệt</span>
                                <?php elseif ($req['status'] === 'Approved'): ?>
                                    <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Đã được duyệt thành công</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Yêu cầu bị từ chối</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-paper-plane fa-3x margin-bottom-15" style="color: #cbd5e1;"></i>
                            <p>Bạn chưa gửi yêu cầu chuyển phòng nào.</p>
                            <a href="<?= BASE_URL ?>request/create" class="btn btn-primary btn-sm margin-top-10">Gửi yêu cầu ngay</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
