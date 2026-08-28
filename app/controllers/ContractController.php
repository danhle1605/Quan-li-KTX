<?php

class ContractController extends Controller {
    private $contractModel;
    private $studentModel;
    private $roomModel;

    public function __construct() {
        $this->contractModel = $this->model('Contract');
        $this->studentModel = $this->model('Student');
        $this->roomModel = $this->model('Room');
    }

    public function index() {
        $this->requireLogin();

        $keyword = Validator::sanitize($_GET['search'] ?? '');
        $status = Validator::sanitize($_GET['status'] ?? '');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        if ($this->isAdmin()) {
            $result = $this->contractModel->getAll($keyword, $status, $page, 6);
            $expiringContracts = $this->contractModel->getExpiringContracts(7);
            $expiredCount = $this->contractModel->getExpiredContractsCount();
            $activeCount = $this->contractModel->getActiveContractsCount();

            $data = [
                'title' => 'Quản lý Hợp đồng Ở Kí túc xá UTH',
                'userRole' => 'admin',
                'contracts' => $result['data'],
                'total' => $result['total'],
                'page' => $result['page'],
                'totalPages' => $result['totalPages'],
                'keyword' => $keyword,
                'status' => $status,
                'expiringContracts' => $expiringContracts,
                'activeCount' => $activeCount,
                'expiredCount' => $expiredCount
            ];

            $this->view('contracts/index', $data);
        } else { // Student
            $student = $this->getStudentInfo();
            $studentId = $student ? $student['id'] : 0;
            $result = $this->contractModel->getAll($keyword, $status, $page, 10, $studentId);

            $data = [
                'title' => 'Hợp đồng Ở KTX của Tôi',
                'userRole' => 'student',
                'contracts' => $result['data'],
                'total' => $result['total'],
                'page' => $result['page'],
                'totalPages' => $result['totalPages'],
                'keyword' => $keyword,
                'status' => $status
            ];

            $this->view('contracts/index', $data);
        }
    }

    public function detail($id = null) {
        $this->requireLogin();

        if (!$id) {
            $this->redirect('contract/index');
        }

        $contract = $this->contractModel->getById($id);
        if (!$contract) {
            Session::setFlash('error', 'Hợp đồng không tồn tại!');
            $this->redirect('contract/index');
            return;
        }

        // Nếu là sinh viên, chỉ được xem hợp đồng của chính mình
        if ($this->isStudent()) {
            $student = $this->getStudentInfo();
            if (!$student || $contract['student_id'] != $student['id']) {
                Session::setFlash('error', 'Bạn không có quyền xem hợp đồng của người khác!');
                $this->redirect('contract/index');
                return;
            }
        }

        $data = [
            'title' => 'Chi tiết Hợp đồng KTX #' . $contract['id'],
            'contract' => $contract
        ];

        $this->view('contracts/detail', $data);
    }

    public function create() {
        $this->requireAdmin();

        $students = $this->studentModel->getAll('', null, 1, 300)['data'];
        $rooms = $this->roomModel->getAvailableRooms();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);

            if (empty($data['student_id']) || empty($data['room_id']) || empty($data['start_date']) || empty($data['end_date'])) {
                Session::setFlash('error', 'Vui lòng nhập đầy đủ các trường thông tin hợp đồng bắt buộc!');
                $this->view('contracts/create', ['students' => $students, 'rooms' => $rooms, 'formData' => $data]);
                return;
            }

            if ($data['start_date'] > $data['end_date']) {
                Session::setFlash('error', 'Ngày bắt đầu hợp đồng không thể sau ngày kết thúc!');
                $this->view('contracts/create', ['students' => $students, 'rooms' => $rooms, 'formData' => $data]);
                return;
            }

            // Kiểm tra phòng chọn có khả dụng không
            $room = $this->roomModel->getById($data['room_id']);
            if (!$room || $room['status'] === 'Maintenance' || $room['occupied'] >= $room['capacity']) {
                Session::setFlash('error', 'Phòng chọn đang bảo trì hoặc đã hết chỗ!');
                $this->view('contracts/create', ['students' => $students, 'rooms' => $rooms, 'formData' => $data]);
                return;
            }

            if ($this->contractModel->create($data)) {
                // Tự động cập nhật phòng của sinh viên
                $this->studentModel->updateRoomId($data['student_id'], $data['room_id']);
                $this->roomModel->updateOccupiedCount($data['room_id']);

                Session::setFlash('success', 'Tạo hợp đồng ở kí túc xá mới thành công!');
                $this->redirect('contract/index');
            } else {
                Session::setFlash('error', 'Không thể tạo hợp đồng. Vui lòng kiểm tra lại!');
                $this->view('contracts/create', ['students' => $students, 'rooms' => $rooms, 'formData' => $data]);
            }
        } else {
            $this->view('contracts/create', ['students' => $students, 'rooms' => $rooms]);
        }
    }

    public function edit($id = null) {
        $this->requireAdmin();

        if (!$id) $this->redirect('contract/index');
        $contract = $this->contractModel->getById($id);
        if (!$contract) $this->redirect('contract/index');

        $students = $this->studentModel->getAll('', null, 1, 300)['data'];
        $rooms = $this->roomModel->getAll('', 1, 200)['data'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);

            if (empty($data['student_id']) || empty($data['room_id']) || empty($data['start_date']) || empty($data['end_date'])) {
                Session::setFlash('error', 'Vui lòng điền đầy đủ các trường bắt buộc!');
                $this->view('contracts/edit', ['contract' => array_merge($contract, $data), 'students' => $students, 'rooms' => $rooms]);
                return;
            }

            $oldRoomId = $contract['room_id'];

            if ($this->contractModel->update($id, $data)) {
                if ($oldRoomId != $data['room_id']) {
                    $this->studentModel->updateRoomId($data['student_id'], $data['room_id']);
                    $this->roomModel->updateOccupiedCount($oldRoomId);
                    $this->roomModel->updateOccupiedCount($data['room_id']);
                }
                Session::setFlash('success', 'Cập nhật hợp đồng thành công!');
                $this->redirect('contract/index');
            } else {
                Session::setFlash('error', 'Cập nhật hợp đồng thất bại!');
                $this->view('contracts/edit', ['contract' => array_merge($contract, $data), 'students' => $students, 'rooms' => $rooms]);
            }
        } else {
            $this->view('contracts/edit', ['contract' => $contract, 'students' => $students, 'rooms' => $rooms]);
        }
    }

    public function renew($id = null) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $newEndDate = Validator::sanitize($_POST['end_date'] ?? '');
            if (!empty($newEndDate)) {
                if ($this->contractModel->renew($id, $newEndDate)) {
                    Session::setFlash('success', 'Gia hạn hợp đồng thành công đến ngày ' . $newEndDate . '!');
                } else {
                    Session::setFlash('error', 'Có lỗi xảy ra khi gia hạn hợp đồng.');
                }
            }
        }
        $this->redirect('contract/index');
    }

    public function cancel($id = null) {
        $this->requireAdmin();

        if ($id) {
            if ($this->contractModel->cancel($id)) {
                Session::setFlash('success', 'Đã hủy hợp đồng ở kí túc xá.');
            } else {
                Session::setFlash('error', 'Không thể hủy hợp đồng này.');
            }
        }
        $this->redirect('contract/index');
    }

    public function delete($id = null) {
        $this->requireAdmin();

        if ($id) {
            $contract = $this->contractModel->getById($id);
            if ($contract) {
                $roomId = $contract['room_id'];
                if ($this->contractModel->delete($id)) {
                    $this->roomModel->updateOccupiedCount($roomId);
                    Session::setFlash('success', 'Xóa hợp đồng thành công!');
                }
            }
        }
        $this->redirect('contract/index');
    }
}
