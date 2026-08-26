<?php

require_once APPROOT . '/core/Model.php';

class Student extends Model {
    public function getAll($keyword = '', $roomId = null, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $whereClause = [];

        if (!empty($keyword)) {
            $whereClause[] = "(s.student_code LIKE :kw1 OR s.fullname LIKE :kw2 OR s.email LIKE :kw3 OR s.phone LIKE :kw4)";
            $params[':kw1'] = "%$keyword%";
            $params[':kw2'] = "%$keyword%";
            $params[':kw3'] = "%$keyword%";
            $params[':kw4'] = "%$keyword%";
        }

        if (!empty($roomId)) {
            $whereClause[] = "s.room_id = :room_id";
            $params[':room_id'] = $roomId;
        }

        $where = !empty($whereClause) ? " WHERE " . implode(" AND ", $whereClause) : "";

        // Đếm tổng số bản ghi
        $countSql = "SELECT COUNT(*) as total FROM students s" . $where;
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $totalRecords = $stmtCount->fetch()['total'];

        // Truy vấn dữ liệu có JOIN với phòng ở
        $sql = "SELECT s.*, r.room_number, r.building 
                FROM students s 
                LEFT JOIN rooms r ON s.room_id = r.id 
                " . $where . " 
                ORDER BY s.id DESC 
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
        $sql = "SELECT s.*, r.room_number, r.building, r.room_type, r.price, r.capacity, r.occupied
                FROM students s 
                LEFT JOIN rooms r ON s.room_id = r.id 
                WHERE s.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        $sql = "SELECT s.*, r.room_number, r.building, r.room_type, r.price, r.capacity, r.occupied, r.floor
                FROM students s 
                LEFT JOIN rooms r ON s.room_id = r.id 
                WHERE s.user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch();
    }

    public function findByStudentCode($code, $excludeId = null) {
        $sql = "SELECT * FROM students WHERE student_code = :code";
        $params = [':code' => $code];
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function findByEmail($email, $excludeId = null) {
        $sql = "SELECT * FROM students WHERE email = :email";
        $params = [':email' => $email];
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
        $sql = "INSERT INTO students (user_id, student_code, fullname, gender, dob, phone, email, address, faculty, avatar, room_id) 
                VALUES (:user_id, :student_code, :fullname, :gender, :dob, :phone, :email, :address, :faculty, :avatar, :room_id)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
            ':student_code' => $data['student_code'],
            ':fullname' => $data['fullname'],
            ':gender' => $data['gender'],
            ':dob' => $data['dob'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
            ':address' => $data['address'],
            ':faculty' => $data['faculty'],
            ':avatar' => $data['avatar'] ?? 'default.png',
            ':room_id' => !empty($data['room_id']) ? $data['room_id'] : null
        ]);

        return $result ? $this->db->lastInsertId() : false;
    }

    public function update($id, $data) {
        $sql = "UPDATE students SET 
                student_code = :student_code,
                fullname = :fullname,
                gender = :gender,
                dob = :dob,
                phone = :phone,
                email = :email,
                address = :address,
                faculty = :faculty,
                avatar = :avatar,
                room_id = :room_id 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':student_code' => $data['student_code'],
            ':fullname' => $data['fullname'],
            ':gender' => $data['gender'],
            ':dob' => $data['dob'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
            ':address' => $data['address'],
            ':faculty' => $data['faculty'],
            ':avatar' => $data['avatar'],
            ':room_id' => !empty($data['room_id']) ? $data['room_id'] : null,
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM students WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function updateRoomId($studentId, $newRoomId) {
        $sql = "UPDATE students SET room_id = :room_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':room_id' => !empty($newRoomId) ? $newRoomId : null,
            ':id' => $studentId
        ]);
    }

    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM students");
        return $stmt->fetch()['total'];
    }
}
