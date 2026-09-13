<?php

class App {
    protected $controller = "DashboardController";
    protected $method = "index";
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // Xử lý Route REST API: e.g., /api/room-recommendations -> ApiController::roomRecommendations
        if (isset($url[0]) && strtolower($url[0]) === 'api') {
            $this->controller = "ApiController";
            array_shift($url); // loại bỏ 'api'
            if (isset($url[0])) {
                $rawMethod = $url[0];
                $camelMethod = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $rawMethod))));
                
                require_once __DIR__ . "/../controllers/ApiController.php";
                if (method_exists("ApiController", $camelMethod)) {
                    $this->method = $camelMethod;
                } else if (method_exists("ApiController", $rawMethod)) {
                    $this->method = $rawMethod;
                }
                array_shift($url);
            } else {
                $this->method = "index";
            }
        } 
        // Route MVC thông thường: e.g., /room/map -> RoomController::map
        else if (isset($url[0])) {
            $controllerName = ucfirst($url[0]) . "Controller";
            if (file_exists(__DIR__ . "/../controllers/" . $controllerName . ".php")) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }

        require_once __DIR__ . "/../controllers/" . $this->controller . ".php";
        if (is_string($this->controller)) {
            $this->controller = new $this->controller;
        }

        if (isset($url[1])) {
            $rawMethod = $url[1];
            $camelMethod = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $rawMethod))));

            if (method_exists($this->controller, $camelMethod)) {
                $this->method = $camelMethod;
                unset($url[1]);
            } else if (method_exists($this->controller, $rawMethod)) {
                $this->method = $rawMethod;
                unset($url[1]);
            }
        }

        $this->params = $url ? array_values($url) : [];

        // Gọi hàm của Controller tương ứng với danh sách tham số
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
