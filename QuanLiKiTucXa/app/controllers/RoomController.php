<?php

class RoomController extends Controller {
    private $roomModel;
    private $studentModel;

    public function __construct() {
        $this->roomModel = $this->model('Room');
        $this->studentModel = $this->model('Student');
    }

    public function index() {
        $keyword = Validator::sanitize($_GET['search'] ?? '');
        $building = Validator::sanitize($_GET['building'] ?? '');
        $status = Validator::sanitize($_GET['status'] ?? '');
        $roomType = Validator::sanitize($_GET['room_type'] ?? '');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $roomsResult = $this->roomModel->getAll($keyword, $page, 6, $building, $status, $roomType);
        $buildings = $this->roomModel->getAllBuildings();

        $data = [
            'title' => 'Danh sách & Quản lý Phòng Kí túc xá',
            'rooms' => $roomsResult['data'],
            'total' => $roomsResult['total'],
            'page' => $roomsResult['page'],
            'totalPages' => $roomsResult['totalPages'],
            'keyword' => $keyword,
            'building' => $building,
            'status' => $status,
            'roomType' => $roomType,
            'buildings' => $buildings
        ];

        $this->view('rooms/index', $data);
    }

    /**
     * ĐIỂM SÁNG TẠO 2: ROOM STATUS VISUALIZATION (BẢN ĐỒ PHÒNG KTX)
     * Đường dẫn: /room/map
     */
    public function map() {
        $groupedRooms = $this->roomModel->getRoomsGroupedByBuilding();

        $data = [
            'title' => 'Bản đồ Trạng thái Phòng Kí túc xá (Room Status Map)',
            'groupedRooms' => $groupedRooms
        ];

        $this->view('rooms/map', $data);
    }

    public function detail($id = null) {
        if (!$id) {
            $this->redirect('room/index');
        }

        $room = $this->roomModel->getById($id);
        if (!$room) {
            $this->redirect('room/index');
        }

        $students = $this->roomModel->getStudentsInRoom($id);

        if (isset($_GET['json']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            $this->jsonResponse([
                'status' => 'success',
                'room' => $room,
                'students' => $students
            ]);
            return;
        }

        $this->view('rooms/detail', [
            'title' => 'Chi tiết phòng ' . $room['room_number'],
            'room' => $room,
            'students' => $students
        ]);
    }

    public function create() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);

            // Validation server-side nghiêm ngặt
            $capacity = (int)($data['capacity'] ?? 0);
            $price = (float)($data['price'] ?? -1);

            if (empty($data['room_number']) || empty($data['building']) || $capacity <= 0 || $price < 0) {
                Session::setFlash('error', 'Thông tin phòng không hợp lệ! Sức chứa phải > 0 và giá phòng >= 0.');
                $this->view('rooms/create', ['formData' => $data]);
                return;
            }

            if ($this->roomModel->findByRoomNumber($data['room_number'])) {
                Session::setFlash('error', 'Số phòng "' . $data['room_number'] . '" đã tồn tại trong hệ thống! Vui lòng chọn số phòng khác.');
                $this->view('rooms/create', ['formData' => $data]);
                return;
            }

            if ($this->roomModel->create($data)) {
                Session::setFlash('success', 'Thêm phòng mới thành công!');
                $this->redirect('room/index');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra. Không thể thêm phòng!');
                $this->view('rooms/create', ['formData' => $data]);
            }
        } else {
            $this->view('rooms/create');
        }
    }

    public function edit($id = null) {
        $this->requireAdmin();

        if (!$id) $this->redirect('room/index');
        $room = $this->roomModel->getById($id);
        if (!$room) $this->redirect('room/index');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);
            $capacity = (int)($data['capacity'] ?? 0);
            $price = (float)($data['price'] ?? -1);

            if (empty($data['room_number']) || empty($data['building']) || $capacity <= 0 || $price < 0) {
                Session::setFlash('error', 'Thông tin phòng không hợp lệ! Sức chứa phải > 0 và giá phòng >= 0.');
                $this->view('rooms/edit', ['room' => array_merge($room, $data)]);
                return;
            }

            // Kiểm tra số lượng người đang ở hiện tại không vượt quá sức chứa mới
            if ($room['occupied'] > $capacity) {
                Session::setFlash('error', 'Không thể giảm sức chứa xuống ' . $capacity . ' vì phòng đang có ' . $room['occupied'] . ' sinh viên!');
                $this->view('rooms/edit', ['room' => array_merge($room, $data)]);
                return;
            }

            if ($this->roomModel->findByRoomNumber($data['room_number'], $id)) {
                Session::setFlash('error', 'Số phòng "' . $data['room_number'] . '" đã bị trùng với phòng khác!');
                $this->view('rooms/edit', ['room' => array_merge($room, $data)]);
                return;
            }

            if ($this->roomModel->update($id, $data)) {
                // Đồng bộ lại status theo số người ở và Maintenance
                $this->roomModel->updateOccupiedCount($id);
                Session::setFlash('success', 'Cập nhật thông tin phòng thành công!');
                $this->redirect('room/index');
            } else {
                Session::setFlash('error', 'Cập nhật thất bại. Vui lòng thử lại!');
                $this->view('rooms/edit', ['room' => array_merge($room, $data)]);
            }
        } else {
            $this->view('rooms/edit', ['room' => $room]);
        }
    }

    public function delete($id = null) {
        $this->requireAdmin();

        if ($id) {
            if ($this->roomModel->delete($id)) {
                Session::setFlash('success', 'Xóa phòng kí túc xá thành công!');
            } else {
                Session::setFlash('error', 'Không thể xóa phòng đang có sinh viên cư trú!');
            }
        }
        $this->redirect('room/index');
    }

    /**
     * Chức năng CHUYỂN PHÒNG trực tiếp cho Quản trị viên
     */
    public function transfer() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = (int)($_POST['student_id'] ?? 0);
            $newRoomId = (int)($_POST['new_room_id'] ?? 0);

            if (!$studentId || !$newRoomId) {
                Session::setFlash('error', 'Vui lòng chọn đầy đủ sinh viên và phòng mới cần chuyển!');
                $this->redirect('room/index');
                return;
            }

            $student = $this->studentModel->getById($studentId);
            $newRoom = $this->roomModel->getById($newRoomId);

            if (!$student || !$newRoom) {
                Session::setFlash('error', 'Dữ liệu không hợp lệ!');
                $this->redirect('room/index');
                return;
            }

            // Kiểm tra phòng mới không phải Maintenance và không Full
            if ($newRoom['status'] === 'Maintenance' || $newRoom['occupied'] >= $newRoom['capacity']) {
                Session::setFlash('error', 'Phòng ' . $newRoom['room_number'] . ' đang bảo trì hoặc đã hết chỗ!');
                $this->redirect('room/index');
                return;
            }

            $oldRoomId = $student['room_id'];

            if ($this->studentModel->updateRoomId($studentId, $newRoomId)) {
                if ($oldRoomId) {
                    $this->roomModel->updateOccupiedCount($oldRoomId);
                }
                $this->roomModel->updateOccupiedCount($newRoomId);

                Session::setFlash('success', 'Chuyển sinh viên ' . $student['fullname'] . ' sang phòng ' . $newRoom['room_number'] . ' thành công!');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra trong quá trình chuyển phòng.');
            }
        }
        $this->redirect('room/index');
    }

    /**
     * ĐIỂM SÁNG TẠO 1: SMART ROOM RECOMMENDATION (GỢI Ý PHÒNG THÔNG MINH)
     * Tính điểm scoring 100 điểm
     */
    public function smartMatch() {
        $maxPrice = isset($_REQUEST['price']) ? (float)$_REQUEST['price'] : 1000000;
        $gender = isset($_REQUEST['gender']) ? Validator::sanitize($_REQUEST['gender']) : 'Nam';
        $building = isset($_REQUEST['building']) ? Validator::sanitize($_REQUEST['building']) : '';
        $desiredCapacity = isset($_REQUEST['capacity']) ? (int)$_REQUEST['capacity'] : 0;
        $roomType = isset($_REQUEST['room_type']) ? Validator::sanitize($_REQUEST['room_type']) : '';

        $matchedRooms = $this->roomModel->smartMatch($maxPrice, $gender, $building, $desiredCapacity, $roomType);
        $buildings = $this->roomModel->getAllBuildings();

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->jsonResponse([
                'status' => 'success',
                'data' => $matchedRooms
            ]);
            return;
        }

        $this->view('rooms/smart_match', [
            'title' => 'Gợi ý Phòng Thông Minh (Smart Room Recommendation)',
            'matchedRooms' => $matchedRooms,
            'price' => $maxPrice,
            'gender' => $gender,
            'building' => $building,
            'desiredCapacity' => $desiredCapacity,
            'roomType' => $roomType,
            'buildings' => $buildings
        ]);
    }
}
