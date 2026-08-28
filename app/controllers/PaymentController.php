<?php

class PaymentController extends Controller {
    private $paymentModel;
    private $roomModel;

    public function __construct() {
        $this->paymentModel = $this->model('Payment');
        $this->roomModel = $this->model('Room');
    }

    public function index() {
        $this->requireLogin();

        $keyword = Validator::sanitize($_GET['search'] ?? '');
        $status = Validator::sanitize($_GET['status'] ?? '');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $roomId = null;
        if ($this->isStudent()) {
            $student = $this->getStudentInfo();
            $roomId = $student['room_id'] ?? 0;
        }

        $result = $this->paymentModel->getAll($keyword, $status, $page, 6, $roomId);
        $rooms = $this->roomModel->getAll('', 1, 100)['data'];

        $data = [
            'title' => 'Quản lý Thanh toán & Hóa đơn KTX',
            'invoices' => $result['data'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'keyword' => $keyword,
            'status' => $status,
            'rooms' => $rooms
        ];

        $this->view('payments/index', $data);
    }

    public function create() {
        $this->requireAdmin();

        $rooms = $this->roomModel->getAll('', 1, 100)['data'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);

            if (empty($data['room_id']) || empty($data['billing_month'])) {
                Session::setFlash('error', 'Vui lòng chọn phòng và nhập tháng thanh toán!');
                $this->view('payments/create', ['rooms' => $rooms, 'formData' => $data]);
                return;
            }

            foreach (['room_fee', 'electricity_fee', 'water_fee'] as $fee) {
                if (!isset($data[$fee]) || !is_numeric($data[$fee]) || (float)$data[$fee] < 0) {
                    Session::setFlash('error', 'Các khoản phí phải là số không âm!');
                    $this->view('payments/create', ['rooms' => $rooms, 'formData' => $data]);
                    return;
                }
            }

            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $data['billing_month'])) {
                Session::setFlash('error', 'Tháng thanh toán phải có định dạng MM/YYYY hợp lệ!');
                $this->view('payments/create', ['rooms' => $rooms, 'formData' => $data]);
                return;
            }

            // Lấy thông tin giá phòng làm tiền phòng mặc định
            $room = $this->roomModel->getById($data['room_id']);
            if ($room) {
                $data['room_number'] = $room['room_number'];
                if (empty($data['room_fee'])) {
                    $data['room_fee'] = $room['price'];
                }
            }

            if (!$room || $this->paymentModel->existsForRoomMonth($data['room_id'], $data['billing_month'])) {
                Session::setFlash('error', 'Phòng này đã có hóa đơn trong tháng đã chọn!');
                $this->view('payments/create', ['rooms' => $rooms, 'formData' => $data]);
                return;
            }

            if ($this->paymentModel->create($data)) {
                Session::setFlash('success', 'Tạo hóa đơn điện nước mới thành công!');
                $this->redirect('payment/index');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra. Không thể tạo hóa đơn!');
                $this->view('payments/create', ['rooms' => $rooms, 'formData' => $data]);
            }
        } else {
            $this->view('payments/create', ['rooms' => $rooms]);
        }
    }

    public function pay($id = null) {
        $this->requireLogin();

        if ($id) {
            if ($this->isStudent()) {
                $student = $this->getStudentInfo();
                if (!$student || !$this->paymentModel->belongsToRoom($id, $student['room_id'])) {
                    Session::setFlash('error', 'Bạn không có quyền thanh toán hóa đơn này!');
                    $this->redirect('payment/index');
                }
            }
            if ($this->paymentModel->markAsPaid($id)) {
                Session::setFlash('success', 'Đã xác nhận thanh toán hóa đơn thành công!');
            } else {
                Session::setFlash('error', 'Không thể cập nhật trạng thái thanh toán.');
            }
        }
        $this->redirect('payment/index');
    }

    public function detail($id = null) {
        $this->requireLogin();

        $invoice = $id ? $this->paymentModel->getById($id) : null;
        if (!$invoice) {
            Session::setFlash('error', 'Hóa đơn không tồn tại!');
            $this->redirect('payment/index');
        }

        if ($this->isStudent()) {
            $student = $this->getStudentInfo();
            if (!$student || !$this->paymentModel->belongsToRoom($id, $student['room_id'])) {
                Session::setFlash('error', 'Bạn không có quyền xem hóa đơn này!');
                $this->redirect('payment/index');
            }
        }

        $this->view('payments/detail', [
            'title' => 'Chi tiết hóa đơn ' . $invoice['invoice_code'],
            'invoice' => $invoice
        ]);
    }

    public function delete($id = null) {
        $this->requireAdmin();

        if ($id) {
            if ($this->paymentModel->delete($id)) {
                Session::setFlash('success', 'Xóa hóa đơn thành công!');
            }
        }
        $this->redirect('payment/index');
    }
}
