<?php

// Thiết lập chế độ báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Thiết lập mã hóa UTF-8 toàn hệ thống
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=UTF-8');

// Đặt múi giờ mặc định
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Định nghĩa đường dẫn gốc dự án
define('APPROOT', dirname(__DIR__) . '/app');
define('URLROOT', 'http://localhost:8080');
define('BASE_URL', 'http://localhost:8080/');

// Tải các Core Modules
require_once APPROOT . '/core/Session.php';
require_once APPROOT . '/core/Validator.php';
require_once APPROOT . '/core/Database.php';
require_once APPROOT . '/core/Model.php';
require_once APPROOT . '/core/Controller.php';
require_once APPROOT . '/core/App.php';

// Khởi tạo Session
Session::init();

// Khởi tạo Core Application Engine
$app = new App();
