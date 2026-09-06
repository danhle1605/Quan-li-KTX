<?php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function login() {
        // Kiểm tra xem đã đăng nhập chưa
        if (Session::has('user_id')) {
            $this->redirect('dashboard/index');
        }

        // Tự động đăng nhập từ Cookie Remember Me nếu có
        $rememberToken = Session::getCookie('remember_token');
        if ($rememberToken) {
            $user = $this->userModel->findByRememberToken($rememberToken);
            if ($user) {
                Session::set('user_id', $user['id']);
                Session::set('user_name', $user['fullname']);
                Session::set('user_role', $user['role']);
                $this->redirect('dashboard/index');
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            $remember = isset($_POST['remember']);

            if (empty($username) || empty($password)) {
                Session::setFlash('error', 'Vui lòng nhập tên đăng nhập và mật khẩu!');
                $this->view('auth/login', ['username' => $username]);
                return;
            }

            $user = $this->userModel->login($username, $password);
            if ($user) {
                Session::set('user_id', $user['id']);
                Session::set('user_name', $user['fullname']);
                Session::set('user_role', $user['role']);

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->updateRememberToken($user['id'], $token);
                    Session::setCookie('remember_token', $token, 30); // 30 ngày
                }

                Session::setFlash('success', 'Đăng nhập thành công! Chào mừng ' . $user['fullname']);
                $this->redirect('dashboard/index');
            } else {
                Session::setFlash('error', 'Tên đăng nhập hoặc mật khẩu không chính xác!');
                $this->view('auth/login', ['username' => $username]);
            }
        } else {
            $this->view('auth/login');
        }
    }

    public function register() {
        if (Session::has('user_id')) {
            $this->redirect('dashboard/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = Validator::sanitize($_POST);
            $username = $data['username'] ?? '';
            $fullname = $data['fullname'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $confirm_password = $data['confirm_password'] ?? '';

            // Validator server-side
            if (empty($username) || empty($fullname) || empty($email) || empty($password)) {
                Session::setFlash('error', 'Vui lòng điền đầy đủ các trường thông tin!');
                $this->view('auth/register', $data);
                return;
            }

            if (!Validator::validateEmail($email)) {
                Session::setFlash('error', 'Địa chỉ email không hợp lệ!');
                $this->view('auth/register', $data);
                return;
            }

            if ($password !== $confirm_password) {
                Session::setFlash('error', 'Mật khẩu xác nhận không khớp!');
                $this->view('auth/register', $data);
                return;
            }

            if ($this->userModel->findByUsername($username)) {
                Session::setFlash('error', 'Tên đăng nhập đã tồn tại!');
                $this->view('auth/register', $data);
                return;
            }

            if ($this->userModel->findByEmail($email)) {
                Session::setFlash('error', 'Email đã được đăng ký tài khoản khác!');
                $this->view('auth/register', $data);
                return;
            }

            if ($this->userModel->register($username, $password, $fullname, $email, 'student')) {
                Session::setFlash('success', 'Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay bây giờ.');
                $this->redirect('auth/login');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại!');
                $this->view('auth/register', $data);
            }
        } else {
            $this->view('auth/register');
        }
    }

    public function logout() {
        $userId = Session::get('user_id');
        if ($userId) {
            $this->userModel->updateRememberToken($userId, null);
        }
        Session::deleteCookie('remember_token');
        Session::destroy();
        Session::init();
        Session::setFlash('success', 'Bạn đã đăng xuất khỏi hệ thống.');
        $this->redirect('auth/login');
    }
}
