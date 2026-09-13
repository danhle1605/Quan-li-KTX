<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

<main class="page-container container">
    <div class="page-header">
        <div>
            <h1><i class="fa-solid fa-user-graduate"></i> Quản lý sinh viên</h1>
            <p class="text-muted">Danh sách sinh viên cư trú ký túc xá</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>student/create" class="btn btn-primary">
                <i class="fa-solid fa-user-plus"></i> Thêm sinh viên
            </a>
        </div>
    </div>

    <!-- Thanh Tìm kiếm và Lọc dữ liệu -->
    <div class="filter-card card-box">
        <form id="searchStudentForm" class="filter-form" action="<?= BASE_URL ?>student/index" method="GET">
            <div class="form-group flex-1">
                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="ajaxSearchInput" name="search" class="form-control" 
                           placeholder="Nhập MSSV, Họ tên, Email hoặc Số điện thoại để tìm kiếm..." 
                           value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>

            <div class="form-group">
                <select name="room_id" id="ajaxRoomFilter" class="form-control">
                    <option value="">-- Tất cả các phòng --</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= $room['id'] ?>" <?= $roomId == $room['id'] ? 'selected' : '' ?>>
                            Phòng <?= htmlspecialchars($room['room_number']) ?> (<?= htmlspecialchars($room['building']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Lọc</button>
            <a href="<?= BASE_URL ?>student/index" class="btn btn-secondary"><i class="fa-solid fa-rotate"></i> Đặt lại</a>
        </form>
    </div>

    <!-- Bảng danh sách Sinh viên -->
    <div class="card-box">
        <div class="table-responsive">
            <table class="table" id="studentsTable">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>MSSV</th>
                        <th>Họ và Tên</th>
                        <th>Giới tính</th>
                        <th>Khoa</th>
                        <th>Phòng</th>
                        <th>Số điện thoại</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <img src="<?= BASE_URL ?>uploads/avatars/<?= htmlspecialchars($student['avatar']) ?>" 
                                         alt="Avatar" class="avatar-thumb">
                                </td>
                                <td><strong><?= htmlspecialchars($student['student_code']) ?></strong></td>
                                <td><?= htmlspecialchars($student['fullname']) ?></td>
                                <td>
                                    <span class="badge <?= $student['gender'] === 'Nam' ? 'badge-blue' : 'badge-pink' ?>">
                                        <?= htmlspecialchars($student['gender']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($student['faculty']) ?></td>
                                <td>
                                    <?php if ($student['room_number']): ?>
                                        <span class="badge badge-info">
                                            <?= htmlspecialchars($student['room_number']) ?> (<?= htmlspecialchars($student['building']) ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Chưa xếp phòng</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($student['phone']) ?></td>
                                <td class="table-actions">
                                    <button class="btn btn-sm btn-outline btn-view-student" 
                                            data-student='<?= json_encode($student, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>student/edit/<?= $student['id'] ?>" class="btn btn-sm btn-outline">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>student/delete/<?= $student['id'] ?>" 
                                       class="btn btn-sm btn-danger btn-delete-confirm">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Không tìm thấy sinh viên nào phù hợp.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang Pagination -->
        <div class="pagination-container" id="paginationWrapper">
            <?php if (!empty($totalPages) && $totalPages > 1): ?>
                <ul class="pagination">
                    <li class="page-item page-nav page-prev <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <?php if ($page > 1): ?>
                            <a class="page-link" href="<?= BASE_URL ?>student/index?page=<?= $page - 1 ?>&search=<?= urlencode($keyword ?? '') ?>&room_id=<?= $roomId ?? '' ?>">
                                <i class="fa-solid fa-arrow-left"></i> Trước
                            </a>
                        <?php else: ?>
                            <span class="page-link disabled">
                                <i class="fa-solid fa-arrow-left"></i> Trước
                            </span>
                        <?php endif; ?>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item page-number <?= $i == $page ? 'active' : '' ?>">
                            <?php if ($i == $page): ?>
                                <span class="page-link"><?= $i ?></span>
                            <?php else: ?>
                                <a class="page-link" href="<?= BASE_URL ?>student/index?page=<?= $i ?>&search=<?= urlencode($keyword ?? '') ?>&room_id=<?= $roomId ?? '' ?>"><?= $i ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item page-nav page-next <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <?php if ($page < $totalPages): ?>
                            <a class="page-link" href="<?= BASE_URL ?>student/index?page=<?= $page + 1 ?>&search=<?= urlencode($keyword ?? '') ?>&room_id=<?= $roomId ?? '' ?>">
                                Sau <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="page-link disabled">
                                Sau <i class="fa-solid fa-arrow-right"></i>
                            </span>
                        <?php endif; ?>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Modal Chi tiết Sinh viên jQuery Popup -->
<div class="modal" id="studentDetailModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-id-card"></i> Thông Tin Chi Tiết Sinh Viên</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body" id="studentModalBody">
            <!-- Nội dung do jQuery chèn động vào -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Đóng</button>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
