<?php

class Session {
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        if (session_status() != PHP_SESSION_NONE) {
            session_destroy();
            $_SESSION = [];
        }
    }

    public static function setFlash($key, $message) {
        $_SESSION['flash'][$key] = $message;
    }

    public static function getFlash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }

    // Quản lý Cookie cho tính năng Remember Me
    public static function setCookie($name, $value, $expiryDays = 30) {
        setcookie($name, $value, time() + (86400 * $expiryDays), "/", "", false, true);
    }

    public static function getCookie($name) {
        return $_COOKIE[$name] ?? null;
    }

    public static function deleteCookie($name) {
        if (isset($_COOKIE[$name])) {
            setcookie($name, '', time() - 3600, "/");
        }
    }
}
