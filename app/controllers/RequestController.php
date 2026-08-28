<?php

class RequestController extends Controller {
    private $requestModel;
    private $studentModel;
    private $roomModel;

    public function __construct() {
        $this->requestModel = $this->model('RoomRequest');
        $this->studentModel = $this->model('Student');
        $this->roomModel = $this->model('Room');
    }

    public function index() {
        $this->requireLogin();

        $keyword = Validator::sanitize($_GET['search'] ?? '');
        $status = Validator::sanitize($_GET['status'] ?? '');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        if ($this->isAdmin()) {
            $result = $this->requestModel->getAll($keyword, $status, $page, 10);
            $pendingCount = $this->requestModel->getPendingCount();

            $data = [
                'title' => 'Quản lý Yêu cầu Chuyển & Đăng ký phòng KTX',
                'requests' => $result['data'],
                'total' => $result['total'],
                'page' => $result['page'],
                'totalPages' => $result['totalPages'],
                'keyword' => $keyword,
                'status' => $status,
                'pendingCount' => $pendingCount
            ];
            $this->view('requests/admin_index', $data);
        } else { // Student
            $student = $this->getStudentInfo();
            $myRequests = $student ? $this->requestModel->getByStudentId($student['id']) : [];

            $data = [
                'title' => 'Yêu cầu Chuyển phòng của tôi',
                'student' => $student,
                'requests' => $myRequests
            ];
            $this->view('requests/student_index', $data);
        }
    }

    public function create() {
        $this->requireLogin();
        $student = $this->getStudentInfo();

        if ($this->isStudent() && !$student) {
            Session::setFlash('error', 'Tài khoản của bạn chưa được liên kết với hồ sơ sinh viên trong hệ thống!');
            $this->redirect('dashboard/index');
            return;
        }

        // Lấy danh sách các phòng hợp lệ (Không Maintenance, Chưa Full, occupied < capacity)
        $availableRooms = $this->roomModel->getAvailableRooms();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);
            $requestedRoomId = (int)($data['requested_room_id'] ?? 0);
            $reason = trim($data['reason'] ?? '');

            if (!$requestedRoomId || empty($reason)) {
                Session::setFlash('error', 'Vui lòng chọn phòng mong muốn và nhập lý do chuyển phòng!');
                $this->view('requests/create', [
                    'title' => 'Gửi yêu cầu chuyển phòng',
                    'student' => $student,
                    'availableRooms' => $availableRooms
                ]);
                return;
            }

            $requestedRoom = $this->roomModel->getById($requestedRoomId);
            if (!$requestedRoom) {
                Session::setFlash('error', 'Phòng được chọn không tồn tại!');
                $this->redirect('request/create');
                return;
            }

            // Kiểm tra ràng buộc không cho đăng ký vào phòng Full hoặc Maintenance
            if ($requestedRoom['status'] === 'Maintenance' || $requestedRoom['status'] === 'Full' || $requestedRoom['occupied'] >= $requestedRoom['capacity']) {
                Session::setFlash('error', 'Không thể gửi yêu cầu đến phòng ' . $requestedRoom['room_number'] . ' vì phòng đang Bảo trì hoặc đã Đầy chỗ!');
                $this->redirect('request/create');
                return;
            }

            // Nếu sinh viên chuyển đến đúng phòng hiện tại
            if ($student && $student['room_id'] == $requestedRoomId) {
                Session::setFlash('error', 'Bạn đang ở phòng này rồi! Vui lòng chọn phòng khác.');
                $this->redirect('request/create');
                return;
            }

            $requestData = [
                'student_id' => $student['id'],
                'current_room_id' => $student['room_id'] ?? null,
                'requested_room_id' => $requestedRoomId,
                'request_type' => !empty($student['room_id']) ? 'transfer' : 'registration',
                'reason' => $reason
            ];

            if ($this->requestModel->create($requestData)) {
                Session::setFlash('success', 'Gửi yêu cầu chuyển/đăng ký phòng thành công! Quản trị viên sẽ sớm xem xét.');
                $this->redirect('request/index');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra. Không thể gửi yêu cầu!');
                $this->redirect('request/create');
            }
        } else {
            $this->view('requests/create', [
                'title' => 'Gửi yêu cầu chuyển phòng',
                'student' => $student,
                'availableRooms' => $availableRooms
            ]);
        }
    }

    public function approve($id = null) {
        $this->requireAdmin();
        if (!$id) $this->redirect('request/index');

        $req = $this->requestModel->getById($id);
        if (!$req) {
            Session::setFlash('error', 'Yêu cầu không tồn tại!');
            $this->redirect('request/index');
            return;
        }

        if ($req['status'] !== 'Pending') {
            Session::setFlash('error', 'Yêu cầu này đã được xử lý trước đó!');
            $this->redirect('request/index');
            return;
        }

        // Kiểm tra phòng mới có đủ chỗ không
        $requestedRoom = $this->roomModel->getById($req['requested_room_id']);
        if (!$requestedRoom || $requestedRoom['status'] === 'Maintenance' || $requestedRoom['occupied'] >= $requestedRoom['capacity']) {
            Session::setFlash('error', 'Không thể duyệt! Phòng ' . ($requestedRoom['room_number'] ?? '') . ' đã đầy hoặc đang bảo trì.');
            $this->redirect('request/index');
            return;
        }

        $oldRoomId = $req['current_room_id'];
        $newRoomId = $req['requested_room_id'];
        $studentId = $req['student_id'];

        // Cập nhật room_id cho sinh viên
        if ($this->studentModel->updateRoomId($studentId, $newRoomId)) {
            // Cập nhật lại số lượng occupied và status cho cả phòng cũ và phòng mới
            if ($oldRoomId) {
                $this->roomModel->updateOccupiedCount($oldRoomId);
            }
            $this->roomModel->updateOccupiedCount($newRoomId);

            // Đổi trạng thái yêu cầu sang Approved
            $this->requestModel->updateStatus($id, 'Approved');

            Session::setFlash('success', 'Đã duyệt yêu cầu chuyển phòng cho sinh viên ' . $req['student_name'] . ' sang phòng ' . $requestedRoom['room_number'] . '!');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra khi cập nhật phòng cho sinh viên.');
        }

        $this->redirect('request/index');
    }

    public function reject($id = null) {
        $this->requireAdmin();
        if (!$id) $this->redirect('request/index');

        $req = $this->requestModel->getById($id);
        if ($req && $req['status'] === 'Pending') {
            $this->requestModel->updateStatus($id, 'Rejected');
            Session::setFlash('success', 'Đã từ chối yêu cầu chuyển phòng của sinh viên ' . $req['student_name'] . '.');
        }
        $this->redirect('request/index');
    }
}
