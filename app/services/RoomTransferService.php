<?php

require_once APPROOT . '/core/Database.php';

class RoomTransferService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Phê duyệt yêu cầu chuyển phòng/đăng ký phòng an toàn bằng Database Transaction.
     * Quy tắc:
     * - Kiểm tra request tồn tại và đang ở trạng thái 'Pending' (chống approve trùng lặp).
     * - Kiểm tra sinh viên tồn tại.
     * - Kiểm tra phòng mới: tồn tại, không ở trạng thái 'Maintenance', chưa 'Full' và số sinh viên thực tế < capacity.
     * - Chuyển sinh viên sang phòng mới (cập nhật students.room_id).
     * - Cập nhật hợp đồng Active của sinh viên sang phòng mới (nếu có).
     * - Đồng bộ chính xác số người ở (occupied) và trạng thái (status) cho cả phòng cũ và phòng mới.
     * - Cập nhật trạng thái request thành 'Approved'.
     * - Nếu có bất kỳ lỗi nào -> Rollback toàn bộ.
     */
    public function approveTransfer($requestId) {
        try {
            $this->db->beginTransaction();

            // 1. Kiểm tra và lock request
            $stmt = $this->db->prepare("SELECT * FROM room_requests WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $requestId]);
            $request = $stmt->fetch();

            if (!$request) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Yêu cầu chuyển phòng không tồn tại!'
                ];
            }

            // 2. Chống duyệt 2 lần (chỉ xử lý khi Pending)
            if ($request['status'] !== 'Pending') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Yêu cầu này đã được xử lý trước đó (trạng thái: ' . $request['status'] . ')!'
                ];
            }

            $studentId = (int)$request['student_id'];
            $oldRoomId = !empty($request['current_room_id']) ? (int)$request['current_room_id'] : null;
            $newRoomId = (int)$request['requested_room_id'];

            // 3. Kiểm tra sinh viên
            $stmtStudent = $this->db->prepare("SELECT * FROM students WHERE id = :id FOR UPDATE");
            $stmtStudent->execute([':id' => $studentId]);
            $student = $stmtStudent->fetch();

            if (!$student) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Hồ sơ sinh viên không tồn tại trong hệ thống!'
                ];
            }

            // Cập nhật lại phòng cũ thực tế từ hồ sơ sinh viên nếu cần
            if ($student['room_id']) {
                $oldRoomId = (int)$student['room_id'];
            }

            // 4. Kiểm tra phòng mới
            $stmtRoom = $this->db->prepare("SELECT * FROM rooms WHERE id = :id FOR UPDATE");
            $stmtRoom->execute([':id' => $newRoomId]);
            $newRoom = $stmtRoom->fetch();

            if (!$newRoom) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Phòng mới được chọn không tồn tại!'
                ];
            }

            if ($newRoom['status'] === 'Maintenance') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Không thể chuyển! Phòng ' . $newRoom['room_number'] . ' đang ở trạng thái Bảo trì.'
                ];
            }

            // Đếm số lượng sinh viên thực tế đang ở trong phòng mới
            $stmtCountNew = $this->db->prepare("SELECT COUNT(*) as c FROM students WHERE room_id = :room_id");
            $stmtCountNew->execute([':room_id' => $newRoomId]);
            $currentCountNew = (int)$stmtCountNew->fetch()['c'];

            if ($currentCountNew >= (int)$newRoom['capacity']) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Không thể chuyển! Phòng ' . $newRoom['room_number'] . ' đã đạt sức chứa tối đa (' . $currentCountNew . '/' . $newRoom['capacity'] . ').'
                ];
            }

            // 5. Cập nhật phòng cho sinh viên
            $stmtUpdateStudent = $this->db->prepare("UPDATE students SET room_id = :room_id WHERE id = :id");
            $stmtUpdateStudent->execute([
                ':room_id' => $newRoomId,
                ':id' => $studentId
            ]);

            // 6. Cập nhật hợp đồng Active của sinh viên sang phòng mới
            $stmtUpdateContract = $this->db->prepare("UPDATE contracts SET room_id = :room_id WHERE student_id = :student_id AND status = 'Active'");
            $stmtUpdateContract->execute([
                ':room_id' => $newRoomId,
                ':student_id' => $studentId
            ]);

            // 7. Đồng bộ occupied và status cho phòng cũ (nếu có)
            if ($oldRoomId && $oldRoomId !== $newRoomId) {
                $this->syncRoomOccupancy($oldRoomId);
            }

            // 8. Đồng bộ occupied và status cho phòng mới
            $this->syncRoomOccupancy($newRoomId);

            // 9. Cập nhật trạng thái yêu cầu sang Approved
            $stmtUpdateReq = $this->db->prepare("UPDATE room_requests SET status = 'Approved' WHERE id = :id");
            $stmtUpdateReq->execute([':id' => $requestId]);

            // Hoàn tất Transaction
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Duyệt yêu cầu chuyển phòng thành công cho sinh viên ' . $student['fullname'] . ' sang phòng ' . $newRoom['room_number'] . '!'
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("RoomTransferService Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống trong quá trình chuyển phòng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cơ chế đồng bộ số lượng người ở (occupied) duy nhất và chuẩn xác theo bảng students.
     */
    public function syncRoomOccupancy($roomId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM students WHERE room_id = :room_id");
        $stmt->execute([':room_id' => $roomId]);
        $count = (int)$stmt->fetch()['count'];

        $stmtRoom = $this->db->prepare("SELECT * FROM rooms WHERE id = :id");
        $stmtRoom->execute([':id' => $roomId]);
        $room = $stmtRoom->fetch();

        if ($room) {
            if ($room['status'] === 'Maintenance') {
                $upStmt = $this->db->prepare("UPDATE rooms SET occupied = :count WHERE id = :id");
                $upStmt->execute([':count' => $count, ':id' => $roomId]);
            } else {
                $status = ($count >= (int)$room['capacity']) ? 'Full' : 'Available';
                $upStmt = $this->db->prepare("UPDATE rooms SET occupied = :count, status = :status WHERE id = :id");
                $upStmt->execute([':count' => $count, ':status' => $status, ':id' => $roomId]);
            }
        }
    }
}
