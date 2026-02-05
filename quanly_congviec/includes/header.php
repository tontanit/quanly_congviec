<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Công việc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fa-solid fa-briefcase"></i> QL CÔNG VIỆC</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>modules/tasks/list.php">Công việc</a></li>

                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>modules/users/manage.php">Quản lý cán bộ</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>modules/reports/index.php">Thống kê</a></li>
                </ul>
                <div class="navbar-nav">
                    <a class="nav-link text-white" href="<?php echo BASE_URL; ?>profile.php">
                        <i class="fa-solid fa-circle-user"></i> Chào, <strong><?php echo $_SESSION['ho_ten']; ?></strong>
                    </a>
                    <a class="nav-link btn btn-danger btn-sm ms-2 text-white" href="<?php echo BASE_URL; ?>logout.php">Đăng xuất</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">