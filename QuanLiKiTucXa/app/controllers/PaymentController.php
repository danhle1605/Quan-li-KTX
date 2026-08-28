<?php

class PaymentController extends Controller {
    private $paymentModel;
    private $roomModel;

    public function __construct() {
        $this->paymentModel = $this->model('Payment');
        $this->roomModel = $this->model('Room');
    }

    public function index() {
        $keyword = Validator::sanitize($_GET['search'] ?? '');
        $status = Validator::sanitize($_GET['status'] ?? '');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $result = $this->paymentModel->getAll($keyword, $status, $page, 6);
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
        if (!Session::has('user_id')) {
            Session::setFlash('error', 'Bạn cần đăng nhập để tạo hóa đơn!');
            $this->redirect('auth/login');
        }

        $rooms = $this->roomModel->getAll('', 1, 100)['data'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);

            if (empty($data['room_id']) || empty($data['billing_month'])) {
                Session::setFlash('error', 'Vui lòng chọn phòng và nhập tháng thanh toán!');
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
        if (!Session::has('user_id')) {
            Session::setFlash('error', 'Bạn cần đăng nhập để xác nhận thanh toán!');
            $this->redirect('auth/login');
        }

        if ($id) {
            if ($this->paymentModel->markAsPaid($id)) {
                Session::setFlash('success', 'Đã xác nhận thanh toán hóa đơn thành công!');
            } else {
                Session::setFlash('error', 'Không thể cập nhật trạng thái thanh toán.');
            }
        }
        $this->redirect('payment/index');
    }

    public function delete($id = null) {
        if (!Session::has('user_id')) {
            Session::setFlash('error', 'Bạn cần đăng nhập để xóa hóa đơn!');
            $this->redirect('auth/login');
        }

        if ($id) {
            if ($this->paymentModel->delete($id)) {
                Session::setFlash('success', 'Xóa hóa đơn thành công!');
            }
        }
        $this->redirect('payment/index');
    }
}
