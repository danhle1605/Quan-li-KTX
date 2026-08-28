<?php

require_once APPROOT . '/core/Model.php';

class Payment extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `invoices` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `invoice_code` VARCHAR(50) UNIQUE NOT NULL,
              `room_id` INT NOT NULL,
              `billing_month` VARCHAR(20) NOT NULL,
              `room_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
              `electricity_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
              `water_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
              `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
              `status` ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid',
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              `paid_at` DATETIME NULL,
              FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->exec($sql);

            // Seed dữ liệu mẫu nếu bảng chưa có bản ghi
            $checkStmt = $this->db->query("SELECT COUNT(*) as cnt FROM invoices");
            if ($checkStmt && $checkStmt->fetch()['cnt'] == 0) {
                $seedSql = "INSERT INTO invoices (invoice_code, room_id, billing_month, room_fee, electricity_fee, water_fee, total_amount, status, created_at, paid_at) VALUES
                ('INV-202608-A101', 1, '08/2026', 800000.00, 150000.00, 50000.00, 1000000.00, 'Unpaid', NOW(), NULL),
                ('INV-202608-A102', 2, '08/2026', 1200000.00, 220000.00, 80000.00, 1500000.00, 'Unpaid', NOW(), NULL),
                ('INV-202607-B201', 3, '07/2026', 800000.00, 130000.00, 45000.00, 975000.00, 'Paid', '2026-07-05 10:00:00', '2026-07-10 14:20:00');";
                $this->db->exec($seedSql);
            }
        } catch (PDOException $e) {
            error_log("Ensure invoices table error: " . $e->getMessage());
        }
    }

    public function getAll($keyword = '', $status = '', $page = 1, $limit = 6) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $whereClause = [];

        if (!empty($keyword)) {
            $whereClause[] = "(i.invoice_code LIKE :kw1 OR r.room_number LIKE :kw2 OR i.billing_month LIKE :kw3)";
            $params[':kw1'] = "%$keyword%";
            $params[':kw2'] = "%$keyword%";
            $params[':kw3'] = "%$keyword%";
        }

        if (!empty($status)) {
            $whereClause[] = "i.status = :status";
            $params[':status'] = $status;
        }

        $where = !empty($whereClause) ? " WHERE " . implode(" AND ", $whereClause) : "";

        $countSql = "SELECT COUNT(*) as total FROM invoices i LEFT JOIN rooms r ON i.room_id = r.id" . $where;
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $totalRecords = $stmtCount->fetch()['total'];

        $sql = "SELECT i.*, r.room_number, r.building 
                FROM invoices i
                LEFT JOIN rooms r ON i.room_id = r.id" . $where . "
                ORDER BY i.id DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $totalRecords,
            'page' => $page,
            'totalPages' => ceil($totalRecords / $limit)
        ];
    }

    public function getById($id) {
        $sql = "SELECT i.*, r.room_number, r.building 
                FROM invoices i 
                LEFT JOIN rooms r ON i.room_id = r.id 
                WHERE i.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        try {
            $invoiceCode = 'INV-' . date('Ym') . '-' . ($data['room_number'] ?? rand(100,999));
            $total = (float)$data['room_fee'] + (float)$data['electricity_fee'] + (float)$data['water_fee'];

            $sql = "INSERT INTO invoices (invoice_code, room_id, billing_month, room_fee, electricity_fee, water_fee, total_amount, status) 
                    VALUES (:invoice_code, :room_id, :billing_month, :room_fee, :electricity_fee, :water_fee, :total_amount, :status)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':invoice_code' => $invoiceCode,
                ':room_id' => $data['room_id'],
                ':billing_month' => $data['billing_month'],
                ':room_fee' => $data['room_fee'],
                ':electricity_fee' => $data['electricity_fee'],
                ':water_fee' => $data['water_fee'],
                ':total_amount' => $total,
                ':status' => $data['status'] ?? 'Unpaid'
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log("Payment create error: " . $e->getMessage());
            return false;
        }
    }

    public function markAsPaid($id) {
        $sql = "UPDATE invoices SET status = 'Paid', paid_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM invoices WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getUnpaidCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM invoices WHERE status = 'Unpaid'");
        return $stmt->fetch()['total'];
    }

    /**
     * Tổng số tiền các hóa đơn chưa thanh toán
     */
    public function getUnpaidTotal() {
        $stmt = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE status = 'Unpaid'");
        return (float)$stmt->fetch()['total'];
    }

    /**
     * Tổng doanh thu đã thu được (các hóa đơn Paid)
     */
    public function getPaidRevenue() {
        $stmt = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE status = 'Paid'");
        return (float)$stmt->fetch()['total'];
    }

    /**
     * Danh sách chi tiết hóa đơn chưa thanh toán (cho Smart Alert)
     */
    public function getUnpaidInvoiceList($limit = 5) {
        $sql = "SELECT i.*, r.room_number, r.building 
                FROM invoices i 
                LEFT JOIN rooms r ON i.room_id = r.id 
                WHERE i.status = 'Unpaid' 
                ORDER BY i.created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Thống kê theo từng tháng (dùng cho biểu đồ dashboard, 6 tháng gần nhất)
     */
    public function getMonthlyRevenue($months = 6) {
        $sql = "SELECT billing_month, 
                       SUM(total_amount) as revenue,
                       COUNT(*) as invoice_count
                FROM invoices 
                GROUP BY billing_month 
                ORDER BY MIN(created_at) DESC 
                LIMIT :months";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':months', (int)$months, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
