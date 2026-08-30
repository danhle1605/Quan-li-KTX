<header class="main-header">
    <div class="header-container">
        <a href="<?= BASE_URL ?>dashboard/index" class="brand-logo">
            <i class="fa-solid fa-building-user"></i>
            <div class="brand-text">
                <span class="brand-title">KTX UTH</span>
                <span class="brand-subtitle">SMART</span>
            </div>
        </a>

        <button class="mobile-toggle" id="mobileMenuBtn">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="main-nav" id="mainNav">
            <a href="<?= BASE_URL ?>dashboard/index" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> Tổng quan
            </a>
            
            <a href="<?= BASE_URL ?>room/index" class="nav-item <?= (strpos($_SERVER['REQUEST_URI'], 'room/index') !== false || strpos($_SERVER['REQUEST_URI'], 'room/detail') !== false) ? 'active' : '' ?>">
                <i class="fa-solid fa-door-open"></i> Danh sách phòng
            </a>

            <a href="<?= BASE_URL ?>room/map" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'room/map') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-map-location-dot"></i> Sơ đồ phòng
            </a>

            <a href="<?= BASE_URL ?>room/smartMatch" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'smartMatch') !== false ? 'active' : '' ?>" style="color: #6366f1; font-weight: 600;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Gợi ý Smart
            </a>

            <?php if (Session::get('user_role') === 'admin'): ?>
                <a href="<?= BASE_URL ?>student/index" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'student') !== false ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-graduate"></i> Sinh viên
                </a>
                <a href="<?= BASE_URL ?>contract/index" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'contract') !== false ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-contract"></i> Hợp đồng
                </a>
                <a href="<?= BASE_URL ?>request/index" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'request') !== false ? 'active' : '' ?>">
                    <i class="fa-solid fa-right-left"></i> Yêu cầu chuyển phòng
                </a>
                <a href="<?= BASE_URL ?>payment/index" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'payment') !== false ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Điện nước & Hóa đơn
                </a>
            <?php elseif (Session::get('user_role') === 'student'): ?>
                <a href="<?= BASE_URL ?>student/profile" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'student/profile') !== false ? 'active' : '' ?>">
                    <i class="fa-solid fa-id-card"></i> Hồ sơ của tôi
                </a>
                <a href="<?= BASE_URL ?>contract/index" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'contract') !== false ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-contract"></i> Hợp đồng của tôi
                </a>
                <a href="<?= BASE_URL ?>request/index" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'request') !== false ? 'active' : '' ?>">
                    <i class="fa-solid fa-paper-plane"></i> Yêu cầu chuyển phòng
                </a>
            <?php endif; ?>
            
            <?php if (Session::has('user_id')): ?>
                <div class="user-menu">
                    <span class="user-greeting">
                        <i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars(Session::get('user_name')) ?>
                        <small class="badge <?= Session::get('user_role') === 'admin' ? 'badge-danger' : 'badge-primary' ?>"><?= strtoupper(Session::get('user_role')) ?></small>
                    </span>
                    <a href="<?= BASE_URL ?>auth/logout" class="btn-logout" title="Đăng xuất">
                        <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                    </a>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="<?= BASE_URL ?>auth/login" class="btn btn-outline">Đăng nhập</a>
                    <a href="<?= BASE_URL ?>auth/register" class="btn btn-primary">Đăng ký</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- Alert Container cho Flash Messages -->
<div class="container message-container">
    <?php if ($success = Session::getFlash('success')): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i> <?= $success ?>
            <button class="close-alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($error = Session::getFlash('error')): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?>
            <button class="close-alert">&times;</button>
        </div>
    <?php endif; ?>
</div>
