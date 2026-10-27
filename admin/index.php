<?php
	session_start();
	include '../config/db.php'; 

	// 1. Kiểm tra quyền Admin
	if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
		header("Location: ../login.php");
		exit();
	}

	// 2. XỬ LÝ THÊM SẢN PHẨM
	if (isset($_POST['btnLuuSP'])) {
		$tensp = mysqli_real_escape_string($conn, $_POST['tensp']);
		$gia = $_POST['gia'];
		$gia_von = $_POST['gia_von']; 
		$soluong = $_POST['soluong'];
		$maloai = $_POST['maloai'];
		$mota = mysqli_real_escape_string($conn, $_POST['mota']);
		
		$hinhanh = $_FILES['hinhanh']['name'];
		$target = "../assets/images/" . basename($hinhanh);
		
		if (move_uploaded_file($_FILES['hinhanh']['tmp_name'], $target)) {
			$sql_them = "INSERT INTO SANPHAM (TENSP, GIA, GIA_VON, SOLUONG, IMAGE, MOTA, MALOAI) VALUES ('$tensp', '$gia', '$gia_von', '$soluong', '$hinhanh', '$mota', '$maloai')";
			if ($conn->query($sql_them)) {
				header("Location: index.php?msg=success");
				exit();
			}
		}
	}

	// 3. XỬ LÝ CẬP NHẬT SẢN PHẨM
	if (isset($_POST['btnCapNhatSP'])) {
		$masp = $_POST['masp'];
		$tensp = mysqli_real_escape_string($conn, $_POST['tensp']);
		$gia = $_POST['gia'];
		$gia_von = $_POST['gia_von']; 
		$soluong = $_POST['soluong'];
		$maloai = $_POST['maloai'];
		$mota = mysqli_real_escape_string($conn, $_POST['mota']);
		
		if (!empty($_FILES['hinhanh']['name'])) {
			$hinhanh = $_FILES['hinhanh']['name'];
			move_uploaded_file($_FILES['hinhanh']['tmp_name'], "../assets/images/" . $hinhanh);
			
			$sql_update = "UPDATE SANPHAM SET TENSP='$tensp', GIA='$gia', GIA_VON='$gia_von', SOLUONG='$soluong', IMAGE='$hinhanh', MOTA='$mota', MALOAI='$maloai' WHERE MASP=$masp";
		} else {
			$sql_update = "UPDATE SANPHAM SET TENSP='$tensp', GIA='$gia', GIA_VON='$gia_von', SOLUONG='$soluong', MOTA='$mota', MALOAI='$maloai' WHERE MASP=$masp";
		}

		if ($conn->query($sql_update)) {
			header("Location: index.php?msg=updated");
			exit();
		}
	}

	// 4. XỬ LÝ XÓA
	if (isset($_GET['delete_id'])) {
		$id_xoa = $_GET['delete_id'];
		$sql_xoa = "DELETE FROM SANPHAM WHERE MASP = $id_xoa";
		if ($conn->query($sql_xoa)) {
			header("Location: index.php?msg=deleted");
			exit();
		}
	}

	// 5. LẤY DỮ LIỆU ĐỂ HIỂN THỊ
	$sql = "SELECT sp.*, loai.TENLOAI FROM SANPHAM sp JOIN LOAISP loai ON sp.MALOAI = loai.MALOAI ORDER BY sp.MASP DESC";
	$result = $conn->query($sql);

	$edit_sp = null;
	if (isset($_GET['edit_id'])) {
		$id_edit = $_GET['edit_id'];
		$edit_sp = $conn->query("SELECT * FROM SANPHAM WHERE MASP = $id_edit")->fetch_assoc();
	}

	$list_loai = $conn->query("SELECT * FROM LOAISP");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản trị - Sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
	<style>
        .form-card {  
			border-left: 5px solid #198754; 
			background-color: #e9f7ef;
		}
		.form-card-edit { 
			border-left: 5px solid #ffc107; 
			background-color: #fff8e1;
		}
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark">Quản lý sản phẩm</h3>
                <?php if(!isset($_GET['show_form']) && !isset($_GET['edit_id'])): ?>
                    <a href="index.php?show_form=1" class="btn btn-success shadow-sm"><i class="bi bi-plus-circle"></i> Thêm món mới</a>
                <?php endif; ?>
            </div>

            <?php if(isset($_GET['show_form']) || isset($_GET['edit_id'])): 
                $is_edit = isset($_GET['edit_id']);
                $title = $is_edit ? "Chỉnh sửa sản phẩm: " . $edit_sp['TENSP'] : "Thêm món ăn mới";
                $btn_name = $is_edit ? "btnCapNhatSP" : "btnLuuSP";
            ?>
            <div class="card shadow-sm mb-4 border-0 <?php echo $is_edit ? 'form-card-edit' : 'form-card'; ?>">
                <div class="card-body">
                    <h5 class="card-title <?php echo $is_edit ? 'text-warning' : 'text-success'; ?> mb-4 fw-bold">
                        <i class="bi <?php echo $is_edit ? 'bi-pencil-square' : 'bi-plus-circle'; ?>"></i> <?php echo $title; ?>
                    </h5>
                    <form method="POST" enctype="multipart/form-data">
                        <?php if($is_edit): ?>
                            <input type="hidden" name="masp" value="<?php echo $edit_sp['MASP']; ?>">
                        <?php endif; ?>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tên món ăn</label>
                                <input type="text" name="tensp" class="form-control" value="<?php echo $is_edit ? $edit_sp['TENSP'] : ''; ?>" required placeholder="Nhập tên món...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Danh mục</label>
                                <select name="maloai" class="form-select" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php 
                                    $list_loai->data_seek(0);
                                    while($l = $list_loai->fetch_assoc()): ?>
                                        <option value="<?php echo $l['MALOAI']; ?>" <?php echo ($is_edit && $edit_sp['MALOAI'] == $l['MALOAI']) ? 'selected' : ''; ?>>
                                            <?php echo $l['TENLOAI']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-danger">Giá bán (VNĐ)</label>
                                <input type="number" name="gia" class="form-control border-danger" value="<?php echo $is_edit ? $edit_sp['GIA'] : ''; ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-primary">Giá vốn (VNĐ)</label>
                                <input type="number" name="gia_von" class="form-control border-primary" value="<?php echo $is_edit ? $edit_sp['GIA_VON'] : '0'; ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-success">Số lượng</label>
                                <input type="number" name="soluong" class="form-control border-success" value="<?php echo $is_edit ? $edit_sp['SOLUONG'] : '10'; ?>" required min="0">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Hình ảnh</label>
                                <input type="file" name="hinhanh" class="form-control" <?php echo $is_edit ? '' : 'required'; ?>>
                                <?php if($is_edit): ?>
                                    <div class="mt-2 small text-muted">
                                        Ảnh hiện tại: <span class="badge bg-secondary"><?php echo $edit_sp['IMAGE']; ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Mô tả món ăn</label>
                                <textarea name="mota" class="form-control" rows="2" placeholder="Nhập mô tả chi tiết..."><?php echo $is_edit ? $edit_sp['MOTA'] : ''; ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="index.php" class="btn btn-light border px-4">Hủy bỏ</a>
                            <button type="submit" name="<?php echo $btn_name; ?>" class="btn <?php echo $is_edit ? 'btn-warning' : 'btn-success'; ?> px-5">
                                <i class="bi bi-check-lg"></i> <?php echo $is_edit ? 'Cập nhật' : 'Lưu sản phẩm'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <?php 
                        if($_GET['msg'] == 'success') echo "Thêm món mới thành công!";
                        if($_GET['msg'] == 'updated') echo "Đã cập nhật thông tin sản phẩm!";
                        if($_GET['msg'] == 'deleted') echo "Đã xóa sản phẩm khỏi hệ thống!";
                    ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">STT</th> <!-- Đổi tiêu đề ID thành STT -->
                                <th>Hình ảnh</th>
                                <th>Tên món</th>
                                <th>Danh mục</th>
                                <th class="text-primary">Giá vốn</th>
                                <th class="text-danger">Giá bán</th>
                                <th class="text-center">Số lượng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $stt = 1; // Khởi tạo biến đếm
                            while($row = $result->fetch_assoc()): 
                            ?>
                            <tr class="<?php echo (isset($_GET['edit_id']) && $_GET['edit_id'] == $row['MASP']) ? 'table-warning' : ''; ?>">
                                <td class="ps-3"><?php echo $stt++; ?></td> <!-- In STT tự tăng -->
                                <td><img src="../assets/images/<?php echo $row['IMAGE']; ?>" width="55" height="55" class="rounded shadow-sm" style="object-fit: cover;"></td>
                                <td class="fw-bold text-dark"><?php echo $row['TENSP']; ?></td>
                                <td><?php echo $row['TENLOAI']; ?></td>
                                <td class="text-primary fw-bold"><?php echo number_format($row['GIA_VON'], 0, ',', '.'); ?>đ</td>
                                <td class="text-danger fw-bold"><?php echo number_format($row['GIA'], 0, ',', '.'); ?>đ</td>
                                
                                <td class="text-center">
                                    <?php if($row['SOLUONG'] <= 0): ?>
                                        <span class="badge bg-danger">Hết hàng</span>
                                    <?php elseif($row['SOLUONG'] <= 5): ?>
                                        <span class="badge bg-warning text-dark"><?php echo $row['SOLUONG']; ?> (Sắp hết)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo $row['SOLUONG']; ?></span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <!-- Giữ nguyên các nút bấm gốc của bạn -->
                                    <a href="index.php?edit_id=<?php echo $row['MASP']; ?>" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <a href="index.php?delete_id=<?php echo $row['MASP']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa món này?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>