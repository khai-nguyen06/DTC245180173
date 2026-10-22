# Hệ Thống Quản Lý & Chia Sẻ Công Thức Nấu Ăn (Đề 29)
- **Học phần**: Triển khai và Quản trị Hệ thống Phần mềm
- **Sinh viên thực hiện**: DTC245180173

## 1. Hướng dẫn khởi chạy hệ thống

**Yêu cầu:** Docker, Docker Compose v2 (lệnh `docker compose`), git, openssl.

```bash
# Bước 1: Lấy mã nguồn
git clone https://github.com/khai-nguyen06/DTC245180173.git
cd DTC245180173

# Bước 2: Tạo file mật khẩu (mở .env và điền mật khẩu mạnh cho các biến)
cp .env.example .env
nano .env

# Bước 3: Tạo chứng chỉ HTTPS tự ký cho Nginx
mkdir -p nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout nginx/ssl/nginx.key -out nginx/ssl/nginx.crt \
  -subj "/C=VN/ST=ThaiNguyen/L=ThaiNguyen/O=DTC/OU=IT/CN=localhost"

# Bước 4: Tạo file đăng nhập cho mysqld-exporter (điền user và mật khẩu MySQL)
cp monitoring/prometheus/.my.cnf.example monitoring/prometheus/.my.cnf
nano monitoring/prometheus/.my.cnf

# Bước 5: Khởi chạy toàn bộ hệ thống
docker compose up -d --build
```

**Kiểm tra:** chạy `docker compose ps`, tất cả container phải ở trạng thái `Up`.
Mở `https://<IP>` (bỏ qua cảnh báo chứng chỉ tự ký), rồi vào `http://<IP>:9090/targets` để thấy 4 target UP.

Nếu Promtail không thu được log, cấp quyền đọc: `sudo chmod -R a+r /var/lib/docker/containers`.

## 2. Danh mục cổng và truy cập
- **Website Recipe (HTTPS)**: https://192.168.22.128 (hoặc localhost)
- **phpMyAdmin**: http://192.168.22.128:8080 (đăng nhập bằng tài khoản MySQL, mật khẩu trong file .env)
- **Grafana**: http://192.168.22.128:3000 (tài khoản admin, mật khẩu là GRAFANA_ADMIN_PASSWORD trong .env)
- **Prometheus**: http://192.168.22.128:9090
- **Loki API**: Cổng 3100 (Chỉ truy cập nội bộ)

## 3. Các câu truy vấn LogQL mẫu
1. Tổng quan log: `{job="docker_logs"}`
2. Lọc thao tác POST: `{job="docker_logs"} |= "POST"`
3. Lọc lỗi (Regex): `{job="docker_logs"} |~ "(?i)(error|failed|warn)"`

## 4. Hardening bảo mật áp dụng
- **Cô lập mạng (Network isolation):** 4 mạng Docker riêng `frontend_net`, `backend_net`, `monitoring_net`, `logging_net`. MySQL chỉ nằm trong `backend_net` và **không mở cổng** ra máy chủ; Loki không mở cổng 3100 ra ngoài.
- **Tách thông tin nhạy cảm:** mật khẩu nằm trong file `.env` (đã gitignore), repo chỉ có `.env.example`; `.my.cnf` và `nginx.key` không được commit, chỉ có file mẫu.
- **Bảo vệ Nginx:** HTTPS (TLS 1.2/1.3) và chuyển hướng 80 sang 443; security headers (X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Referrer-Policy); `server_tokens off`; ẩn header `X-Powered-By`; `/stub_status` chỉ mở ở cổng nội bộ 8081, truy cập qua cổng 443 trả về 404.
- **Hạn chế quyền:** cAdvisor bỏ cờ `privileged: true`; các container chính bật `no-new-privileges`; user ứng dụng MySQL chỉ có quyền SELECT, INSERT, UPDATE, DELETE, CREATE trên `recipe_db` (không có DROP, ALTER, GRANT).
