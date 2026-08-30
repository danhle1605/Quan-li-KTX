<?php

class DatabaseConfig {
    public static function getHost() {
        return $_ENV['DB_HOST'] ?? 'db';
    }

    public static function getUser() {
        return $_ENV['DB_USER'] ?? 'dms_user';
    }

    public static function getPass() {
        return $_ENV['DB_PASS'] ?? 'dms_password';
    }

    public static function getName() {
        return $_ENV['DB_NAME'] ?? 'dormitory_db';
    }
}
