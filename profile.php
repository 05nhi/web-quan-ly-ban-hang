<?php
session_start();
include 'config/db.php'; 

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$message = "";
$error = "";

// 2. Xử lý khi người dùng nhấn nút "Lưu thay đổi"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_hoten = $_POST['hoten'];
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    // Lấy mật khẩu hiện tại từ DB để đối soát
    $sql_check = "SELECT MATKHAU FROM Users WHERE TENUSER = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $current_user = $res_check->fetch_assoc();

    // Kiểm tra nếu người dùng điền vào các ô mật khẩu
    if (!empty($old_pass) || !empty($new_pass) || !empty($confirm_pass)) {
        // Kiểm tra mật khẩu cũ có khớp không
        if ($old_pass !== $current_user['MATKHAU']) {
            $error = "Mật khẩu cũ không chính xác!";
        } 
        // Kiểm tra mật khẩu mới và xác nhận
        elseif (empty($new_pass)) {
            $error = "Vui lòng nhập mật khẩu mới!";
        }
        elseif ($new_pass !== $confirm_pass) {
            $error = "Mật khẩu xác nhận không khớp!";
        } 
        else {
            // Cập nhật cả họ tên và mật khẩu mới
            $sql_update = "UPDATE Users SET HOTEN = ?, MATKHAU = ? WHERE TENUSER = ?";
            $stmt_up = $conn->prepare($sql_update);
            $stmt_up->bind_param("sss", $new_hoten, $new_pass, $username);
            if ($stmt_up->execute()) {
                $message = "Cập nhật mật khẩu thành công!";
            } else {
                $error = "Có lỗi hệ thống xảy ra.";
            }
        }
    } else {
        // Chỉ cập nhật họ tên nếu các ô mật khẩu để trống
        $sql_update = "UPDATE Users SET HOTEN = ? WHERE TENUSER = ?";
        $stmt_up = $conn->prepare($sql_update);
        $stmt_up->bind_param("ss", $new_hoten, $username);
        if ($stmt_up->execute()) {
            $message = "Cập nhật họ tên thành công!";
        } else {
            $error = "Có lỗi xảy ra khi cập nhật.";
        }
    }
}

// 3. Lấy thông tin hiển thị
$sql_user = "SELECT * FROM Users WHERE TENUSER = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("s", $username);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Lấy lịch sử đơn hàng
$maUser = $user_data['MAUSER'];
$sql_orders = "SELECT * FROM HOADON WHERE MAUSER = ? ORDER BY MAHOADON DESC";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $maUser);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ & Bảo mật</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main-color: #00b14f; --bg-gray: #f4f7f6; }
        body { background-color: var(--bg-gray); font-family: 'Inter', sans-serif; }
        .profile-header { background: #00b14f; height: 120px; border-radius: 0 0 20px 20px; }
        .profile-card { background: white; border-radius: 15px; margin-top: -50px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: none; }
        .nav-link { color: #555; font-weight: 500; border-bottom: 2px solid transparent; }
        .nav-link.active { color: var(--main-color) !important; background: transparent !important; border-bottom: 2px solid var(--main-color); border-radius: 0; }
        .form-label { color: #666; }
        .order-item { border-left: 4px solid var(--main-color); background: #fff; margin-bottom: 10px; border-radius: 8px; }
    </style>
</head>
<body>

<div class="profile-header"></div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card profile-card p-4">
                
                <?php if ($message): ?>
                    <div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-2"></i><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <?php echo strtoupper(substr($user_data['HOTEN'], 0, 1)); ?>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($user_data['HOTEN']); ?></h5>
                        <small class="text-muted">Thành viên GrabFood</small>
                    </div>
                </div>

                <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                    <li class="nav-item flex-fill">
                        <button class="nav-link active w-100" data-bs-toggle="pill" data-bs-target="#tab-info">Thông tin</button>
                    </li>
                    <li class="nav-item flex-fill">
                        <button class="nav-link w-100" data-bs-toggle="pill" data-bs-target="#tab-orders">Đơn hàng</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-info">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Họ và tên</label>
                                <input type="text" name="hoten" class="form-control" value="<?php echo htmlspecialchars($user_data['HOTEN']); ?>" required>
                            </div>

                            <div class="bg-light p-3 rounded-3 mb-4">
                                <h6 class="fw-bold mb-3 small text-uppercase">Đổi mật khẩu</h6>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Mật khẩu hiện tại</label>
                                    <input type="password" name="old_password" class="form-control form-control-sm" placeholder="Xác nhận mật khẩu cũ">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Mật khẩu mới</label>
                                    <input type="password" name="new_password" class="form-control form-control-sm" placeholder="Tối thiểu 6 ký tự">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small">Xác nhận mật khẩu mới</label>
                                    <input type="password" name="confirm_password" class="form-control form-control-sm">
                                </div>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-success w-100 fw-bold py-2">CẬP NHẬT HỒ SƠ</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="tab-orders">
                        <?php if ($result_orders->num_rows > 0): ?>
                            <?php while($order = $result_orders->fetch_assoc()): ?>
                                <div class="order-item p-3 shadow-sm border">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Mã đơn: <?php echo $order['MAHOADON']; ?></span>
                                        <span class="text-success fw-bold"><?php echo number_format($order['TONGTIEN'], 0, ',', '.'); ?>đ</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($order['NGAY'])); ?></small>
                                        <span class="badge bg-light text-dark border"><?php echo $order['TRANGTHAI']; ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center text-muted py-4">Bạn chưa có đơn hàng nào.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Quay lại</a>
                    <a href="logout.php" class="text-decoration-none text-danger small fw-bold">Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>