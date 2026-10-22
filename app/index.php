<?php
$host = getenv('DB_HOST') ?: 'mysql_db';
$db   = getenv('DB_NAME') ?: 'recipe_db';
$user = getenv('DB_USER') ?: 'recipe_user';
$pass = getenv('DB_PASS') ?: 'RecipeSecurePass2026!';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("<h2 style='color:red;'>Kết nối Database thất bại: " . $conn->connect_error . "</h2>");
}

// Tạo bảng theo nghiệp vụ Đề 29
$conn->query("CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    ingredients TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Dữ liệu mẫu ban đầu
$check = $conn->query("SELECT COUNT(*) as count FROM recipes");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO recipes (name, category, ingredients) VALUES 
        ('Phở Bò Tái Lăn', 'Món Nước', 'Bánh phở, Thịt bò, Xương hầm, Quế, Hồi, Gừng nướng, Hành hoa'),
        ('Bún Chả Hà Nội', 'Món Nướng', 'Thịt ba chỉ, Chả viên, Bún tươi, Nước mắm chua ngọt, Đu đủ ngâm'),
        ('Nem Rán Truyền Thống', 'Món Chiên', 'Bánh đa nem, Thịt nạc vai xay, Mộc nhĩ, Miến dong, Trứng gà, Cà rốt')");
}

// Xử lý thêm công thức mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $stmt = $conn->prepare("INSERT INTO recipes (name, category, ingredients) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['name'], $_POST['category'], $_POST['ingredients']);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

$result = $conn->query("SELECT * FROM recipes ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Website Công Thức Nấu Ăn - Đề 29</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 30px; background-color: #f4f6f9; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.08); }
        h1 { color: #d35400; text-align: center; }
        .status { padding: 12px; background: #e8f8f5; color: #27ae60; border-radius: 4px; font-weight: bold; margin-bottom: 20px; }
        form { display: grid; grid-gap: 12px; margin-bottom: 30px; background: #fdfefe; border: 1px solid #dcdde1; padding: 20px; border-radius: 6px; }
        input, textarea, button { padding: 10px; border: 1px solid #dcdde1; border-radius: 4px; font-size: 14px; }
        button { background: #e67e22; color: white; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background: #d35400; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #dcdde1; padding: 12px; text-align: left; }
        th { background: #e67e22; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
    </style>
</head>
<body>
<div class="container">
    <h1>Hệ Thống Chia Sẻ Công Thức Nấu Ăn (Đề 29)</h1>
    <div class="status">✓ Trạng thái: Kết nối MySQL thành công!</div>

    <form method="POST">
        <h3>Thêm công thức món ăn mới:</h3>
        <input type="text" name="name" placeholder="Tên món ăn (Ví dụ: Cá kho tộ)" required>
        <input type="text" name="category" placeholder="Danh mục (Ví dụ: Món Kho, Món Nước, Món Chiên)" required>
        <textarea name="ingredients" rows="3" placeholder="Nguyên liệu cần chuẩn bị..." required></textarea>
        <button type="submit">Lưu công thức vào MySQL</button>
    </form>

    <h3>Danh sách món ăn:</h3>
    <table>
        <thead>
            <tr>
                <th width="8%">ID</th>
                <th width="25%">Tên món ăn</th>
                <th width="20%">Danh mục</th>
                <th>Nguyên liệu</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['ingredients']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
