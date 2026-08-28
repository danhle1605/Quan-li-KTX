<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="container margin-top-20" style="max-width: 760px;">
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn <?= htmlspecialchars($invoice['invoice_code']) ?></h1>
            <p class="text-muted">Chi tiết tiền phòng, điện nước và trạng thái thanh toán</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-secondary"><i class="fa-solid fa-print"></i> In hóa đơn</button>
            <a href="<?= BASE_URL ?>payment/index" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
        </div>
    </div>

    <section class="card printable-area" style="padding: 32px; background: #fff;">
        <div class="text-center margin-bottom-30">
            <h2>HÓA ĐƠN KÝ TÚC XÁ UTH</h2>
            <p>Mã hóa đơn: <strong><?= htmlspecialchars($invoice['invoice_code']) ?></strong></p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px;">
            <div><strong>Phòng:</strong> <?= htmlspecialchars($invoice['room_number']) ?></div>
            <div><strong>Tòa:</strong> <?= htmlspecialchars($invoice['building']) ?></div>
            <div><strong>Kỳ thanh toán:</strong> <?= htmlspecialchars($invoice['billing_month']) ?></div>
            <div><strong>Ngày lập:</strong> <?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?></div>
        </div>
        <table class="table">
            <tbody>
                <tr><td>Tiền phòng</td><td class="text-right"><?= number_format($invoice['room_fee'], 0, ',', '.') ?> VNĐ</td></tr>
                <tr><td>Tiền điện</td><td class="text-right"><?= number_format($invoice['electricity_fee'], 0, ',', '.') ?> VNĐ</td></tr>
                <tr><td>Tiền nước</td><td class="text-right"><?= number_format($invoice['water_fee'], 0, ',', '.') ?> VNĐ</td></tr>
                <tr><th>Tổng cộng</th><th class="text-right text-primary"><?= number_format($invoice['total_amount'], 0, ',', '.') ?> VNĐ</th></tr>
            </tbody>
        </table>
        <p class="margin-top-20"><strong>Trạng thái:</strong>
            <?php if ($invoice['status'] === 'Paid'): ?>
                <span class="badge badge-success">Đã thanh toán<?= $invoice['paid_at'] ? ' ngày ' . date('d/m/Y H:i', strtotime($invoice['paid_at'])) : '' ?></span>
            <?php else: ?>
                <span class="badge badge-danger">Chưa thanh toán</span>
            <?php endif; ?>
        </p>
    </section>
</main>

<style>
@media print {
    body * { visibility: hidden; }
    .printable-area, .printable-area * { visibility: visible; }
    .printable-area { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none !important; }
}
</style>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
