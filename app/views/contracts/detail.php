<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20" style="max-width: 800px;">
    <div class="page-header d-flex justify-content-between align-items-center margin-bottom-30">
        <div>
            <h2><i class="fa-solid fa-file-contract text-primary"></i> Chi Tiết Hợp Đồng KTX #<?= $contract['id'] ?></h2>
            <p class="text-muted">Thông tin pháp lý hợp đồng ở kí túc xá UTH giữa Ban Quản Lý và Sinh viên</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fa-solid fa-print"></i> In Hợp Đồng
            </button>
            <a href="<?= BASE_URL ?>contract/index" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Khung Hợp Đồng Xem Chi Tiết -->
    <div class="card printable-area" style="background: #ffffff; border-radius: 16px; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
        <div class="text-center margin-bottom-30">
            <h3 style="margin: 0; color: #1e293b; text-transform: uppercase;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</h3>
            <p style="margin: 5px 0; font-weight: bold;">Độc lập - Tự do - Hạnh phúc</p>
            <div style="width: 150px; height: 2px; background: #94a3b8; margin: 10px auto;"></div>
            <h2 class="margin-top-20" style="color: #4f46e5; font-size: 24px;">HỢP ĐỒNG CHO THUÊ PHÒNG KÍ TÚC XÁ UTH</h2>
            <p class="text-muted">Mã số hợp đồng: <strong>HĐ-KTX-<?= str_pad($contract['id'], 6, '0', STR_PAD_LEFT) ?></strong></p>
        </div>

        <!-- Thông tin bên cho thuê & bên thuê -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; background: #f8fafc; padding: 20px; border-radius: 12px;">
            <div>
                <h4 style="margin-top: 0; color: #4f46e5;"><i class="fa-solid fa-building"></i> BÊN CHO THUÊ (BÊN A)</h4>
                <div style="font-size: 14px; line-height: 1.6;">
                    <div><strong>Đơn vị:</strong> Ban Quản Lý Kí Túc Xá Trường ĐH GTVT TP.HCM (UTH)</div>
                    <div><strong>Địa chỉ:</strong> Đường Võ Văn Ngân, TP. Thủ Đức, TP.HCM</div>
                    <div><strong>Đại diện:</strong> Quản Trị Viên KTX UTH</div>
                </div>
            </div>

            <div>
                <h4 style="margin-top: 0; color: #4f46e5;"><i class="fa-solid fa-user-graduate"></i> BÊN THUÊ (BÊN B)</h4>
                <div style="font-size: 14px; line-height: 1.6;">
                    <div><strong>Họ và tên:</strong> <?= htmlspecialchars($contract['student_name']) ?></div>
                    <div><strong>Mã số sinh viên:</strong> <?= htmlspecialchars($contract['student_code']) ?></div>
                    <div><strong>SĐT liên hệ:</strong> <?= htmlspecialchars($contract['phone'] ?? 'Chưa có') ?></div>
                    <div><strong>Email:</strong> <?= htmlspecialchars($contract['email'] ?? 'Chưa có') ?></div>
                </div>
            </div>
        </div>

        <!-- Điều khoản phòng & giá -->
        <div class="margin-bottom-25" style="font-size: 15px; line-height: 1.8;">
            <h4 style="color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;"><i class="fa-solid fa-door-open"></i> ĐIỀU 1: ĐIỀU KHOẢN PHÒNG Ở & THỜI HẠN</h4>
            <div>Bên A đồng ý cho Bên B ở phòng kí túc xá với các thông số chi tiết như sau:</div>
            <ul style="padding-left: 20px; margin-top: 8px;">
                <li><strong>Phòng ở:</strong> Phòng <strong><?= htmlspecialchars($contract['room_number']) ?></strong> - <strong><?= htmlspecialchars($contract['building']) ?></strong></li>
                <li><strong>Loại phòng:</strong> <?= htmlspecialchars($contract['room_type'] ?? 'Thường') ?></li>
                <li><strong>Ngày bắt đầu hợp đồng:</strong> <?= date('d/m/Y', strtotime($contract['start_date'])) ?></li>
                <li><strong>Ngày kết thúc hợp đồng:</strong> <?= date('d/m/Y', strtotime($contract['end_date'])) ?></li>
                <li><strong>Trạng thái hợp đồng:</strong> 
                    <?php if ($contract['status'] === 'Active'): ?>
                        <span class="badge badge-success">Đang Hiệu Lực</span>
                    <?php elseif ($contract['status'] === 'Expired'): ?>
                        <span class="badge badge-danger">Đã Hết Hạn</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Đã Hủy Hợp Đồng</span>
                    <?php endif; ?>
                </li>
            </ul>

            <h4 style="color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;" class="margin-top-20"><i class="fa-solid fa-coins"></i> ĐIỀU 2: GIÁ THUÊ & TIỀN ĐẶT CỌC</h4>
            <ul style="padding-left: 20px; margin-top: 8px;">
                <li><strong>Giá thuê phòng hàng tháng:</strong> <?= number_format($contract['price'] ?? 0, 0, ',', '.') ?> VNĐ/tháng</li>
                <li><strong>Tiền đặt cọc hợp đồng:</strong> <strong class="text-primary"><?= number_format($contract['deposit'], 0, ',', '.') ?> VNĐ</strong></li>
                <li><strong>Phương thức thanh toán:</strong> Đóng theo từng tháng hoặc kỳ học. Tiền cọc sẽ được hoàn trả khi hết hạn hợp đồng.</li>
            </ul>
        </div>

        <!-- Chữ ký -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 40px; text-align: center;">
            <div>
                <strong>ĐẠI DIỆN BÊN CHO THUÊ (BÊN A)</strong>
                <p class="text-muted margin-top-5">(Ký và ghi rõ họ tên)</p>
                <div style="height: 80px;"></div>
                <strong>Quản Trị Viên KTX UTH</strong>
            </div>
            <div>
                <strong>ĐẠI DIỆN BÊN THUÊ (BÊN B)</strong>
                <p class="text-muted margin-top-5">(Ký và ghi rõ họ tên)</p>
                <div style="height: 80px;"></div>
                <strong><?= htmlspecialchars($contract['student_name']) ?></strong>
            </div>
        </div>
    </div>
</main>

<style>
@media print {
    body * { visibility: hidden; }
    .printable-area, .printable-area * { visibility: visible; }
    .printable-area { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none !important; border: none !important; }
}
</style>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
