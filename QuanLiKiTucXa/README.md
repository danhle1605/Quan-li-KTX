# Hệ Thống Quản Lý Ký Túc Xá UTH

Hệ thống quản lý ký túc xá cho Trường Đại học Giao thông vận tải TP.HCM (UTH), được xây dựng bằng PHP theo kiến trúc MVC. Ứng dụng hỗ trợ quản lý sinh viên, danh mục phòng ở, hợp đồng lưu trú, hóa đơn điện nước và quy trình duyệt chuyển phòng.

---

## 1. Giới thiệu

Hệ thống cung cấp giải pháp quản lý ký túc xá tập trung cho hai phân hệ Quản trị viên (Admin) và Sinh viên (Student). Giúp tự động hóa việc theo dõi chỗ trống, quản lý hợp đồng, tính tiền dịch vụ và duyệt đơn chuyển phòng trực tuyến.

---

## 2. Chức năng chính

### Phân hệ Quản trị viên (Admin)
- **Tổng quan (Dashboard)**: Thống kê số phòng, tổng sức chứa, số sinh viên đang ở, hợp đồng còn hiệu lực và bảng cảnh báo hệ thống (hợp đồng sắp hết hạn, yêu cầu chuyển phòng chờ xử lý).
- **Quản lý phòng**: Thêm, sửa, xóa phòng, cập nhật trạng thái (`Available`, `Full`, `Maintenance`), xem danh sách sinh viên đang ở theo từng phòng.
- **Quản lý sinh viên**: Thêm mới, chỉnh sửa thông tin sinh viên, cập nhật ảnh đại diện (avatar), phân phòng trực tiếp.
- **Quản lý hợp đồng**: Lập hợp đồng mới, gia hạn, hủy hợp đồng (tự động cập nhật phòng và giải phóng chỗ ở), xem mẫu văn bản hợp đồng.
- **Duyệt chuyển phòng**: Tiếp nhận và phê duyệt (`Approved`) hoặc từ chối (`Rejected`) yêu cầu chuyển phòng của sinh viên.
- **Quản lý hóa đơn**: Lập hóa đơn tiền phòng, tiền điện, tiền nước theo tháng và cập nhật trạng thái thanh toán (`Unpaid` / `Paid`).

### Phân hệ Sinh viên (Student)
- **Hồ sơ cá nhân**: Xem thông tin cá nhân, phòng ở hiện tại và danh sách bạn cùng phòng.
- **Sơ đồ phòng (Room Map)**: Theo dõi trạng thái lấp đầy của các phòng theo từng tòa nhà và tầng.
- **Gợi ý phòng (Smart Match)**: Tìm kiếm và tính điểm gợi ý phòng phù hợp theo giới tính, ngân sách, tòa nhà và loại phòng.
- **Yêu cầu chuyển phòng**: Gửi đơn xin chuyển phòng kèm lý do và theo dõi trạng thái xử lý (`Pending`, `Approved`, `Rejected`).
- **Hợp đồng & Hóa đơn**: Xem thông tin hợp đồng và hóa đơn điện nước cá nhân.

---

## 3. Công nghệ sử dụng

- **Backend**: PHP 8.2 (Kiến trúc MVC tự dựng)
- **Database**: MySQL 8.0 (Kết nối qua PDO Prepared Statements)
- **Frontend**: HTML5, CSS3 (Vanilla CSS), JavaScript (ES6), jQuery, FontAwesome, Chart.js
- **Môi trường & Server**: Docker, Docker Compose, Apache Web Server, phpMyAdmin

---

## 4. Cấu trúc project

```text
QuanLiKiTucXa/
├── app/
│   ├── config/
│   │   └── Database.php
│   ├── controllers/
│   │   ├── ApiController.php
│   │   ├── AuthController.php
│   │   ├── ContractController.php
│   │   ├── DashboardController.php
│   │   ├── PaymentController.php
│   │   ├── RequestController.php
│   │   ├── RoomController.php
│   │   └── StudentController.php
│   ├── core/
│   │   ├── App.php
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   ├── Model.php
│   │   ├── Session.php
│   │   └── Validator.php
│   ├── models/
│   │   ├── Contract.php
│   │   ├── Payment.php
│   │   ├── Room.php
│   │   ├── RoomRequest.php
│   │   ├── Student.php
│   │   └── User.php
│   ├── services/
│   │   └── RoomTransferService.php
│   └── views/
│       ├── auth/
│       ├── contracts/
│       ├── dashboard/
│       ├── layouts/
│       ├── payments/
│       ├── requests/
│       ├── rooms/
│       └── students/
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/style.css
│   │   └── js/app.js
│   └── uploads/avatars/
├── docker-compose.yml
├── Dockerfile
├── init.sql
└── README.md
```

---

## 5. Database

Cơ sở dữ liệu: `dormitory_db` (được khởi tạo từ file `init.sql`).

Các bảng chính:
- `users`: Lưu tài khoản đăng nhập, phân quyền `admin` hoặc `student`.
- `rooms`: Lưu thông tin phòng, tòa nhà, tầng, loại phòng, sức chứa, số người ở thực tế và trạng thái phòng.
- `students`: Hồ sơ sinh viên, thông tin liên lạc, khoa, giới tính, liên kết với `users` và `rooms`.
- `contracts`: Hợp đồng lưu trú (ngày bắt đầu, ngày kết thúc, tiền cọc, trạng thái `Active`, `Expired`, `Cancelled`).
- `invoices`: Hóa đơn tiền phòng, tiền điện, tiền nước theo tháng (`Unpaid`, `Paid`).
- `room_requests`: Yêu cầu chuyển/đăng ký phòng của sinh viên (`Pending`, `Approved`, `Rejected`).

---

## 6. Cách chạy

### Yêu cầu môi trường
- Docker & Docker Compose

### Khởi chạy dịch vụ
Chạy lệnh tại thư mục gốc của dự án:

```bash
docker compose up -d --build
```

### Địa chỉ truy cập
- **Ứng dụng Web**: [http://localhost:8080](http://localhost:8080)
- **phpMyAdmin**: [http://localhost:8081](http://localhost:8081)
  - Server: `db`
  - Username: `root`
  - Password: `root_password`

### Dừng dịch vụ

```bash
docker compose down
```

---

## 7. Tài khoản demo

Mật khẩu mặc định cho tất cả các tài khoản thử nghiệm: `password123` (hoặc `123`)

| Vai trò | Username | Mật khẩu | Ghi chú |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `password123` | Quản trị viên hệ thống |
| **Sinh viên 1** | `sv2026001` | `password123` | Sinh viên Nguyễn Văn An (Nam - Phòng A101) |
| **Sinh viên 2** | `sv2026002` | `password123` | Sinh viên Trần Thị Bình (Nữ - Phòng C301) |
| **Sinh viên 3** | `sv2026003` | `password123` | Sinh viên Đỗ Minh Khang (Nam - Phòng B201) |

---

## 8. Hạn chế / Hướng phát triển

### Hạn chế hiện tại
- Trạng thái thanh toán hóa đơn điện nước do Admin cập nhật thủ công, chưa tích hợp cổng thanh toán trực tuyến.
- Thông báo hệ thống hiển thị trực tiếp trên Dashboard, chưa gửi email hoặc SMS tự động.

### Hướng phát triển
- Tích hợp thanh toán trực tuyến qua mã QR (VietQR / MoMo / VNPay).
- Tích hợp gửi email tự động (PHPMailer) thông báo hợp đồng sắp hết hạn hoặc kết quả duyệt đơn chuyển phòng.
- Mở rộng RESTful API phục vụ ứng dụng di động cho sinh viên.
- Xây dựng module quản lý tài sản và trang thiết bị gắn với từng phòng.
