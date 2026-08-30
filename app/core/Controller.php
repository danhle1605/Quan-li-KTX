<?php

abstract class Controller {
    // Model Loader
    public function model($model) {
        require_once __DIR__ . "/../models/" . $model . ".php";
        return new $model();
    }

    // View Render Helper
    public function view($view, $data = []) {
        extract($data);
        if (file_exists(__DIR__ . "/../views/" . $view . ".php")) {
            require_once __DIR__ . "/../views/" . $view . ".php";
        } else {
            die("View URL '$view' không tồn tại!");
        }
    }

    // REST API JSON Response Helper
    public function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // Redirect Helper
    public function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit;
    }

    // Authorization Helpers
    public function requireLogin() {
        if (!Session::has('user_id')) {
            Session::setFlash('error', 'Bạn cần đăng nhập để thực hiện chức năng này!');
            $this->redirect('auth/login');
        }
    }

    public function requireAdmin() {
        $this->requireLogin();
        if (Session::get('user_role') !== 'admin') {
            Session::setFlash('error', 'Bạn không có quyền truy cập chức năng dành cho Quản trị viên!');
            $this->redirect('dashboard/index');
        }
    }

    public function requireStudent() {
        $this->requireLogin();
        if (Session::get('user_role') !== 'student') {
            Session::setFlash('error', 'Chức năng này chỉ dành cho sinh viên!');
            $this->redirect('dashboard/index');
        }
    }

    public function isAdmin() {
        return Session::get('user_role') === 'admin';
    }

    public function isStudent() {
        return Session::get('user_role') === 'student';
    }

    public function getStudentInfo() {
        if (!Session::has('user_id')) return null;
        $studentModel = $this->model('Student');
        return $studentModel->getByUserId(Session::get('user_id'));
    }
}
