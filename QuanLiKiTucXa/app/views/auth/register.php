<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="auth-container container">
    <div class="auth-card">
        <div class="auth-header">
            <h2><i class="fa-solid fa-user-plus"></i> Đăng Ký Tài Khoản</h2>
            <p>Tạo tài khoản mới dành cho Sinh viên</p>
        </div>

        <form action="<?= BASE_URL ?>auth/register" method="POST" id="registerForm" class="auth-form">
            <div class="form-group">
                <label for="fullname"><i class="fa-solid fa-id-card"></i> Họ và tên</label>
                <input type="text" id="fullname" name="fullname" class="form-control" 
                       value="<?= htmlspecialchars($fullname ?? '') ?>" placeholder="Nguyễn Văn A" required>
                <span class="error-feedback" id="fullnameError"></span>
            </div>

            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user"></i> Tên đăng nhập</label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?= htmlspecialchars($username ?? '') ?>" placeholder="sv2026004" required>
                <span class="error-feedback" id="usernameError"></span>
            </div>

            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email sinh viên</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($email ?? '') ?>" placeholder="student@gmail.com" required>
                <span class="error-feedback" id="emailError"></span>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-key"></i> Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Nhập ít nhất 6 ký tự..." required>
                <span class="error-feedback" id="passwordError"></span>
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fa-solid fa-lock"></i> Xác nhận Mật khẩu</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                       placeholder="Nhập lại mật khẩu..." required>
                <span class="error-feedback" id="confirmPasswordError"></span>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa-solid fa-user-check"></i> Đăng Ký Ngay
            </button>

            <div class="auth-footer">
                Đã có tài khoản? <a href="<?= BASE_URL ?>auth/login">Đăng nhập tại đây</a>
            </div>
        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
