<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="auth-container container">
    <div class="auth-card">
        <div class="auth-header">
            <h2><i class="fa-solid fa-lock"></i> Đăng Nhập KTX UTH</h2>
            <p>Hệ thống Quản lý Kí túc xá – Trường ĐH Giao thông vận tải TP.HCM</p>
        </div>

        <form action="<?= BASE_URL ?>auth/login" method="POST" id="loginForm" class="auth-form">
            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user"></i> Tên đăng nhập hoặc Email</label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?= htmlspecialchars($username ?? '') ?>" placeholder="Nhập admin hoặc email..." required>
                <span class="error-feedback" id="usernameError"></span>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-key"></i> Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Nhập mật khẩu..." required>
                <span class="error-feedback" id="passwordError"></span>
            </div>

            <div class="form-group checkbox-group">
                <label class="custom-checkbox">
                    <input type="checkbox" name="remember" value="1">
                    <span class="checkmark"></span>
                    Ghi nhớ đăng nhập
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập
            </button>

            <div class="auth-footer">
                Chưa có tài khoản? <a href="<?= BASE_URL ?>auth/register">Đăng ký ngay</a>
            </div>
        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
