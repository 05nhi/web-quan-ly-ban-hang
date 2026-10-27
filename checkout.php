<?php
session_start();
include 'config/db.php';

// 1. Kiểm tra nếu giỏ hàng trống thì không cho vào trang này
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

// 2. Xử lý khi khách bấm nút "XÁC NHẬN ĐẶT HÀNG"
if (isset($_POST['btn_confirm'])) {
    $tenkhach = $_POST['txt_hoten'];
    $sodt = $_POST['txt_sodt'];
    $diachi = $_POST['txt_diachi'];
    $ghichu = $_POST['txt_ghichu'];
    $tongtien = $_POST['txt_tongtien'];
    $ngaydat = date("Y-m-d H:i:s");

    // Bước A: Lưu vào bảng DONHANG (Thông tin chung)
    $sql_dh = "INSERT INTO DONHANG (HOTEN, SODT, DIACHI, GHICHU, TONGTIEN, NGAYDAT, TRANGTHAI) 
               VALUES ('$tenkhach', '$sodt', '$diachi', '$ghichu', '$tongtien', '$ngaydat', 'Chờ xử lý')";
    
    if ($conn->query($sql_dh)) {
        // Lấy ID của đơn hàng vừa tạo để lưu chi tiết
        $id_donhang = $conn->insert_id;

        // Bước B: Lưu vào bảng CHITIETDONHANG (Từng món ăn)
        foreach ($_SESSION['cart'] as $id_sp => $soluong) {
            // Lấy giá hiện tại của sản phẩm để lưu lại (tránh trường hợp sau này đổi giá)
            $res_sp = $conn->query("SELECT GIA FROM SANPHAM WHERE MASP = $id_sp");
            $sp = $res_sp->fetch_assoc();
            $gia_luc_mua = $sp['GIA'];

            $conn->query("INSERT INTO CHITIETDONHANG (MADH, MASP, SOLUONG, GIA) 
                          VALUES ($id_donhang, $id_sp, $soluong, $gia_luc_mua)");
        }

        // Bước C: Xóa giỏ hàng sau khi đặt thành công
        unset($_SESSION['cart']);
        echo "<script>alert('Đặt hàng thành công! Chúng tôi sẽ liên hệ sớm.'); window.location.href='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <form method="POST">
        <div class="row">
            <div class="col-md-7">
                <div class="card shadow-sm border-0 p-4">
                    <h4 class="fw-bold mb-4 text-success">Thông tin giao hàng</h4>
                    <div class="mb-3">
                        <label class="form-label">Họ và tên người nhận:</label>
                        <input type="text" name="txt_hoten" class="form-control" placeholder="Nhập họ tên" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại:</label>
                        <input type="text" name="txt_sodt" class="form-control" placeholder="Nhập số điện thoại" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ nhận hàng:</label>
                        <textarea name="txt_diachi" class="form-control" rows="3" placeholder="Số nhà, tên đường, phường/xã..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (nếu có):</label>
                        <input type="text" name="txt_ghichu" class="form-control" placeholder="Ví dụ: Cay nhiều, không lấy hành...">
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm border-0 p-4">
                    <h4 class="fw-bold mb-4">Đơn hàng của bạn</h4>
                    <ul class="list-group list-group-flush mb-3">
                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $id => $qty):
                            $res = $conn->query("SELECT * FROM SANPHAM WHERE MASP = $id");
                            $row = $res->fetch_assoc();
                            $sub = $row['GIA'] * $qty;
                            $total += $sub;
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <span class="fw-bold"><?php echo $row['TENSP']; ?></span> x <?php echo $qty; ?>
                            </div>
                            <span><?php echo number_format($sub, 0, ',', '.'); ?>đ</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="d-flex justify-content-between fs-4 fw-bold text-danger mb-4">
                        <span>Tổng tiền:</span>
                        <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                    </div>

                    <input type="hidden" name="txt_tongtien" value="<?php echo $total; ?>">

                    <button type="submit" name="btn_confirm" class="btn btn-success w-100 btn-lg rounded-pill fw-bold">XÁC NHẬN ĐẶT HÀNG</button>
                    <a href="cart.php" class="btn btn-link w-100 text-secondary mt-2">Quay lại giỏ hàng</a>
                </div>
            </div>
        </div>
    </form>
</div>

</body>
</html>