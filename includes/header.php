<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Công việc</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --primary-color: #0d6efd;
            --header-height: 60px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
        }

        /* Tối ưu Navbar */
        .navbar {
            padding: 0.8rem 0;
            background: linear-gradient(135deg, #0d6efd 0%, #0046b8 100%) !important;
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: 1.25rem;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.2s ease;
            padding: 0.5rem 1rem !important;
            border-radius: 6px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff !important;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: #fff !important;
            font-weight: 600;
        }

        /* Badge thông báo hoặc role */
        .badge-role {
            font-size: 0.7rem;
            vertical-align: middle;
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 8px;
            border-radius: 20px;
        }

        /* Hiệu ứng mượt cho các Card trên toàn web */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 12px;
            transition: transform 0.2s ease;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
                <i class="fa-solid fa-briefcase me-2"></i>WORK MS
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>"
                            href="<?php echo BASE_URL; ?>index.php">
                            <i class="fa fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>modules/tasks/list.php">
                            <i class="fa fa-list-check me-1"></i> Công việc
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>modules/schedule/index.php">
                            <i class="fa-solid fa-calendar-days me-1"></i> Lịch công tác
                        </a>
                    </li>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>modules/users/manage.php">
                                <i class="fa fa-users-gear me-1"></i> Quản lý cán bộ
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>modules/tasks/export_excel.php">
                            <i class="fa fa-chart-line me-1"></i> Thống kê
                        </a>
                    </li>
                </ul>

                <div class="navbar-nav align-items-center">
                    <div class="nav-item dropdown">
                        <a class="nav-link text-white dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-circle-user me-1"></i>
                            <strong><?php echo $_SESSION['ho_ten'] ?? 'Cán bộ'; ?></strong>
                            <span class="badge-role ms-1 text-uppercase"><?php echo $_SESSION['role'] ?? ''; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php"><i class="fa fa-user me-2"></i>Hồ sơ cá nhân</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">