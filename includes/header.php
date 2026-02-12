<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
$hoTen = htmlspecialchars($_SESSION['ho_ten'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Công việc</title>

    <!-- Fonts & CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --primary-color: #0d6efd;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
        }

        .navbar {
            padding: 0.8rem 0;
            background: linear-gradient(135deg, #0d6efd 0%, #0046b8 100%) !important;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.25rem;
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 6px;
            transition: 0.2s;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff !important;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 12px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container">

            <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
                <i class="fa-solid fa-briefcase me-2"></i>WORK MS
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">

                <!-- LEFT MENU -->
                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage == 'index.php') ? 'active' : '' ?>"
                            href="<?= BASE_URL ?>index.php">
                            <i class="fa fa-home me-1"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage == 'list.php') ? 'active' : '' ?>"
                            href="<?= BASE_URL ?>modules/tasks/list.php">
                            <i class="fa fa-list-check me-1"></i> Công việc
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], 'schedule') !== false) ? 'active' : '' ?>"
                            href="<?= BASE_URL ?>modules/schedule/index.php">
                            <i class="fa-solid fa-calendar-days me-1"></i> Lịch công tác
                        </a>
                    </li>

                    <?php if ($role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage == 'manage.php') ? 'active' : '' ?>"
                                href="<?= BASE_URL ?>modules/users/manage.php">
                                <i class="fa fa-users-gear me-1"></i> Quản lý cán bộ
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>modules/tasks/export_excel.php">
                            <i class="fa fa-chart-line me-1"></i> Thống kê
                        </a>
                    </li>

                </ul>

                <!-- RIGHT MENU -->
                <div class="navbar-nav align-items-center">

                    <a class="nav-link text-white"
                        href="<?= BASE_URL ?>profile.php">
                        <i class="fa-solid fa-circle-user me-1"></i>
                        Chào, <strong><?= $hoTen ?></strong>
                    </a>

                    <a class="btn btn-danger btn-sm ms-2"
                        href="<?= BASE_URL ?>logout.php">
                        Đăng xuất
                    </a>

                </div>

            </div>
        </div>
    </nav>

    <div class="container mt-4">