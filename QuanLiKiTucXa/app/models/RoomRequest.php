<?php

require_once APPROOT . '/core/Model.php';

class RoomRequest extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `room_requests` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `student_id` INT NOT NULL,
              `current_room_id` INT NULL,
              `requested_room_id` INT NOT NULL,
              `request_type` ENUM('registration', 'transfer') DEFAULT 'transfer',
              `reason` TEXT NOT NULL,
              `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
              FOREIGN KEY (`current_room_id`) REFERENCES `rooms`(`id`) ON DELETE SET NULL,
              FOREIGN KEY (`requested_room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Ensure room_requests table error: " . $e->getMessage());
        }
    }

    public function getAll($keyword = '', $status = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $whereClause = [];

        if (!empty($keyword)) {
            $whereClause[] = "(s.student_code LIKE :kw1 OR s.fullname LIKE :kw2 OR req_r.room_number LIKE :kw3)";
            $params[':kw1'] = "%$keyword%";
            $params[':kw2'] = "%$keyword%";
            $params[':kw3'] = "%$keyword%";
        }

        if (!empty($status)) {
            $whereClause[] = "rr.status = :status";
            $params[':status'] = $status;
        }

        $where = !empty($whereClause) ? " WHERE " . implode(" AND ", $whereClause) : "";

        $countSql = "SELECT COUNT(*) as total FROM room_requests rr 
                    JOIN students s ON rr.student_id = s.id 
                    JOIN rooms req_r ON rr.requested_room_id = req_r.id" . $where;
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $totalRecords = $stmtCount->fetch()['total'];

        $sql = "SELECT rr.*, 
                       s.fullname as student_name, s.student_code, s.phone,
                       curr_r.room_number as current_room_number, curr_r.building as current_building,
                       req_r.room_number as requested_room_number, req_r.building as requested_building,
                       req_r.capacity as requested_capacity, req_r.occupied as requested_occupied,
                       req_r.status as requested_room_status
                FROM room_requests rr
                JOIN students s ON rr.student_id = s.id
                LEFT JOIN rooms curr_r ON rr.current_room_id = curr_r.id
                JOIN rooms req_r ON rr.requested_room_id = req_r.id" . $where . "
                ORDER BY rr.id DESC 
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

    public function getByStudentId($studentId) {
        $sql = "SELECT rr.*, 
                       curr_r.room_number as current_room_number, curr_r.building as current_building,
                       req_r.room_number as requested_room_number, req_r.building as requested_building
                FROM room_requests rr
                LEFT JOIN rooms curr_r ON rr.current_room_id = curr_r.id
                JOIN rooms req_r ON rr.requested_room_id = req_r.id
                WHERE rr.student_id = :student_id
                ORDER BY rr.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    public function getLatestByStudentId($studentId) {
        $sql = "SELECT rr.*, 
                       curr_r.room_number as current_room_number,
                       req_r.room_number as requested_room_number, req_r.building as requested_building
                FROM room_requests rr
                LEFT JOIN rooms curr_r ON rr.current_room_id = curr_r.id
                JOIN rooms req_r ON rr.requested_room_id = req_r.id
                WHERE rr.student_id = :student_id
                ORDER BY rr.id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetch();
    }

    public function getById($id) {
        $sql = "SELECT rr.*, 
                       s.fullname as student_name, s.student_code,
                       req_r.room_number as requested_room_number, req_r.capacity, req_r.occupied, req_r.status as room_status
                FROM room_requests rr
                JOIN students s ON rr.student_id = s.id
                JOIN rooms req_r ON rr.requested_room_id = req_r.id
                WHERE rr.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO room_requests (student_id, current_room_id, requested_room_id, request_type, reason, status)
                    VALUES (:student_id, :current_room_id, :requested_room_id, :request_type, :reason, 'Pending')";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':student_id' => $data['student_id'],
                ':current_room_id' => !empty($data['current_room_id']) ? $data['current_room_id'] : null,
                ':requested_room_id' => $data['requested_room_id'],
                ':request_type' => $data['request_type'] ?? 'transfer',
                ':reason' => $data['reason']
            ]);
        } catch (PDOException $e) {
            error_log("RoomRequest create error: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE room_requests SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function getPendingCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM room_requests WHERE status = 'Pending'");
        return $stmt->fetch()['total'];
    }
}
