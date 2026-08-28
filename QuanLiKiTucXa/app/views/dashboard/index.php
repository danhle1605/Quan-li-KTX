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
            <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-indigo">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Gợi Ý Phòng Smart Match
            </a>
            <a href="<?= BASE_URL ?>request/create" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Chuyển Phòng
            </a>
        </div>
    </div>

    <div class="dashboard-grid-two">
        <!-- Card 1: Phòng ở hiện tại -->
        <div class="card-box" style="background: #ffffff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div class="d-flex justify-content-between align-items-center margin-bottom-15">
                <h3 style="margin: 0; color: #1e293b;"><i class="fa-solid fa-door-open text-indigo"></i> Phòng Ở Hiện Tại</h3>
                <?php if ($currentRoom): ?>
                    <span class="badge badge-success"><?= htmlspecialchars($currentRoom['status']) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($currentRoom): ?>
                <div style="background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                    <div style="font-size: 24px; font-weight: 800; color: #4f46e5;">
                        Phòng <?= htmlspecialchars($currentRoom['room_number']) ?>
                    </div>
                    <div class="text-muted margin-top-5">
                        <i class="fa-solid fa-building"></i> <?= htmlspecialchars($currentRoom['building']) ?> (Tầng <?= $currentRoom['floor'] ?>) | Loại: <strong><?= htmlspecialchars($currentRoom['room_type']) ?></strong>
                    </div>
                    <div class="margin-top-10 font-weight-bold" style="color: #0f172a;">
                        Giá phòng: <span class="text-primary"><?= number_format($currentRoom['price'], 0, ',', '.') ?> VNĐ</span>/tháng
                    </div>
                    <div class="margin-top-10">
                        <i class="fa-solid fa-users"></i> Sức chứa: <?= $currentRoom['occupied'] ?> / <?= $currentRoom['capacity'] ?> sinh viên
                    </div>
                </div>

                <h4><i class="fa-solid fa-user-group"></i> Bạn Cùng Phòng (<?= count($roommates) ?>)</h4>
                <ul class="margin-top-10" style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 8px;">
                    <?php foreach ($roommates as $rm): ?>
                        <li style="display: flex; align-items: center; gap: 10px; background: #fff; padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <img src="<?= BASE_URL ?>uploads/avatars/<?= htmlspecialchars($rm['avatar']) ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
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
                    <p style="font-size: 16px;">Bạn chưa được đăng ký vào phòng KTX nào.</p>
                    <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-primary margin-top-10">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Tìm phòng phù hợp ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card 2: Hợp đồng & Yêu cầu gần nhất -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Hợp đồng cá nhân -->
            <div class="card-box" style="background: #ffffff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; color: #1e293b;"><i class="fa-solid fa-file-contract text-primary"></i> Hợp Đồng Cá Nhân</h3>
                <?php if ($activeContract): ?>
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 10px;" class="margin-top-15">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Mã HĐ: #HĐ-<?= $activeContract['id'] ?></strong>
                            <span class="badge badge-success">Active</span>
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
            <div class="card-box" style="background: #ffffff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; color: #1e293b;"><i class="fa-solid fa-paper-plane text-primary"></i> Trạng Thái Yêu Cầu Gần Nhất</h3>
                <?php if ($latestRequest): ?>
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px;" class="margin-top-15">
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
            <h1><i class="fa-solid fa-gauge-high text-primary"></i> Admin Dashboard – Smart KTX UTH</h1>
            <p class="text-muted">Thống kê toàn hệ thống, cảnh báo hợp đồng & quản lý duyệt yêu cầu</p>
        </div>
        <div class="header-actions d-flex gap-2">
            <a href="<?= BASE_URL ?>room/map" class="btn btn-outline">
                <i class="fa-solid fa-map-location-dot"></i> Bản Đồ Phòng
            </a>
            <a href="<?= BASE_URL ?>room/smartMatch" class="btn btn-indigo">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Gợi Ý Smart Match
            </a>
            <a href="<?= BASE_URL ?>room/create" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Thêm Phòng
            </a>
            <a href="<?= BASE_URL ?>student/create" class="btn btn-success">
                <i class="fa-solid fa-user-plus"></i> Thêm Sinh Viên
            </a>
        </div>
    </div>

    <!-- SMART ALERT PANEL (Cảnh báo khẩn cho Admin) -->
    <?php if (!empty($smartAlerts)): ?>
    <div class="smart-alert-panel card-box margin-bottom-25" id="smartAlertPanel" style="background: #fff; border-left: 6px solid #f59e0b; border-radius: 12px; padding: 20px;">
        <div class="smart-alert-header d-flex justify-content-between align-items-center margin-bottom-15">
            <h3 style="margin:0;"><i class="fa-solid fa-bell fa-beat" style="color: #f59e0b;"></i> Cảnh Báo Hệ Thống (Smart Alerts Panel)</h3>
            <button class="btn-icon" id="btnToggleAlerts" style="background: none; border: none; cursor: pointer;">
                <i class="fa-solid fa-chevron-up" id="iconToggleAlerts"></i>
            </button>
        </div>
        <div class="alert-items-list" id="alertItemsList" style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($smartAlerts as $alert): ?>
                <div class="smart-alert-item alert-<?= htmlspecialchars($alert['level']) ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fa-solid <?= htmlspecialchars($alert['icon']) ?>" style="font-size: 18px;"></i>
                        <span style="font-size: 15px; font-weight: 600;"><?= htmlspecialchars($alert['message']) ?></span>
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
        <div class="stat-card card-blue" onclick="window.location='<?= BASE_URL ?>student/index'" style="cursor:pointer;">
            <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
            <div class="stat-info">
                <h3><?= $totalStudents ?></h3>
                <p>Tổng Sinh Viên</p>
            </div>
        </div>

        <div class="stat-card card-green" onclick="window.location='<?= BASE_URL ?>room/index'" style="cursor:pointer;">
            <div class="stat-icon"><i class="fa-solid fa-door-open"></i></div>
            <div class="stat-info">
                <h3><?= $totalRooms ?></h3>
                <p>Tổng Số Phòng KTX</p>
                <small><?= $availableRooms ?> trống · <?= $fullRooms ?> đầy · <?= $maintenanceRooms ?> bảo trì</small>
            </div>
        </div>

        <div class="stat-card card-purple" onclick="window.location='<?= BASE_URL ?>contract/index'" style="cursor:pointer;">
            <div class="stat-icon"><i class="fa-solid fa-file-contract"></i></div>
            <div class="stat-info">
                <h3><?= $activeContracts ?></h3>
                <p>Hợp Đồng Active</p>
                <?php if (count($expiringContracts7) > 0): ?>
                    <small class="text-danger">⚠ <?= count($expiringContracts7) ?> hết hạn trong 7 ngày</small>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card card-orange" onclick="window.location='<?= BASE_URL ?>request/index'" style="cursor:pointer;">
            <div class="stat-icon"><i class="fa-solid fa-right-left"></i></div>
            <div class="stat-info">
                <h3><?= $pendingRequestsCount ?></h3>
                <p>Yêu Cầu Chờ Duyệt</p>
                <small class="text-warning">🔔 Cần xem xét ngay</small>
            </div>
        </div>
    </div>

    <!-- STATS ROW 2: TỶ LỆ SỬ DỤNG + DOANH THU -->
    <div class="dashboard-grid-two margin-bottom-25">
        <div class="card-box" style="background: #fff; padding: 20px; border-radius: 12px;">
            <div class="section-title d-flex justify-content-between align-items-center">
                <h3><i class="fa-solid fa-chart-pie text-indigo"></i> Tỷ Lệ Sử Dụng Sức Chứa Phòng</h3>
                <span class="text-muted"><?= $occupiedSeats ?> / <?= $totalCapacity ?> chỗ đang ở</span>
            </div>

            <?php $pct = $totalCapacity > 0 ? round(($occupiedSeats / $totalCapacity) * 100) : 0; ?>
            <div class="occupancy-visual margin-top-15">
                <div class="occ-bar-wrap" style="background: #e2e8f0; height: 20px; border-radius: 10px; overflow: hidden;">
                    <div class="occ-bar-fill" style="width: <?= $pct ?>%; background: #10b981; height: 100%; text-align: center; color: #fff; font-size: 12px; font-weight: bold; line-height: 20px;">
                        <?= $pct ?>%
                    </div>
                </div>
            </div>

            <div class="margin-top-20">
                <canvas id="chartBuilding" height="160"></canvas>
            </div>
        </div>

        <div class="card-box" style="background: #fff; padding: 20px; border-radius: 12px;">
            <div class="section-title">
                <h3><i class="fa-solid fa-money-bill-trend-up text-success"></i> Doanh Thu & Thanh Toán Điện Nước</h3>
            </div>

            <div class="revenue-stats-grid margin-top-15" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="background: #f0fdf4; padding: 15px; border-radius: 10px; border: 1px solid #bbf7d0;">
                    <div style="font-size: 20px; font-weight: bold; color: #16a34a;"><?= number_format($paidRevenue, 0, ',', '.') ?>đ</div>
                    <div class="text-muted" style="font-size: 13px;">Đã Thanh Toán</div>
                </div>
                <div style="background: #fef2f2; padding: 15px; border-radius: 10px; border: 1px solid #fecaca;">
                    <div style="font-size: 20px; font-weight: bold; color: #dc2626;"><?= number_format($unpaidTotal, 0, ',', '.') ?>đ</div>
                    <div class="text-muted" style="font-size: 13px;">Chưa Thanh Toán (<?= $unpaidInvoices ?> HĐ)</div>
                </div>
            </div>

            <div class="margin-top-20">
                <canvas id="chartRoomStatus" height="140"></canvas>
            </div>
        </div>
    </div>

    <!-- BẢNG SINH VIÊN MỚI -->
    <div class="card-box" style="background: #fff; padding: 20px; border-radius: 12px;">
        <div class="section-title d-flex justify-content-between align-items-center margin-bottom-15">
            <h3><i class="fa-solid fa-users text-primary"></i> Sinh Viên Gần Đây</h3>
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
                                    <img src="<?= BASE_URL ?>uploads/avatars/<?= htmlspecialchars($student['avatar']) ?>" class="avatar-thumb">
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
                        backgroundColor: 'rgba(99, 102, 241, 0.2)',
                        borderColor: '#6366f1',
                        borderWidth: 2,
                        borderRadius: 4
                    },
                    {
                        label: 'Đã có người',
                        data: occupiedData,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: '#10b981',
                        borderWidth: 2,
                        borderRadius: 4
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
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                    borderColor: ['#fff', '#fff', '#fff'],
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
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
