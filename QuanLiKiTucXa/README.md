# BÁO CÁO ĐỒ ÁN MÔN HỌC: LẬP TRÌNH WEB
## ĐỀ TÀI: HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ THÔNG MINH (SMART DORMITORY MANAGEMENT SYSTEM - KTX UTH)

---

## I. GIỚI THIỆU ĐỀ TÀI & MỤC TIÊU
Hệ thống **"Quản lý Kí túc xá Thông minh" (Smart Dormitory Management System)** được nâng cấp từ nền tảng ứng dụng web quản lý kí túc xá Trường Đại học Giao thông vận tải TP.HCM (UTH). 

Hệ thống nhằm tối ưu hóa quy trình quản lý thông tin phòng ở, đăng ký lưu trú của sinh viên, tự động hóa xử lý trạng thái hợp đồng, duyệt các yêu cầu chuyển phòng trực tuyến, gợi ý phòng ở thông minh theo thuật toán scoring 100 điểm và trực quan hóa sơ đồ trạng thái phòng ở theo từng tòa nhà.

---

## II. CÔNG NGHỆ BẮT BUỘC GIỮ NGUYÊN (ĐÁP ỨNG 100%)
Dự án tuân thủ nghiêm ngặt yêu cầu công nghệ thuần, **KHÔNG rewrite lại toàn bộ project** và **KHÔNG sử dụng framework PHP/JS có sẵn**:
- **Core**: PHP 8.2 (Pure PHP) xây dựng theo mô hình **MVC Pattern tự xây dựng**.
- **Database**: MySQL 8.0 kết nối qua **PDO Singleton** an toàn, chống SQL Injection với Prepared Statements.
- **Frontend**: HTML5, CSS3 (Modern Glassmorphism UI, Responsive), JavaScript, **jQuery**.
- **REST API**: Trả về dữ liệu chuẩn JSON (`{"status": "success", "data": ...}`).
- **Docker**: Docker + Docker Compose + Apache Web Server (`php:8.2-apache`).
- **KHÔNG sử dụng**: Laravel, Symfony, React, Vue, Node.js backend.

---

## III. SỬA CÁC LỖI & LOGIC NƠI CODE HIỆN TẠI
1. **Sửa lỗi Avatar Preview**:
   - Sửa logic hiển thị ảnh upload trong `public/assets/js/app.js` từ `event.target.value` thành `event.target.result` giúp xem trước ảnh đại diện sinh viên chính xác 100%.
2. **Sửa logic `updateOccupiedCount()` (`app/models/Room.php`)**:
   - Khi phòng ở trạng thái `Maintenance` (Bảo trì): **KHÔNG** tự động chuyển sang `Available` hoặc `Full`.
   - Khi phòng không ở `Maintenance`: `occupied >= capacity` $\rightarrow$ `Full`, `occupied < capacity` $\rightarrow$ `Available`.
3. **Kiểm tra Validation server-side & client-side**:
   - `capacity > 0`, `price >= 0`, `occupied >= 0` và không lớn hơn `capacity`.
   - Email hợp lệ (`Validator::validateEmail`), Số điện thoại 10-11 chữ số (`Validator::validatePhone`).
   - Kiểm tra tránh trùng lặp Mã số sinh viên (`student_code`) và Số phòng (`room_number`).
   - Ngăn chặn tuyệt đối sinh viên đăng ký/chuyển vào các phòng `Full`, `Maintenance` hoặc đã hết chỗ.
4. **Cấu hình BASE_URL**:
   - Khởi chạy mượt mà trên môi trường Docker Apache tại [http://localhost:8080](http://localhost:8080).

---

## IV. PHÂN QUYỀN NGƯỜI DÙNG THỰC SỰ (ROLE-BASED ACCESS CONTROL)
Hệ thống triển khai helper phân quyền chung trong `Controller.php` (`requireLogin()`, `requireAdmin()`, `requireStudent()`) giúp bảo mật chặt chẽ:

### 1. QUẢN TRỊ VIÊN (ADMIN)
- **Admin Dashboard**: Thống kê toàn hệ thống, tổng phòng, sức chứa, số chỗ đã ở, số hợp đồng Active, các cảnh báo khẩn.
- **CRUD Phòng**: Thêm, sửa, xóa phòng, cập nhật trạng thái bảo trì.
- **CRUD Sinh viên**: Quản lý danh sách sinh viên, upload avatar, kiểm tra trùng lặp.
- **Quản lý Hợp đồng**: Thêm, sửa, xóa, gia hạn, hủy và xem chi tiết hợp đồng KTX.
- **Quản lý Yêu cầu**: Xem danh sách yêu cầu chuyển/đăng ký phòng từ sinh viên, thực hiện **Duyệt (Approve)** hoặc **Từ chối (Reject)**.

### 2. SINH VIÊN (STUDENT)
- **Student Dashboard**: Xem thông tin phòng ở hiện tại, danh sách bạn cùng phòng, hợp đồng cá nhân, ngày hết hạn và số ngày còn lại.
- **Hồ sơ cá nhân**: Xem chi tiết thông tin cá nhân và bạn cùng phòng.
- **Bản đồ phòng & Smart Match**: Tìm phòng còn chỗ và sử dụng công cụ gợi ý phòng thông minh.
- **Hợp đồng của tôi**: Xem các hợp đồng KTX của chính mình (chặn truy cập hợp đồng sinh viên khác).
- **Yêu cầu chuyển phòng**: Gửi yêu cầu chuyển phòng mong muốn kèm lý do, theo dõi trạng thái xử lý (`Pending`, `Approved`, `Rejected`).
- **Ràng buộc**: Student KHÔNG được thêm/sửa/xóa phòng, không xóa sinh viên khác, không truy cập các chức năng Admin.

---

## V. MODULE HỢP ĐỒNG & TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI
- Controller: `app/controllers/ContractController.php`
- Views: `app/views/contracts/` (`index.php`, `create.php`, `edit.php`, `detail.php`).
- **Tự động cập nhật trạng thái**:
  - Nếu `current_date > end_date` và status đang `Active` $\rightarrow$ Tự động chuyển thành `Expired`.
  - Nếu `current_date <= end_date` $\rightarrow$ `Active`.
  - Khi Admin hủy $\rightarrow$ `Cancelled`.
- **Xem chi tiết & In hợp đồng**: Trang `contract/detail/$id` thiết kế đúng định dạng mẫu văn bản hợp đồng KTX, hỗ trợ in ấn.

---

## 🌟 VI. BA ĐIỂM SÁNG TẠO HỆ THỐNG (CREATIVE HIGHLIGHTS)

### 1. SMART ROOM RECOMMENDATION (Gợi ý phòng thông minh)
- **Đường dẫn**: `/room/smartMatch`
- **Công thức chấm điểm scoring 100 điểm**:
  - `+30 điểm`: Phòng còn chỗ trống (`occupied < capacity`).
  - `+25 điểm`: Phù hợp giới tính (Sinh viên Nam $\rightarrow$ Tòa A/B/Nam; Sinh viên Nữ $\rightarrow$ Tòa C/Nữ).
  - `+20 điểm`: Đơn giá phòng $\le$ ngân sách tối đa của sinh viên.
  - `+15 điểm`: Đúng tòa nhà sinh viên mong muốn.
  - `+10 điểm`: Đúng sức chứa / loại phòng (VIP, Máy lạnh, Thường).
- **Hiển thị**: Sắp xếp danh sách phòng theo điểm giảm dần (VD: `A101 - 95/100 điểm phù hợp`), liệt kê chi tiết lý do cộng điểm trực quan.

### 2. ROOM STATUS VISUALIZATION (Bản đồ phòng KTX)
- **Đường dẫn**: `/room/map`
- Trực quan hóa các phòng theo từng **Tòa nhà (TÒA A, TÒA B, TÒA C)**.
- Mỗi card phòng hiển thị: Số phòng, Tòa, Tầng, Loại phòng, Đơn giá, Trạng thái (`Available` - Xanh, `Full` - Đỏ, `Maintenance` - Xám) và **Progress Bar** biểu diễn % sức chứa đã sử dụng.
- Tương tác click trực tiếp vào phòng để mở Modal xem chi tiết phòng và danh sách sinh viên cư trú qua jQuery AJAX.

### 3. YÊU CẦU CHUYỂN PHÒNG (Room Request Workflow)
- Bảng CSDL: `room_requests` (`id`, `student_id`, `current_room_id`, `requested_room_id`, `request_type`, `reason`, `status`, `created_at`).
- **Sinh viên**: Chọn phòng muốn chuyển đến, nhập lý do và gửi yêu cầu.
- **Admin**: Duyệt hoặc từ chối yêu cầu.
- **Tự động hóa**: Khi Admin bấm **Duyệt**, hệ thống tự động cập nhật `room_id` mới cho sinh viên, đồng thời gọi `updateOccupiedCount()` để tính toán lại số người ở cho cả phòng cũ và phòng mới.
- Ràng buộc không cho chuyển vào phòng `Full` hoặc `Maintenance`.

---

## VII. SMART ALERT PANEL (CẢNH BÁO TRÊN DASHBOARD)
Bảng điều khiển Admin tự động hiển thị các cảnh báo thông minh:
- ⚠ **Hợp đồng sắp hết hạn trong 7 ngày** (Cảnh báo khẩn cấp màu đỏ).
- 🔔 **Yêu cầu chuyển phòng đang chờ duyệt** (Thông báo cần xử lý).
- ⚡ **Hóa đơn điện nước chưa thanh toán**.
- 👤 **Sinh viên chưa xếp phòng**.

---

## VIII. REST API JSON
Hệ thống cung cấp đầy đủ các REST API theo định dạng JSON thống nhất:
- `GET/POST/DELETE /api/rooms` - Danh sách & quản lý phòng.
- `GET/DELETE /api/students` - Danh sách & chi tiết sinh viên.
- `GET /api/contracts` - Danh sách hợp đồng.
- `GET /api/room-recommendations` - Thuật toán gợi ý phòng.
- `GET /api/room-requests` - Danh sách yêu cầu chuyển phòng.
- `GET /api/stats` - Thống kê tổng quan hệ thống.

**Cấu trúc response JSON**:
```json
{
  "status": "success",
  "data": [...]
}
```

---

## IX. HƯỚNG DẪN KHỞI CHẠY BẰNG DOCKER

Chạy lệnh duy nhất tại thư mục gốc của project:
```bash
docker compose up -d --build
```

### URL Đăng nhập hệ thống:
- 🌐 **Hệ thống Quản lý KTX**: [http://localhost:8080](http://localhost:8080)
- 🗄️ **phpMyAdmin**: [http://localhost:8081](http://localhost:8081) (Host: `db`, User: `root`, Pass: `root_password`)

---

## X. TÀI KHOẢN DEMO

| Vai trò | Username | Password | Mô tả |
| :--- | :--- | :--- | :--- |
| **Admin (Quản trị viên)** | `admin` | `password123` | Quản trị toàn quyền hệ thống |
| **Student 1 (Nam - Phòng A101)** | `sv2026001` | `password123` | Sinh viên Nguyễn Văn A |
| **Student 2 (Nữ - Phòng C301)** | `sv2026002` | `password123` | Sinh viên Trần Thị B |
| **Student 3 (Nam - Phòng B201)** | `sv2026003` | `password123` | Sinh viên Lê Hoàng C |

---

## XI. KỊCH BẢN DEMO TRƯỚC GIẢNG VIÊN (DEMO SCRIPT)

1. **Demo Phân Quyền & Dashboard Admin**:
   - Đăng nhập `admin` / `password123`.
   - Chỉ ra **Smart Alert Panel**: Thấy cảnh báo hợp đồng sắp hết hạn trong 7 ngày và 🔔 yêu cầu chuyển phòng đang chờ duyệt.
2. **Demo Điểm Sáng Tạo 2 - Bản Đồ Phòng KTX (Room Map)**:
   - Truy cập `/room/map`, giới thiệu sơ đồ phân nhóm theo Tòa A, Tòa B, Tòa C.
   - Click vào phòng `A101` hoặc `A102` để hiển thị Modal chi tiết phòng và danh sách sinh viên đang ở.
3. **Demo Điểm Sáng Tạo 1 - Gợi Ý Phòng Thông Minh (Smart Match)**:
   - Truy cập `/room/smartMatch`.
   - Chọn Giới tính: `Nam`, Ngân sách: `700.000đ`, Tòa: `Tòa A`.
   - Bấm "Phân Tích & Gợi Ý", giải thích thuật toán 100 điểm hiển thị trực quan các lý do cộng điểm.
4. **Demo Đăng Nhập Student & Điểm Sáng Tạo 3 - Yêu Cầu Chuyển Phòng**:
   - Đăng xuất Admin, đăng nhập `sv2026001` / `password123`.
   - Cho xem Student Dashboard (hiển thị phòng A101, bạn cùng phòng, hợp đồng cá nhân).
   - Truy cập `/request/create`, gửi yêu cầu xin chuyển sang phòng `B201` với lý do *"Muốn ở gần bạn cùng lớp"*.
   - Đăng xuất, đăng nhập lại `admin`. Truy cập `/request/index`, bấm **Duyệt (Approve)**.
   - Chỉ cho giảng viên thấy: Số người ở phòng `A101` tự động giảm, số người phòng `B201` tự động tăng và sinh viên `sv2026001` đã chuyển sang phòng mới thành công!
5. **Demo Module Hợp Đồng & REST API**:
   - Đăng nhập Admin, vào `/contract/index`, chọn xem hợp đồng chi tiết `#HĐ-1` (`/contract/detail/1`), bấm In hợp đồng.
   - Mở Tab trình duyệt mới test đường dẫn REST API: `http://localhost:8080/api/room-recommendations`.

---

## XII. CẤU TRÚC THƯ MỤC DỰ ÁN

```
dormitory_management/
├── docker-compose.yml          # Cấu hình Docker
├── Dockerfile                  # Cấu hình Webserver Apache + PHP 8.2
├── init.sql                    # Database Schema & Seed Data 2026
├── README.md                   # Tài liệu đồ án
├── public/                     # Document Root
│   ├── index.php               # Entry point
│   ├── assets/
│   │   ├── css/style.css       # CSS styling
│   │   └── js/app.js           # JS/jQuery logic (Avatar preview fix)
│   └── uploads/avatars/        # Ảnh đại diện sinh viên
└── app/                        # Kiến trúc MVC Backend
    ├── config/Database.php     # Config kết nối DB
    ├── core/                   # App, Controller, Database, Model, Session, Validator
    ├── models/                 # User, Student, Room, Contract, Payment, RoomRequest
    ├── controllers/            # Auth, Dashboard, Student, Room, Contract, Payment, Request, Api
    └── views/                  # layouts, auth, dashboard, rooms, students, contracts, requests
```
