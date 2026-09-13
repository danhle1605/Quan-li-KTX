<?php

class DashboardController extends Controller {
    public function index() {
        $this->requireLogin();

        $studentModel = $this->model('Student');
        $roomModel = $this->model('Room');
        $contractModel = $this->model('Contract');
        $paymentModel = $this->model('Payment');
        $requestModel = $this->model('RoomRequest');

        $userRole = Session::get('user_role');

        if ($userRole === 'student') {
            // === DASHBOARD STUDENT ===
            $student = $this->getStudentInfo();
            $currentRoom = null;
            $roommates = [];
            $activeContract = null;
            $latestRequest = null;

            if ($student) {
                if (!empty($student['room_id'])) {
                    $currentRoom = $roomModel->getById($student['room_id']);
                    $roommates = $roomModel->getStudentsInRoom($student['room_id']);
                }
                $activeContract = $contractModel->getActiveByStudentId($student['id']);
                $latestRequest = $requestModel->getLatestByStudentId($student['id']);
            }

            $data = [
                'title' => 'Dashboard Sinh Viên - KTX UTH',
                'userRole' => 'student',
                'student' => $student,
                'currentRoom' => $currentRoom,
                'roommates' => $roommates,
                'activeContract' => $activeContract,
                'latestRequest' => $latestRequest
            ];

            $this->view('dashboard/index', $data);
            return;
        }

        // === DASHBOARD ADMIN ===
        $totalStudents = $studentModel->getTotalCount();
        $roomsData = $roomModel->getAll('', 1, 200);
        $totalRooms = count($roomsData['data']);
        
        $availableRooms = 0;
        $fullRooms = 0;
        $maintenanceRooms = 0;
        $occupiedSeats = 0;
        $totalCapacity = 0;
        $estimatedRevenue = 0;

        foreach ($roomsData['data'] as $room) {
            $totalCapacity += $room['capacity'];
            $occupiedSeats += $room['occupied'];
            $estimatedRevenue += $room['price'] * $room['occupied'];

            if ($room['status'] === 'Available' && $room['occupied'] < $room['capacity']) {
                $availableRooms++;
            } else if ($room['occupied'] >= $room['capacity'] || $room['status'] === 'Full') {
                $fullRooms++;
            } else if ($room['status'] === 'Maintenance') {
                $maintenanceRooms++;
            }
        }

        // === THỐNG KÊ HỢP ĐỒNG & YÊU CẦU ===
        $activeContracts = $contractModel->getActiveContractsCount();
        $expiredContracts = $contractModel->getExpiredContractsCount();
        $expiringContracts7 = $contractModel->getExpiringContracts(7);   // khẩn 7 ngày
        $expiringContracts30 = $contractModel->getExpiringContracts(30); // 30 ngày
        $pendingRequestsCount = $requestModel->getPendingCount();

        // === THỐNG KÊ THANH TOÁN ===
        $unpaidInvoices = $paymentModel->getUnpaidCount();
        $unpaidTotal = $paymentModel->getUnpaidTotal();
        $paidRevenue = $paymentModel->getPaidRevenue();

        // === SMART ALERTS PANEL (SECTION X) ===
        $smartAlerts = [];

        // 1. Cảnh báo hợp đồng sắp hết hạn trong 7 ngày
        if (count($expiringContracts7) > 0) {
            $smartAlerts[] = [
                'level' => 'danger',
                'icon' => 'fa-fire',
                'message' => '⚠ ' . count($expiringContracts7) . ' hợp đồng sắp hết hạn trong 7 ngày tới!',
                'link' => BASE_URL . 'contract/index?status=Active',
                'label' => 'Xem hợp đồng'
            ];
        }

        // 2. Cảnh báo yêu cầu chuyển phòng đang chờ duyệt
        if ($pendingRequestsCount > 0) {
            $smartAlerts[] = [
                'level' => 'primary',
                'icon' => 'fa-bell',
                'message' => '🔔 ' . $pendingRequestsCount . ' yêu cầu chuyển/đăng ký phòng đang chờ duyệt!',
                'link' => BASE_URL . 'request/index?status=Pending',
                'label' => 'Duyệt yêu cầu'
            ];
        }

        // 3. Cảnh báo hóa đơn chưa thanh toán
        if ($unpaidInvoices > 0) {
            $smartAlerts[] = [
                'level' => 'warning',
                'icon' => 'fa-file-invoice-dollar',
                'message' => '⚡ ' . $unpaidInvoices . ' hóa đơn chưa thanh toán (tổng: ' . number_format($unpaidTotal, 0, ',', '.') . ' VNĐ)',
                'link' => BASE_URL . 'payment/index?status=Unpaid',
                'label' => 'Xem hóa đơn'
            ];
        }

        // 4. Cảnh báo sinh viên chưa có phòng
        $studentsWithoutRoom = $roomModel->getStudentsWithoutRoom();
        if ($studentsWithoutRoom > 0) {
            $smartAlerts[] = [
                'level' => 'info',
                'icon' => 'fa-user-clock',
                'message' => '👤 ' . $studentsWithoutRoom . ' sinh viên chưa được xếp phòng',
                'link' => BASE_URL . 'student/index',
                'label' => 'Xếp phòng ngay'
            ];
        }

        $roomStatsByBuilding = $roomModel->getRoomStatsByBuilding();

        $data = [
            'title' => 'Admin Dashboard - Quản lý Kí túc xá Thông minh UTH',
            'userRole' => 'admin',
            'totalStudents' => $totalStudents,
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'fullRooms' => $fullRooms,
            'maintenanceRooms' => $maintenanceRooms,
            'totalCapacity' => $totalCapacity,
            'occupiedSeats' => $occupiedSeats,
            'activeContracts' => $activeContracts,
            'expiredContracts' => $expiredContracts,
            'expiringContracts7' => $expiringContracts7,
            'pendingRequestsCount' => $pendingRequestsCount,
            'unpaidInvoices' => $unpaidInvoices,
            'unpaidTotal' => $unpaidTotal,
            'paidRevenue' => $paidRevenue,
            'estimatedRevenue' => $estimatedRevenue,
            'smartAlerts' => $smartAlerts,
            'roomStatsByBuilding' => $roomStatsByBuilding,
            'recentStudents' => $studentModel->getAll('', null, 1, 5)['data']
        ];

        $this->view('dashboard/index', $data);
    }
}
