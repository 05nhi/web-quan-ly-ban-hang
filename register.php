<?php
include 'config/db.php';

$error = "";
if (isset($_POST['btnRegister'])) {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];

    // Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $check_user = "SELECT * FROM Users WHERE TENUSER = '$username'";
    $res = $conn->query($check_user);

    if ($res->num_rows > 0) {
        $error = "Tên đăng nhập này đã có người sử dụng!";
    } elseif ($password != $re_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        // Mã hóa MD5 như trong database của bạn
        $pass_md5 = md5($password);
        $sql = "INSERT INTO Users (TENUSER, MATKHAU, HOTEN, QUYEN) VALUES ('$username', '$pass_md5', '$fullname', 0)";
        
        if ($conn->query($sql)) {
            header("Location: login.php?msg=registered");
        } else {
            $error = "Lỗi đăng ký: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .register-container { max-width: 400px; margin: 80px auto; }
    </style>
</head>
<body>
<div class="container">
    <div class="register-container card shadow p-4 border-0">
        <h3 class="text-center text-success fw-bold mb-4">ĐĂNG KÝ</h3>
        
        <?php if($error != ""): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Họ và tên</label>
                <input type="text" name="fullname" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Xác nhận mật khẩu</label>
                <input type="password" name="re_password" class="form-control" required>
            </div>
            <button type="submit" name="btnRegister" class="btn btn-success w-100 py-2">Đăng Ký Ngay</button>
            <div class="text-center mt-3">
                <small>Đã có tài khoản? <a href="login.php" class="text-success">Đăng nhập</a></small>
            </div>
        </form>
    </div>
</div>
</body>
</html>