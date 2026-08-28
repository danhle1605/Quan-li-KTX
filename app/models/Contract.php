<?php

require_once APPROOT . '/core/Model.php';

class Contract extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
        $this->updateContractStatuses();
    }

    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `contracts` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `student_id` INT NOT NULL,
              `room_id` INT NOT NULL,
              `start_date` DATE NOT NULL,
              `end_date` DATE NOT NULL,
              `deposit` DECIMAL(12,2) DEFAULT 0.00,
              `status` ENUM('Active', 'Expired', 'Cancelled') DEFAULT 'Active',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
              FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Ensure contracts table error: " . $e->getMessage());
        }
    }

    /**
     * TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI HỢP ĐỒNG (VI. TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI HỢP ĐỒNG)
     * Quy tắc:
     * - Nếu current_date > end_date và status = 'Active' -> status = 'Expired'
     * - Không được để hợp đồng đã quá end_date vẫn hiển thị Active.
     */
    public function updateContractStatuses() {
        try {
            $sql = "UPDATE contracts 
                    SET status = 'Expired' 
                    WHERE end_date < CURRENT_DATE() AND status = 'Active'";
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Update contract statuses error: " . $e->getMessage());
        }
    }

    public function getAll($keyword = '', $status = '', $page = 1, $limit = 6, $studentId = null) {
        $this->updateContractStatuses();
        $offset = ($page - 1) * $limit;
        $params = [];
        $whereClause = [];

        if (!empty($keyword)) {
            $whereClause[] = "(s.student_code LIKE :kw1 OR s.fullname LIKE :kw2 OR r.room_number LIKE :kw3)";
            $params[':kw1'] = "%$keyword%";
            $params[':kw2'] = "%$keyword%";
            $params[':kw3'] = "%$keyword%";
        }

        if (!empty($status)) {
            $whereClause[] = "c.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($studentId)) {
            $whereClause[] = "c.student_id = :student_id";
            $params[':student_id'] = $studentId;
        }

        $where = !empty($whereClause) ? " WHERE " . implode(" AND ", $whereClause) : "";

        $countSql = "SELECT COUNT(*) as total FROM contracts c 
                    JOIN students s ON c.student_id = s.id 
                    JOIN rooms r ON c.room_id = r.id" . $where;
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $totalRecords = $stmtCount->fetch()['total'];

        $sql = "SELECT c.*, s.fullname as student_name, s.student_code, s.phone, r.room_number, r.building, r.room_type,
                       DATEDIFF(c.end_date, CURRENT_DATE()) as days_left
                FROM contracts c
                JOIN students s ON c.student_id = s.id
                JOIN rooms r ON c.room_id = r.id" . $where . "
                ORDER BY c.id DESC 
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
        $this->updateContractStatuses();
        $sql = "SELECT c.*, s.fullname as student_name, s.student_code, s.phone, s.email, s.faculty,
                       r.room_number, r.building, r.room_type, r.price,
                       DATEDIFF(c.end_date, CURRENT_DATE()) as days_left
                FROM contracts c
                JOIN students s ON c.student_id = s.id
                JOIN rooms r ON c.room_id = r.id
                WHERE c.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getByStudentId($studentId) {
        $this->updateContractStatuses();
        $sql = "SELECT c.*, s.fullname as student_name, s.student_code, r.room_number, r.building, r.room_type,
                       DATEDIFF(c.end_date, CURRENT_DATE()) as days_left
                FROM contracts c
                JOIN students s ON c.student_id = s.id
                JOIN rooms r ON c.room_id = r.id
                WHERE c.student_id = :student_id
                ORDER BY c.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    public function getActiveByStudentId($studentId) {
        $this->updateContractStatuses();
        $sql = "SELECT c.*, s.fullname as student_name, s.student_code, r.room_number, r.building, r.room_type, r.price,
                       DATEDIFF(c.end_date, CURRENT_DATE()) as days_left
                FROM contracts c
                JOIN students s ON c.student_id = s.id
                JOIN rooms r ON c.room_id = r.id
                WHERE c.student_id = :student_id AND c.status = 'Active'
                ORDER BY c.id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetch();
    }

    public function create($data) {
        try {
            // Tự động tính status theo ngày kết thúc
            $currentDate = date('Y-m-d');
            $status = ($currentDate > $data['end_date']) ? 'Expired' : ($data['status'] ?? 'Active');

            $sql = "INSERT INTO contracts (student_id, room_id, start_date, end_date, deposit, status) 
                    VALUES (:student_id, :room_id, :start_date, :end_date, :deposit, :status)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':student_id' => $data['student_id'],
                ':room_id' => $data['room_id'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':deposit' => $data['deposit'],
                ':status' => $status
            ]);
        } catch (PDOException $e) {
            error_log("Contract create error: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $currentDate = date('Y-m-d');
            $status = ($currentDate > $data['end_date']) ? 'Expired' : ($data['status'] ?? 'Active');

            $sql = "UPDATE contracts SET 
                    student_id = :student_id,
                    room_id = :room_id,
                    start_date = :start_date,
                    end_date = :end_date,
                    deposit = :deposit,
                    status = :status 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':student_id' => $data['student_id'],
                ':room_id' => $data['room_id'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':deposit' => $data['deposit'],
                ':status' => $status,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Contract update error: " . $e->getMessage());
            return false;
        }
    }

    public function renew($id, $newEndDate) {
        $currentDate = date('Y-m-d');
        $status = ($currentDate > $newEndDate) ? 'Expired' : 'Active';

        $sql = "UPDATE contracts SET end_date = :end_date, status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':end_date' => $newEndDate, ':status' => $status, ':id' => $id]);
    }

    public function cancel($id) {
        $sql = "UPDATE contracts SET status = 'Cancelled' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM contracts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getActiveContractsCount() {
        $this->updateContractStatuses();
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM contracts WHERE status = 'Active'");
        return $stmt->fetch()['total'];
    }

    public function getExpiredContractsCount() {
        $this->updateContractStatuses();
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM contracts WHERE status = 'Expired'");
        return $stmt->fetch()['total'];
    }

    public function getExpiringContracts($days = 7) {
        $this->updateContractStatuses();
        $sql = "SELECT c.*, s.fullname as student_name, s.student_code, r.room_number, r.building,
                       DATEDIFF(c.end_date, CURRENT_DATE()) as days_left
                FROM contracts c
                JOIN students s ON c.student_id = s.id
                JOIN rooms r ON c.room_id = r.id
                WHERE c.status = 'Active' 
                  AND DATEDIFF(c.end_date, CURRENT_DATE()) BETWEEN 0 AND :days
                ORDER BY c.end_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
