<?php

class Validator {
    public static function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePhone($phone) {
        return preg_match('/^[0-9]{10,11}$/', $phone);
    }

    public static function validateStudentCode($code) {
        return preg_match('/^[A-Za-z0-9]{5,20}$/', $code);
    }
}
