<?php

class ApiController extends Controller {
    private $studentModel;
    private $roomModel;
    private $contractModel;
    private $paymentModel;
    private $requestModel;

    public function __construct() {
        $this->studentModel = $this->model('Student');
        $this->roomModel = $this->model('Room');
        $this->contractModel = $this->model('Contract');
        $this->paymentModel = $this->model('Payment');
        $this->requestModel = $this->model('RoomRequest');
    }

    // GET /api/rooms hoặc REST CRUD
    public function rooms($id = null) {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $room = $this->roomModel->getById($id);
                if ($room) {
                    $students = $this->roomModel->getStudentsInRoom($id);
                    $this->jsonResponse([
                        'status' => 'success',
                        'message' => 'Lấy thông tin phòng thành công',
                        'data' => $room,
                        'students' => $students
                    ]);
                } else {
                    $this->jsonResponse(['status' => 'error', 'message' => 'Phòng không tồn tại'], 404);
                }
                return;
            }

            $keyword = Validator::sanitize($_GET['search'] ?? '');
            $building = Validator::sanitize($_GET['building'] ?? '');
            $status = Validator::sanitize($_GET['status'] ?? '');
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;

            $result = $this->roomModel->getAll($keyword, $page, $limit, $building, $status);

            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Lấy danh sách phòng thành công',
                'data' => $result['data'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'totalPages' => $result['totalPages'],
                    'limit' => $limit
                ]
            ]);
        } else if ($method === 'POST') {
            if (!$this->isAdmin()) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này!'], 403);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $data = Validator::sanitize($input);

            if (empty($data['room_number']) || empty($data['building']) || empty($data['capacity']) || empty($data['price'])) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin phòng bắt buộc!'], 400);
                return;
            }

            if ($this->roomModel->findByRoomNumber($data['room_number'])) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Số phòng đã tồn tại!'], 409);
                return;
            }

            if ($this->roomModel->create($data)) {
                $this->jsonResponse(['status' => 'success', 'message' => 'Thêm phòng thành công']);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'Không thể thêm phòng'], 500);
            }
        } else if ($method === 'DELETE' && $id) {
            if (!$this->isAdmin()) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này!'], 403);
                return;
            }

            if ($this->roomModel->delete($id)) {
                $this->jsonResponse(['status' => 'success', 'message' => 'Xóa phòng thành công']);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'Không thể xóa phòng đang có sinh viên'], 400);
            }
        }
    }

    // GET /api/students hoặc REST CRUD
    public function students($id = null) {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $student = $this->studentModel->getById($id);
                if ($student) {
                    $this->jsonResponse(['status' => 'success', 'message' => 'Lấy thông tin sinh viên thành công', 'data' => $student]);
                } else {
                    $this->jsonResponse(['status' => 'error', 'message' => 'Sinh viên không tồn tại'], 404);
                }
                return;
            }

            $keyword = Validator::sanitize($_GET['search'] ?? '');
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
            $roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : null;

            $result = $this->studentModel->getAll($keyword, $roomId, $page, $limit);

            $this->jsonResponse([
                'status' => 'success',
                'data' => $result['data'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'totalPages' => $result['totalPages'],
                    'limit' => $limit
                ]
            ]);
        } else if ($method === 'DELETE' && $id) {
            if (!$this->isAdmin()) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này!'], 403);
                return;
            }

            $student = $this->studentModel->getById($id);
            if ($student) {
                $roomId = $student['room_id'];
                if ($this->studentModel->delete($id)) {
                    if ($roomId) $this->roomModel->updateOccupiedCount($roomId);
                    $this->jsonResponse(['status' => 'success', 'message' => 'Xóa sinh viên thành công']);
                    return;
                }
            }
            $this->jsonResponse(['status' => 'error', 'message' => 'Không thể xóa sinh viên'], 400);
        }
    }

    // GET /api/contracts
    public function contracts() {
        $keyword = Validator::sanitize($_GET['search'] ?? '');
        $status = Validator::sanitize($_GET['status'] ?? '');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;

        $result = $this->contractModel->getAll($keyword, $status, $page, $limit);

        $this->jsonResponse([
            'status' => 'success',
            'message' => 'Lấy danh sách hợp đồng thành công',
            'data' => $result['data'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'totalPages' => $result['totalPages']
            ]
        ]);
    }

    // GET /api/room-recommendations
    public function roomRecommendations() {
        $maxPrice = isset($_REQUEST['price']) ? (float)$_REQUEST['price'] : 1000000;
        $gender = isset($_REQUEST['gender']) ? Validator::sanitize($_REQUEST['gender']) : 'Nam';
        $building = isset($_REQUEST['building']) ? Validator::sanitize($_REQUEST['building']) : '';
        $desiredCapacity = isset($_REQUEST['capacity']) ? (int)$_REQUEST['capacity'] : 0;
        $roomType = isset($_REQUEST['room_type']) ? Validator::sanitize($_REQUEST['room_type']) : '';

        $matchedRooms = $this->roomModel->smartMatch($maxPrice, $gender, $building, $desiredCapacity, $roomType);

        $this->jsonResponse([
            'status' => 'success',
            'message' => 'Tính toán gợi ý phòng thông minh thành công',
            'data' => $matchedRooms
        ]);
    }

    // Alias cho /api/smart-match
    public function smartMatch() {
        $this->roomRecommendations();
    }

    // GET /api/room-requests
    public function roomRequests() {
        $keyword = Validator::sanitize($_GET['search'] ?? '');
        $status = Validator::sanitize($_GET['status'] ?? '');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;

        $result = $this->requestModel->getAll($keyword, $status, $page, $limit);

        $this->jsonResponse([
            'status' => 'success',
            'message' => 'Lấy danh sách yêu cầu chuyển/đăng ký phòng thành công',
            'data' => $result['data'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'totalPages' => $result['totalPages']
            ]
        ]);
    }

    // GET /api/stats
    public function stats() {
        $totalStudents = $this->studentModel->getTotalCount();
        $roomsData = $this->roomModel->getAll('', 1, 300);
        $unpaid = $this->paymentModel->getUnpaidCount();
        $pendingRequests = $this->requestModel->getPendingCount();
        $activeContracts = $this->contractModel->getActiveContractsCount();
        
        $this->jsonResponse([
            'status' => 'success',
            'stats' => [
                'totalStudents' => $totalStudents,
                'totalRooms' => count($roomsData['data']),
                'activeContracts' => $activeContracts,
                'unpaidInvoices' => $unpaid,
                'pendingRequests' => $pendingRequests
            ]
        ]);
    }
}
