<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="dashboard-container container margin-top-20">

<?php if (isset($userRole) && $userRole === 'student'): ?>
    <!-- ==========================================
         GIAO DIỆN STUDENT DASHBOARD
         ========================================== -->
    <div class="dashboard-header margin-bottom-25 d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fa-solid fa-graduation-cap text-primary"></i> Chào mừng, <?= htmlspecialchars(Session::get('user_name')) ?>!</h2>
            <p class="text-muted">Bảng điều khiển cá nhân dành cho Sinh viên KTX UTH</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-outline">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Smart Match
            </a>
            <a href="<?= BASE_URL ?>request/create" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Gửi yêu cầu chuyển phòng
            </a>
        </div>
    </div>

    <div class="dashboard-grid-two">
        <!-- Card 1: Phòng ở hiện tại -->
        <div class="card-box">
            <div class="d-flex justify-content-between align-items-center margin-bottom-15">
                <h3 style="margin: 0;"><i class="fa-solid fa-door-open text-primary"></i> Phòng ở hiện tại</h3>
                <?php if ($currentRoom): ?>
                    <span class="badge badge-success"><?= htmlspecialchars($currentRoom['status']) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($currentRoom): ?>
                <div class="info-block margin-bottom-20">
                    <div style="font-size: 22px; font-weight: 700; color: var(--primary-color);">
                        Phòng <?= htmlspecialchars($currentRoom['room_number']) ?>
                    </div>
                    <div class="text-muted margin-top-5">
                        <i class="fa-solid fa-building"></i> <?= htmlspecialchars($currentRoom['building']) ?> (Tầng <?= $currentRoom['floor'] ?>) | Loại: <strong><?= htmlspecialchars($currentRoom['room_type']) ?></strong>
                    </div>
                    <div class="margin-top-10 font-weight-bold" style="color: var(--text-main);">
                        Giá phòng: <span class="text-primary"><?= number_format($currentRoom['price'], 0, ',', '.') ?> VNĐ</span>/tháng
                    </div>
                    <div class="margin-top-10">
                        <i class="fa-solid fa-users"></i> Sức chứa: <?= $currentRoom['occupied'] ?> / <?= $currentRoom['capacity'] ?> sinh viên
                    </div>
                </div>

                <h4><i class="fa-solid fa-user-group"></i> Bạn cùng phòng (<?= count($roommates) ?>)</h4>
                <ul class="roommate-list margin-top-10">
                    <?php foreach ($roommates as $rm): ?>
                        <li class="roommate-item">
                            <img src="<?= BASE_URL ?>uploads/avatars/<?= htmlspecialchars($rm['avatar']) ?>" class="avatar-thumb" alt="Avatar">
                            <div>
                                <strong><?= htmlspecialchars($rm['fullname']) ?></strong>
                                <small class="text-muted"> (MSSV: <?= htmlspecialchars($rm['student_code']) ?>)</small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-circle-info fa-3x margin-bottom-15 text-warning"></i>
                    <p style="font-size: 15px;">Bạn chưa được đăng ký vào phòng KTX nào.</p>
                    <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-primary margin-top-10">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Tim phòng Smart Match
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card 2: Hợp đồng & Yêu cầu gần nhất -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Hợp đồng cá nhân -->
            <div class="card-box">
                <h3 style="margin-top: 0;"><i class="fa-solid fa-file-contract text-primary"></i> Hợp đồng cá nhân</h3>
                <?php if ($activeContract): ?>
                    <div class="status-block alert-success-block margin-top-15">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Mã HĐ: #HĐ-<?= $activeContract['id'] ?></strong>
                            <span class="badge badge-success">Đang hiệu lực</span>
                        </div>
                        <div class="margin-top-10">
                            <div><strong>Thời hạn:</strong> <?= date('d/m/Y', strtotime($activeContract['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime($activeContract['end_date'])) ?></div>
                            <div><strong>Tiền cọc:</strong> <?= number_format($activeContract['deposit'], 0, ',', '.') ?> VNĐ</div>
                            <div class="margin-top-5 text-success font-weight-bold">
                                <i class="fa-solid fa-clock"></i> Còn <?= $activeContract['days_left'] ?> ngày hiệu lực
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>contract/detail/<?= $activeContract['id'] ?>" class="btn btn-outline btn-sm margin-top-10">
                            Xem hợp đồng chi tiết
                        </a>
                    </div>
                <?php else: ?>
                    <p class="text-muted margin-top-15">Bạn chưa có hợp đồng ở KTX đang hoạt động.</p>
                <?php endif; ?>
            </div>

            <!-- Yêu cầu chuyển phòng mới nhất -->
            <div class="card-box">
                <h3 style="margin-top: 0;"><i class="fa-solid fa-paper-plane text-primary"></i> Trạng thái yêu cầu gần nhất</h3>
                <?php if ($latestRequest): ?>
                    <div class="info-block margin-top-15">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Phòng muốn chuyển: <strong>Phòng <?= htmlspecialchars($latestRequest['requested_room_number']) ?></strong></span>
                            <?php if ($latestRequest['status'] === 'Pending'): ?>
                                <span class="badge badge-warning">Đang chờ duyệt</span>
                            <?php elseif ($latestRequest['status'] === 'Approved'): ?>
                                <span class="badge badge-success">Đã duyệt</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Từ chối</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted margin-top-5" style="font-size: 13px;">
                            Lý do: "<?= htmlspecialchars($latestRequest['reason']) ?>"
                        </div>
                        <div class="text-muted margin-top-5" style="font-size: 12px;">
                            Ngày gửi: <?= date('d/m/Y H:i', strtotime($latestRequest['created_at'])) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted margin-top-15">Bạn chưa gửi yêu cầu chuyển phòng nào.</p>
                    <a href="<?= BASE_URL ?>request/create" class="btn btn-primary btn-sm">Gửi yêu cầu chuyển phòng</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ==========================================
         GIAO DIỆN ADMIN DASHBOARD
         ========================================== -->
    <div class="dashboard-header d-flex justify-content-between align-items-center margin-bottom-25">
        <div>
            <h1><i class="fa-solid fa-gauge-high text-primary"></i> Tổng quan quản lý KTX</h1>
            <p class="text-muted">Thống kê toàn hệ thống, cảnh báo hợp đồng & quản lý duyệt yêu cầu</p>
        </div>
        <div class="header-actions d-flex gap-2">
            <a href="<?= BASE_URL ?>room/map" class="btn btn-outline">
                <i class="fa-solid fa-map-location-dot"></i> Bản đồ phòng
            </a>
            <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-outline">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Smart Match
            </a>
            <a href="<?= BASE_URL ?>room/create" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Thêm phòng
            </a>
            <a href="<?= BASE_URL ?>student/create" class="btn btn-primary">
                <i class="fa-solid fa-user-plus"></i> Thêm sinh viên
            </a>
        </div>
    </div>

    <!-- SMART ALERT PANEL (Cảnh báo khẩn cho Admin) -->
    <?php if (!empty($smartAlerts)): ?>
    <div class="smart-alert-panel margin-bottom-25" id="smartAlertPanel">
        <div class="smart-alert-header d-flex justify-content-between align-items-center margin-bottom-15">
            <h3 style="margin:0;"><i class="fa-solid fa-bell text-warning"></i> Cảnh báo hệ thống</h3>
            <button class="btn-icon" id="btnToggleAlerts" aria-label="Toggle alerts">
                <i class="fa-solid fa-chevron-up" id="iconToggleAlerts"></i>
            </button>
        </div>
        <div class="alert-items-list" id="alertItemsList">
            <?php foreach ($smartAlerts as $alert): ?>
                <div class="smart-alert-item alert-<?= htmlspecialchars($alert['level']) ?>">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fa-solid <?= htmlspecialchars($alert['icon']) ?>" style="font-size: 16px;"></i>
                        <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($alert['message']) ?></span>
                    </div>
                    <a href="<?= $alert['link'] ?>" class="btn btn-sm btn-primary">
                        <?= htmlspecialchars($alert['label']) ?> <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- STATS CARDS (4 KPI chính) -->
    <div class="stats-grid margin-bottom-25">
        <div class="stat-card" onclick="window.location='<?= BASE_URL ?>student/index'" style="cursor:pointer;">
            <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
            <div class="stat-info">
                <h3><?= $totalStudents ?></h3>
                <p>Tổng sinh viên</p>
            </div>
        </div>

        <div class="stat-card" onclick="window.location='<?= BASE_URL ?>room/index'" style="cursor:pointer;">
            <div class="stat-icon"><i class="fa-solid fa-door-open"></i></div>
            <div class="stat-info">
                <h3><?= $totalRooms ?></h3>
                <p>Tổng số phòng KTX</p>
                <small><?= $availableRooms ?> trống · <?= $fullRooms ?> đầy · <?= $maintenanceRooms ?> bảo trì</small>
            </div>
        </div>

        <div class="stat-card" onclick="window.location='<?= BASE_URL ?>contract/index'" style="cursor:pointer;">
            <div class="stat-icon"><i class="fa-solid fa-file-contract"></i></div>
            <div class="stat-info">
                <h3><?= $activeContracts ?></h3>
                <p>Hợp đồng hiệu lực</p>
                <?php if (count($expiringContracts7) > 0): ?>
                    <small class="text-danger">⚠ <?= count($expiringContracts7) ?> hết hạn trong 7 ngày</small>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card card-alert-item" onclick="window.location='<?= BASE_URL ?>request/index'" style="cursor:pointer;">
            <div class="stat-icon icon-warning"><i class="fa-solid fa-right-left"></i></div>
            <div class="stat-info">
                <h3><?= $pendingRequestsCount ?></h3>
                <p>Yêu cầu chờ duyệt</p>
                <small class="text-warning">Cần xem xét ngay</small>
            </div>
        </div>
    </div>

    <!-- STATS ROW 2: TỶ LỆ SỬ DỤNG + DOANH THU -->
    <div class="dashboard-grid-two margin-bottom-25">
        <div class="card-box">
            <div class="section-title d-flex justify-content-between align-items-center">
                <h3><i class="fa-solid fa-chart-pie text-primary"></i> Tỷ lệ sử dụng sức chứa phòng</h3>
                <span class="text-muted"><?= $occupiedSeats ?> / <?= $totalCapacity ?> chỗ đang ở</span>
            </div>

            <?php $pct = $totalCapacity > 0 ? round(($occupiedSeats / $totalCapacity) * 100) : 0; ?>
            <div class="occupancy-visual margin-top-15">
                <div class="occ-bar-wrap">
                    <div class="occ-bar-fill" style="width: <?= $pct ?>%;">
                        <span><?= $pct ?>%</span>
                    </div>
                </div>
            </div>

            <div class="margin-top-20">
                <canvas id="chartBuilding" height="160"></canvas>
            </div>
        </div>

        <div class="card-box">
            <div class="section-title">
                <h3><i class="fa-solid fa-money-bill-trend-up text-primary"></i> Doanh thu & thanh toán điện nước</h3>
            </div>

            <div class="revenue-stats-grid margin-top-15">
                <div class="rev-stat-box rev-box-paid">
                    <div class="rev-num text-success"><?= number_format($paidRevenue, 0, ',', '.') ?>đ</div>
                    <div class="text-muted" style="font-size: 13px;">Đã thanh toán</div>
                </div>
                <div class="rev-stat-box rev-box-unpaid">
                    <div class="rev-num text-danger"><?= number_format($unpaidTotal, 0, ',', '.') ?>đ</div>
                    <div class="text-muted" style="font-size: 13px;">Chưa thanh toán (<?= $unpaidInvoices ?> HĐ)</div>
                </div>
            </div>

            <div class="margin-top-20">
                <canvas id="chartRoomStatus" height="140"></canvas>
            </div>
        </div>
    </div>

    <!-- BẢNG SINH VIÊN MỚI -->
    <div class="card-box">
        <div class="section-title d-flex justify-content-between align-items-center margin-bottom-15">
            <h3><i class="fa-solid fa-users text-primary"></i> Sinh viên gần đây</h3>
            <a href="<?= BASE_URL ?>student/index" class="btn btn-outline btn-sm">Xem danh sách sinh viên</a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>MSSV</th>
                        <th>Họ và Tên</th>
                        <th>Khoa</th>
                        <th>Phòng Đang Ở</th>
                        <th>Số điện thoại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentStudents)): ?>
                        <?php foreach ($recentStudents as $student): ?>
                            <tr>
                                <td>
                                    <img src="<?= BASE_URL ?>uploads/avatars/<?= htmlspecialchars($student['avatar']) ?>" class="avatar-thumb" alt="Avatar">
                                </td>
                                <td><strong><?= htmlspecialchars($student['student_code']) ?></strong></td>
                                <td><?= htmlspecialchars($student['fullname']) ?></td>
                                <td><?= htmlspecialchars($student['faculty']) ?></td>
                                <td>
                                    <?php if ($student['room_number']): ?>
                                        <span class="badge badge-success">Phòng <?= htmlspecialchars($student['room_number']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Chưa xếp phòng</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($student['phone']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

</main>

<?php if (isset($userRole) && $userRole === 'admin'): ?>
<!-- Chart.js CDN (Biểu đồ) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const buildingData = <?= json_encode($roomStatsByBuilding ?? []) ?>;

    if (buildingData.length > 0 && document.getElementById('chartBuilding')) {
        const labels = buildingData.map(b => b.building);
        const capacityData = buildingData.map(b => parseInt(b.total_capacity));
        const occupiedData = buildingData.map(b => parseInt(b.total_occupied));

        new Chart(document.getElementById('chartBuilding'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Tổng chỗ',
                        data: capacityData,
                        backgroundColor: 'rgba(109, 40, 217, 0.15)',
                        borderColor: '#6d28d9',
                        borderWidth: 1.5,
                        borderRadius: 2
                    },
                    {
                        label: 'Đã có người',
                        data: occupiedData,
                        backgroundColor: 'rgba(21, 128, 61, 0.7)',
                        borderColor: '#15803d',
                        borderWidth: 1.5,
                        borderRadius: 2
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    const available = <?= (int)($availableRooms ?? 0) ?>;
    const full = <?= (int)($fullRooms ?? 0) ?>;
    const maintenance = <?= (int)($maintenanceRooms ?? 0) ?>;

    if (document.getElementById('chartRoomStatus')) {
        new Chart(document.getElementById('chartRoomStatus'), {
            type: 'doughnut',
            data: {
                labels: ['Còn chỗ (Available)', 'Đã đầy (Full)', 'Bảo trì (Maintenance)'],
                datasets: [{
                    data: [available, full, maintenance],
                    backgroundColor: ['#15803d', '#dc2626', '#d97706'],
                    borderColor: ['#fff', '#fff', '#fff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 } } }
                }
            }
        });
    }
})();
</script>
<?php endif; ?>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
