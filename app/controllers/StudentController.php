<?php

class StudentController extends Controller {
    private $studentModel;
    private $roomModel;

    public function __construct() {
        $this->studentModel = $this->model('Student');
        $this->roomModel = $this->model('Room');
    }

    public function index() {
        $this->requireAdmin();

        $keyword = Validator::sanitize($_GET['search'] ?? '');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : null;

        $studentsResult = $this->studentModel->getAll($keyword, $roomId, $page, 10);
        $rooms = $this->roomModel->getAll('', 1, 200)['data'];

        $data = [
            'title' => 'Quản lý Sinh viên Kí túc xá',
            'students' => $studentsResult['data'],
            'total' => $studentsResult['total'],
            'page' => $studentsResult['page'],
            'totalPages' => $studentsResult['totalPages'],
            'keyword' => $keyword,
            'roomId' => $roomId,
            'rooms' => $rooms
        ];

        $this->view('students/index', $data);
    }

    public function profile() {
        $this->requireLogin();
        $student = $this->getStudentInfo();

        if (!$student) {
            Session::setFlash('error', 'Hồ sơ sinh viên chưa được liên kết với tài khoản này!');
            $this->redirect('dashboard/index');
            return;
        }

        $roommates = [];
        if (!empty($student['room_id'])) {
            $roommates = $this->roomModel->getStudentsInRoom($student['room_id']);
        }

        $data = [
            'title' => 'Hồ sơ cá nhân Sinh viên',
            'student' => $student,
            'roommates' => $roommates
        ];

        $this->view('students/profile', $data);
    }

    public function create() {
        $this->requireAdmin();

        $rooms = $this->roomModel->getAvailableRooms();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);

            // Validation server-side
            if (empty($data['student_code']) || empty($data['fullname']) || empty($data['phone']) || empty($data['email'])) {
                Session::setFlash('error', 'Vui lòng điền đầy đủ các thông tin bắt buộc (*)!');
                $this->view('students/create', ['rooms' => $rooms, 'formData' => $data]);
                return;
            }

            if (!Validator::validateEmail($data['email'])) {
                Session::setFlash('error', 'Địa chỉ email không hợp lệ!');
                $this->view('students/create', ['rooms' => $rooms, 'formData' => $data]);
                return;
            }

            if (!Validator::validatePhone($data['phone'])) {
                Session::setFlash('error', 'Số điện thoại không hợp lệ (gồm 10-11 chữ số)!');
                $this->view('students/create', ['rooms' => $rooms, 'formData' => $data]);
                return;
            }

            if ($this->studentModel->findByStudentCode($data['student_code'])) {
                Session::setFlash('error', 'Mã số sinh viên "' . $data['student_code'] . '" đã tồn tại trên hệ thống!');
                $this->view('students/create', ['rooms' => $rooms, 'formData' => $data]);
                return;
            }

            if ($this->studentModel->findByEmail($data['email'])) {
                Session::setFlash('error', 'Email "' . $data['email'] . '" đã được sử dụng bởi sinh viên khác!');
                $this->view('students/create', ['rooms' => $rooms, 'formData' => $data]);
                return;
            }

            // Kiểm tra phòng chọn không Full hoặc Maintenance
            if (!empty($data['room_id'])) {
                $targetRoom = $this->roomModel->getById($data['room_id']);
                if (!$targetRoom || $targetRoom['status'] === 'Maintenance' || $targetRoom['occupied'] >= $targetRoom['capacity']) {
                    Session::setFlash('error', 'Không thể xếp sinh viên vào phòng này vì phòng đã Đầy hoặc đang Bảo trì!');
                    $this->view('students/create', ['rooms' => $rooms, 'formData' => $data]);
                    return;
                }
            }

            // Xử lý upload avatar
            $avatarName = 'default.png';
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['avatar']['tmp_name'];
                $fileName = $_FILES['avatar']['name'];
                $fileSize = $_FILES['avatar']['size'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $mimeType = mime_content_type($fileTmpPath);
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (in_array($fileExtension, $allowedExtensions) && in_array($mimeType, $allowedMimes) && $fileSize <= 2 * 1024 * 1024) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $uploadFileDir = dirname(dirname(__DIR__)) . '/public/uploads/avatars/';
                    
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }

                    $destPath = $uploadFileDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $avatarName = $newFileName;
                    }
                } else {
                    Session::setFlash('error', 'Ảnh đại diện không hợp lệ (Chấp nhận PNG/JPG < 2MB)!');
                    $this->view('students/create', ['rooms' => $rooms, 'formData' => $data]);
                    return;
                }
            }

            $data['avatar'] = $avatarName;

            $studentId = $this->studentModel->create($data);
            if ($studentId) {
                if (!empty($data['room_id'])) {
                    $this->roomModel->updateOccupiedCount($data['room_id']);
                }
                Session::setFlash('success', 'Thêm sinh viên mới thành công!');
                $this->redirect('student/index');
            } else {
                Session::setFlash('error', 'Không thể thêm sinh viên. Vui lòng kiểm tra lại!');
                $this->view('students/create', ['rooms' => $rooms, 'formData' => $data]);
            }
        } else {
            $this->view('students/create', ['rooms' => $rooms]);
        }
    }

    public function edit($id = null) {
        $this->requireAdmin();

        if (!$id) $this->redirect('student/index');
        $student = $this->studentModel->getById($id);
        if (!$student) $this->redirect('student/index');

        $rooms = $this->roomModel->getAvailableRooms();
        // Bổ sung phòng hiện tại của sinh viên vào danh sách lựa chọn
        if (!empty($student['room_id'])) {
            $currentRoomObj = $this->roomModel->getById($student['room_id']);
            if ($currentRoomObj) {
                $alreadyExists = false;
                foreach ($rooms as $r) {
                    if ($r['id'] == $currentRoomObj['id']) {
                        $alreadyExists = true;
                        break;
                    }
                }
                if (!$alreadyExists) {
                    array_unshift($rooms, $currentRoomObj);
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);
            $oldRoomId = $student['room_id'];

            if (empty($data['student_code']) || empty($data['fullname']) || empty($data['phone']) || empty($data['email'])) {
                Session::setFlash('error', 'Vui lòng điền đầy đủ các thông tin bắt buộc (*)!');
                $this->view('students/edit', ['student' => $student, 'rooms' => $rooms]);
                return;
            }

            if (!Validator::validateEmail($data['email'])) {
                Session::setFlash('error', 'Địa chỉ email không hợp lệ!');
                $this->view('students/edit', ['student' => $student, 'rooms' => $rooms]);
                return;
            }

            if ($this->studentModel->findByStudentCode($data['student_code'], $id)) {
                Session::setFlash('error', 'Mã số sinh viên đã bị trùng với sinh viên khác!');
                $this->view('students/edit', ['student' => $student, 'rooms' => $rooms]);
                return;
            }

            if ($this->studentModel->findByEmail($data['email'], $id)) {
                Session::setFlash('error', 'Email đã bị trùng với sinh viên khác!');
                $this->view('students/edit', ['student' => $student, 'rooms' => $rooms]);
                return;
            }

            // Xử lý Upload ảnh mới nếu có
            $avatarName = $student['avatar'];
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['avatar']['tmp_name'];
                $fileName = $_FILES['avatar']['name'];
                $fileSize = $_FILES['avatar']['size'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $mimeType = mime_content_type($fileTmpPath);
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (in_array($fileExtension, $allowedExtensions) && in_array($mimeType, $allowedMimes) && $fileSize <= 2 * 1024 * 1024) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $uploadFileDir = dirname(dirname(__DIR__)) . '/public/uploads/avatars/';
                    $destPath = $uploadFileDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $avatarName = $newFileName;
                    }
                }
            }

            $data['avatar'] = $avatarName;

            if ($this->studentModel->update($id, $data)) {
                if ($oldRoomId != $data['room_id']) {
                    if ($oldRoomId) $this->roomModel->updateOccupiedCount($oldRoomId);
                    if (!empty($data['room_id'])) $this->roomModel->updateOccupiedCount($data['room_id']);
                }
                Session::setFlash('success', 'Cập nhật thông tin sinh viên thành công!');
                $this->redirect('student/index');
            } else {
                Session::setFlash('error', 'Cập nhật thất bại. Vui lòng thử lại!');
                $this->view('students/edit', ['student' => $student, 'rooms' => $rooms]);
            }
        } else {
            $this->view('students/edit', ['student' => $student, 'rooms' => $rooms]);
        }
    }

    public function delete($id = null) {
        $this->requireAdmin();

        if ($id) {
            $student = $this->studentModel->getById($id);
            if ($student) {
                $roomId = $student['room_id'];
                if ($this->studentModel->delete($id)) {
                    if ($roomId) {
                        $this->roomModel->updateOccupiedCount($roomId);
                    }
                    Session::setFlash('success', 'Xóa sinh viên thành công!');
                }
            }
        }
        $this->redirect('student/index');
    }
}
