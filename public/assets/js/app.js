/**
 * Application JavaScript & jQuery Logic
 * Smart Dormitory Management System - KTX UTH
 */

$(document).ready(function() {

    // 1. Navbar Toggle Mobile Responsive
    $('#mobileMenuBtn').on('click', function() {
        $('#mainNav').toggleClass('open');
    });

    // 2. Alert Close Button
    $('.close-alert').on('click', function() {
        $(this).closest('.alert').fadeOut(300, function() {
            $(this).remove();
        });
    });

    // Tự động ẩn thông báo thành công sau 5 giây
    setTimeout(function() {
        $('.alert-success').fadeOut(400);
    }, 5000);

    // 3. Live Preview Upload Ảnh Đại Diện (File Upload Preview - Fixed: event.target.result)
    $('#avatarInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                $('#avatarPreview').attr('src', event.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // 4. Modal Pop-up Chi Tiết Phòng ở & Danh sách Sinh Viên đang ở (jQuery AJAX)
    $(document).on('click', '.btn-view-room-detail', function() {
        const roomId = $(this).data('id');
        const baseUrl = window.location.origin + '/';

        $('#roomModalTitle').html('<i class="fa-solid fa-door-open"></i> Đang tải thông tin...');
        $('#roomModalBody').html('<p class="text-center py-4"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><br>Đang truy vấn dữ liệu phòng...</p>');
        $('#roomDetailModal').addClass('show');

        $.ajax({
            url: baseUrl + 'room/detail/' + roomId + '?json=1',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success' && res.room) {
                    const r = res.room;
                    const students = res.students || [];

                    $('#roomModalTitle').html(`<i class="fa-solid fa-door-open"></i> Chi Tiết Phòng ${escapeHtml(r.room_number)} (${escapeHtml(r.building)})`);

                    let studentsHtml = '';
                    if (students.length > 0) {
                        studentsHtml = `
                            <table class="table margin-top-15">
                                <thead>
                                    <tr>
                                        <th>Ảnh</th>
                                        <th>Họ & Tên</th>
                                        <th>MSSV</th>
                                        <th>SĐT</th>
                                        <th>Khoa</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        students.forEach(function(s) {
                            studentsHtml += `
                                <tr>
                                    <td><img src="${baseUrl}uploads/avatars/${escapeHtml(s.avatar)}" class="avatar-thumb"></td>
                                    <td><strong>${escapeHtml(s.fullname)}</strong></td>
                                    <td>${escapeHtml(s.student_code)}</td>
                                    <td>${escapeHtml(s.phone)}</td>
                                    <td>${escapeHtml(s.faculty)}</td>
                                </tr>
                            `;
                        });
                        studentsHtml += '</tbody></table>';
                    } else {
                        studentsHtml = '<p class="text-muted text-center py-3">Hiện tại phòng chưa có sinh viên nào sinh sống.</p>';
                    }

                    let statusBadgeClass = 'badge-success';
                    if (r.status === 'Full') statusBadgeClass = 'badge-danger';
                    else if (r.status === 'Maintenance') statusBadgeClass = 'badge-secondary';

                    const bodyHtml = `
                        <div class="room-detail-popup">
                            <div class="popup-meta-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; background:#f8fafc; padding:15px; border-radius:8px;">
                                <div><strong>Số phòng:</strong> ${escapeHtml(r.room_number)}</div>
                                <div><strong>Tòa nhà:</strong> ${escapeHtml(r.building)} (Tầng ${r.floor || 1})</div>
                                <div><strong>Loại phòng:</strong> ${escapeHtml(r.room_type || 'Thường')}</div>
                                <div><strong>Giá phòng:</strong> <span class="text-primary font-weight-bold">${Number(r.price).toLocaleString()} VNĐ/tháng</span></div>
                                <div><strong>Sức chứa:</strong> ${r.occupied} / ${r.capacity} sinh viên</div>
                                <div><strong>Trạng thái:</strong> <span class="badge ${statusBadgeClass}">${escapeHtml(r.status)}</span></div>
                            </div>
                            <div class="margin-top-15">
                                <strong><i class="fa-solid fa-align-left"></i> Mô tả tiện nghi:</strong>
                                <p class="text-muted">${escapeHtml(r.description || 'Chưa có mô tả chi tiết.')}</p>
                            </div>
                            <div class="margin-top-20">
                                <h3><i class="fa-solid fa-users"></i> Danh Sách Sinh Viên Đang Ở (${students.length})</h3>
                                ${studentsHtml}
                            </div>
                        </div>
                    `;
                    $('#roomModalBody').html(bodyHtml);
                } else {
                    $('#roomModalBody').html('<p class="text-danger text-center py-3">Không tìm thấy thông tin phòng.</p>');
                }
            },
            error: function() {
                $('#roomModalBody').html('<p class="text-danger text-center py-3">Có lỗi xảy ra khi kết nối máy chủ.</p>');
            }
        });
    });

    // 5. Modal Popup Gia Hạn Hợp Đồng
    $(document).on('click', '.btn-renew-contract', function() {
        const contractId = $(this).data('id');
        const endDate = $(this).data('end');
        const baseUrl = window.location.origin + '/';

        $('#renewContractForm').attr('action', baseUrl + 'contract/renew/' + contractId);
        if (endDate) {
            $('#renew_end_date').val(endDate);
        }
        $('#renewContractModal').addClass('show');
    });

    // Đóng Modal
    $('.close-modal').on('click', function() {
        $('.modal').removeClass('show');
    });

    $(window).on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            $('.modal').removeClass('show');
        }
    });

    // 6. Form Client-Side Validation
    $('#loginForm').on('submit', function(e) {
        let valid = true;
        const username = $('#username').val().trim();
        const password = $('#password').val().trim();

        if (username === '') {
            $('#usernameError').text('Vui lòng nhập tên đăng nhập.').show();
            valid = false;
        } else {
            $('#usernameError').hide();
        }

        if (password === '') {
            $('#passwordError').text('Vui lòng nhập mật khẩu.').show();
            valid = false;
        } else {
            $('#passwordError').hide();
        }

        if (!valid) e.preventDefault();
    });

    // Cập nhật tổng tiền hóa đơn ngay khi nhập các khoản phí.
    function updateInvoiceTotal() {
        let total = 0;
        $('#paymentForm input[type="number"]').each(function() {
            total += Number($(this).val()) || 0;
        });
        $('#invoiceTotal').text(total.toLocaleString('vi-VN') + ' VNĐ');
    }

    $('#paymentForm input[type="number"]').on('input', updateInvoiceTotal);
    updateInvoiceTotal();

    // 7. Xác nhận thao tác xóa
    $(document).on('click', '.btn-delete-confirm', function(e) {
        if (!confirm('Bạn có chắc chắn muốn thực hiện hành động này? Dữ liệu sẽ bị xóa khỏi hệ thống!')) {
            e.preventDefault();
        }
    });

    // 8. Smart Alert Panel: toggle thu/mở danh sách cảnh báo
    $('#btnToggleAlerts').on('click', function() {
        const list = $('#alertItemsList');
        const icon = $('#iconToggleAlerts');
        list.slideToggle(250);
        icon.toggleClass('fa-chevron-up fa-chevron-down');
    });

    // 9. Smart Match Form: hiển thị loading animation khi submit AJAX
    $('#smartMatchForm').on('submit', function() {
        const btn = $('#btnRunSmartMatch');
        btn.prop('disabled', true)
           .html('<i class="fa-solid fa-spinner fa-spin"></i> Đang phân tích thuật toán scoring (100đ)...');
    });

    // Helper escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
