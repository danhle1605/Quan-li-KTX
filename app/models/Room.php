<?php

require_once APPROOT . '/core/Model.php';

class Room extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureTableColumns();
    }

    private function ensureTableColumns() {
        try {
            // Kiểm tra và thêm cột floor nếu chưa có
            $checkFloor = $this->db->query("SHOW COLUMNS FROM rooms LIKE 'floor'");
            if ($checkFloor && $checkFloor->rowCount() == 0) {
                $this->db->exec("ALTER TABLE rooms ADD COLUMN floor INT DEFAULT 1 AFTER building");
            }

            // Kiểm tra và thêm cột room_type nếu chưa có
            $checkType = $this->db->query("SHOW COLUMNS FROM rooms LIKE 'room_type'");
            if ($checkType && $checkType->rowCount() == 0) {
                $this->db->exec("ALTER TABLE rooms ADD COLUMN room_type VARCHAR(50) DEFAULT 'Thường' AFTER floor");
            }
        } catch (PDOException $e) {
            error_log("Ensure room columns error: " . $e->getMessage());
        }
    }

    public function getAll($keyword = '', $page = 1, $limit = 6, $building = '', $status = '', $roomType = '') {
        $offset = ($page - 1) * $limit;
        $params = [];
        $whereClause = [];

        if (!empty($keyword)) {
            $whereClause[] = "(room_number LIKE :kw1 OR building LIKE :kw2 OR description LIKE :kw3)";
            $params[':kw1'] = "%$keyword%";
            $params[':kw2'] = "%$keyword%";
            $params[':kw3'] = "%$keyword%";
        }

        if (!empty($building)) {
            $whereClause[] = "building = :building";
            $params[':building'] = $building;
        }

        if (!empty($status)) {
            $whereClause[] = "status = :status";
            $params[':status'] = $status;
        }

        if (!empty($roomType)) {
            $whereClause[] = "room_type = :room_type";
            $params[':room_type'] = $roomType;
        }

        $where = !empty($whereClause) ? " WHERE " . implode(" AND ", $whereClause) : "";

        // Đếm tổng bản ghi
        $countSql = "SELECT COUNT(*) as total FROM rooms" . $where;
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $totalRecords = $stmtCount->fetch()['total'];

        // Lấy danh sách trang hiện tại
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id) as occupied,
                (r.capacity - (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id)) as remaining 
                FROM rooms r" . $where . " ORDER BY r.id ASC LIMIT :limit OFFSET :offset";
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
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id) as occupied,
                (r.capacity - (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id)) as remaining 
                FROM rooms r WHERE r.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getStudentsInRoom($roomId) {
        $sql = "SELECT * FROM students WHERE room_id = :room_id ORDER BY fullname ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);
        return $stmt->fetchAll();
    }

    public function findByRoomNumber($roomNumber, $excludeId = null) {
        $sql = "SELECT * FROM rooms WHERE room_number = :room_number";
        $params = [':room_number' => $roomNumber];
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO rooms (room_number, building, floor, room_type, capacity, occupied, price, status, description) 
                    VALUES (:room_number, :building, :floor, :room_type, :capacity, :occupied, :price, :status, :description)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':room_number' => $data['room_number'],
                ':building' => $data['building'],
                ':floor' => $data['floor'] ?? 1,
                ':room_type' => $data['room_type'] ?? 'Thường',
                ':capacity' => $data['capacity'],
                ':occupied' => $data['occupied'] ?? 0,
                ':price' => $data['price'],
                ':status' => $data['status'] ?? 'Available',
                ':description' => $data['description'] ?? ''
            ]);
        } catch (PDOException $e) {
            error_log("Room create error: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $sql = "UPDATE rooms SET 
                    room_number = :room_number,
                    building = :building,
                    floor = :floor,
                    room_type = :room_type,
                    capacity = :capacity,
                    price = :price,
                    status = :status,
                    description = :description 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':room_number' => $data['room_number'],
                ':building' => $data['building'],
                ':floor' => $data['floor'] ?? 1,
                ':room_type' => $data['room_type'] ?? 'Thường',
                ':capacity' => $data['capacity'],
                ':price' => $data['price'],
                ':status' => $data['status'],
                ':description' => $data['description'],
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Room update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        // Kiểm tra xem phòng có sinh viên không
        $studentsCount = (int)$this->db->query("SELECT COUNT(*) as c FROM students WHERE room_id = " . (int)$id)->fetch()['c'];
        if ($studentsCount > 0) {
            return false;
        }

        $sql = "DELETE FROM rooms WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Sửa logic updateOccupiedCount() theo đúng yêu cầu đề bài:
     * Quy tắc:
     * - Nếu phòng đang Maintenance thì KHÔNG được tự động đổi sang Available hoặc Full.
     * - Nếu phòng không phải Maintenance:
     *   + occupied >= capacity -> Full
     *   + occupied < capacity -> Available
     */
    public function updateOccupiedCount($roomId) {
        $sql = "SELECT COUNT(*) as count FROM students WHERE room_id = :room_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);
        $count = (int)$stmt->fetch()['count'];

        $room = $this->getById($roomId);
        if ($room) {
            // Nếu phòng đang Maintenance, giữ nguyên Maintenance, chỉ cập nhật occupied
            if ($room['status'] === 'Maintenance') {
                $updateSql = "UPDATE rooms SET occupied = :count WHERE id = :id";
                $upStmt = $this->db->prepare($updateSql);
                $upStmt->execute([':count' => $count, ':id' => $roomId]);
            } else {
                $status = ($count >= $room['capacity']) ? 'Full' : 'Available';
                $updateSql = "UPDATE rooms SET occupied = :count, status = :status WHERE id = :id";
                $upStmt = $this->db->prepare($updateSql);
                $upStmt->execute([':count' => $count, ':status' => $status, ':id' => $roomId]);
            }
        }
    }

    public function getAvailableRooms() {
        $sql = "SELECT *, (capacity - occupied) as remaining FROM rooms WHERE occupied < capacity AND status != 'Maintenance' ORDER BY room_number ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAllBuildings() {
        $sql = "SELECT DISTINCT building FROM rooms ORDER BY building ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Lấy toàn bộ danh sách phòng phân nhóm theo Tòa (Dùng cho Sơ đồ phòng - Room Map)
     */
    public function getRoomsGroupedByBuilding() {
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id) as occupied
                FROM rooms r ORDER BY r.building ASC, r.floor ASC, r.room_number ASC";
        $stmt = $this->db->query($sql);
        $rooms = $stmt->fetchAll();

        $grouped = [];
        foreach ($rooms as $r) {
            $building = $r['building'];
            if (!isset($grouped[$building])) {
                $grouped[$building] = [];
            }
            $grouped[$building][] = $r;
        }
        return $grouped;
    }

    /**
     * Thuật toán Gợi ý phòng thông minh (SMART ROOM RECOMMENDATION) - Điểm sáng tạo 1
     * Công thức tính 100 điểm:
     * - +30 điểm: Phòng còn chỗ trống (occupied < capacity)
     * - +25 điểm: Phù hợp giới tính (Khu vực / Tòa dành cho Nam / Nữ)
     * - +20 điểm: Giá phòng <= ngân sách tối đa
     * - +15 điểm: Đúng tòa nhà mong muốn
     * - +10 điểm: Đúng số người / sức chứa mong muốn
     */
    public function smartMatch($maxPrice = 1000000, $gender = 'Nam', $preferredBuilding = '', $desiredCapacity = 0, $roomType = '') {
        // Chỉ lấy các phòng hợp lệ: Không Maintenance, Chưa Full, occupied < capacity
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id) as occupied,
                (r.capacity - (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id)) as remaining 
                FROM rooms r 
                WHERE r.status != 'Maintenance' AND r.occupied < r.capacity";
        
        $stmt = $this->db->query($sql);
        $eligibleRooms = $stmt->fetchAll();
        $matchedRooms = [];

        foreach ($eligibleRooms as $room) {
            $score = 0;
            $reasons = [];

            // 1. Phòng còn chỗ trống (+30 điểm)
            $remaining = $room['capacity'] - $room['occupied'];
            if ($remaining > 0) {
                $score += 30;
                $reasons[] = "+30 điểm: Phòng còn chỗ trống (" . $remaining . " giường sẵn sàng)";
            }

            // 2. Phù hợp giới tính (+25 điểm)
            $buildingLower = mb_strtolower($room['building'], 'UTF-8');
            if ($gender === 'Nữ') {
                if (strpos($buildingLower, 'nữ') !== false || strpos($buildingLower, 'c') !== false) {
                    $score += 25;
                    $reasons[] = "+25 điểm: Phù hợp giới tính Nữ (" . $room['building'] . ")";
                } else {
                    $reasons[] = "+0 điểm: Khu vực chủ yếu cho sinh viên Nam";
                }
            } else { // Nam
                if (strpos($buildingLower, 'nữ') === false) {
                    $score += 25;
                    $reasons[] = "+25 điểm: Phù hợp giới tính Nam (" . $room['building'] . ")";
                } else {
                    $reasons[] = "+0 điểm: Khu vực dành riêng cho nữ";
                }
            }

            // 3. Giá <= Ngân sách (+20 điểm)
            if ($maxPrice <= 0 || $room['price'] <= $maxPrice) {
                $score += 20;
                $reasons[] = "+20 điểm: Mức giá " . number_format($room['price'], 0, ',', '.') . "đ/tháng <= ngân sách (" . number_format($maxPrice, 0, ',', '.') . "đ)";
            } else {
                $reasons[] = "+0 điểm: Mức giá vượt quá ngân sách mong muốn";
            }

            // 4. Đúng tòa nhà mong muốn (+15 điểm)
            if (!empty($preferredBuilding)) {
                if (strpos(mb_strtolower($room['building']), mb_strtolower($preferredBuilding)) !== false) {
                    $score += 15;
                    $reasons[] = "+15 điểm: Khớp tòa nhà mong muốn (" . $room['building'] . ")";
                } else {
                    $reasons[] = "+0 điểm: Không trùng tòa nhà chọn trước";
                }
            } else {
                $score += 15;
                $reasons[] = "+15 điểm: Phù hợp vị trí tổng thể KTX";
            }

            // 5. Số người / sức chứa / loại phòng mong muốn (+10 điểm)
            if ($desiredCapacity > 0) {
                if ((int)$room['capacity'] == (int)$desiredCapacity) {
                    $score += 10;
                    $reasons[] = "+10 điểm: Đúng sức chứa phòng mong muốn (" . $desiredCapacity . " người/phòng)";
                } else {
                    $reasons[] = "+5 điểm: Sức chứa phòng " . $room['capacity'] . " người tương đối phù hợp";
                    $score += 5;
                }
            } else if (!empty($roomType) && strtolower($room['room_type']) === strtolower($roomType)) {
                $score += 10;
                $reasons[] = "+10 điểm: Đúng loại phòng yêu cầu (" . $room['room_type'] . ")";
            } else {
                $score += 10;
                $reasons[] = "+10 điểm: Tiện nghi phòng hoàn chỉnh (" . $room['room_type'] . ")";
            }

            $room['match_score'] = min(100, $score);
            $room['match_reasons'] = $reasons;
            $matchedRooms[] = $room;
        }

        // Sắp xếp các phòng theo điểm khớp giảm dần
        usort($matchedRooms, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        return $matchedRooms;
    }

    public function getNearlyFullRooms() {
        $sql = "SELECT *, (capacity - occupied) as remaining 
                FROM rooms 
                WHERE (capacity - occupied) = 1 AND status = 'Available'
                ORDER BY room_number ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getFullRoomsCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM rooms WHERE status = 'Full' OR occupied >= capacity");
        return $stmt->fetch()['total'];
    }

    public function getStudentsWithoutRoom() {
        $sql = "SELECT COUNT(*) as total FROM students WHERE room_id IS NULL";
        $stmt = $this->db->query($sql);
        return $stmt->fetch()['total'];
    }

    public function getRoomStatsByBuilding() {
        $sql = "SELECT building, 
                       COUNT(*) as total_rooms,
                       SUM(capacity) as total_capacity,
                       SUM(occupied) as total_occupied
                FROM rooms 
                GROUP BY building 
                ORDER BY building ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
