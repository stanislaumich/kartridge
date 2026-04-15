<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Обслуживание картриджей'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .status-waiting { color: #ffc107; font-weight: bold; }
        .status-in-progress { color: #0d6efd; font-weight: bold; }
        .status-closed { color: #6c757d; font-weight: bold; }
        .status-completed { color: #198754; font-weight: bold; }
        .status-viewed { color: #0dcaf0; font-weight: bold; }
        
        /* Улучшение отображения таблиц */
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
            -webkit-overflow-scrolling: touch;
        }
        .table {
            margin-bottom: 0;
            min-width: 1000px; /* Минимальная ширина для 10 колонок */
        }
        .table th {
            white-space: nowrap;
            background-color: #f8f9fa;
            border-bottom-width: 2px;
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        .table td {
            vertical-align: middle;
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        /* Увеличение минимальной ширины для колонок с действиями */
        .table td:last-child {
            min-width: 220px;
            white-space: nowrap;
        }
        /* Скрытие менее важных колонок на мобильных устройствах */
        @media (max-width: 992px) {
            .table th:nth-child(4), /* Корпус */
            .table td:nth-child(4),
            .table th:nth-child(5), /* Кабинет */
            .table td:nth-child(5),
            .table th:nth-child(9), /* Изменен */
            .table td:nth-child(9) {
                display: none;
            }
            .table {
                min-width: 700px;
            }
        }
        @media (max-width: 768px) {
            .table td, .table th {
                padding: 0.5rem;
                font-size: 0.85rem;
            }
            .table td:last-child {
                min-width: 180px;
            }
            .table th:nth-child(3), /* Отдел */
            .table td:nth-child(3),
            .table th:nth-child(7), /* Тип ремонта */
            .table td:nth-child(7) {
                display: none;
            }
            .table {
                min-width: 500px;
            }
        }
        @media (max-width: 576px) {
            .table {
                min-width: 400px;
            }
        }
        
        /* Улучшение выпадающих списков статуса */
        .dropdown-menu.status-dropdown {
            max-height: 80vh;
            overflow-y: auto;
            min-width: 220px;
            max-width: 300px;
        }
        /* Для выпадающих списков в таблице - ограничиваем высоту, но убираем прокрутку если помещается */
        .table .dropdown-menu {
            max-height: 80vh;
            overflow-y: auto;
        }
        /* На маленьких экранах уменьшаем максимальную высоту */
        @media (max-height: 600px) {
            .dropdown-menu.status-dropdown,
            .table .dropdown-menu {
                max-height: 60vh;
            }
        }
        /* Улучшение кнопок в таблице */
        .table .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }
        .table .btn {
            white-space: nowrap;
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
        }
        .table .btn-group .dropdown-toggle {
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
        }
        /* Улучшение отображения выпадающего меню внизу экрана */
        .dropdown-menu.status-dropdown.dropdown-menu-end {
            right: 0;
            left: auto;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .btn {
            border-radius: 0.375rem;
        }
        .form-control, .form-select {
            border-radius: 0.375rem;
        }
        .alert {
            border-radius: 0.375rem;
        }
        footer {
            background-color: #f8f9fa;
            padding: 1rem 0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo url('/'); ?>">
                <i class="bi bi-printer"></i> Обслуживание принтеров и ремонт оборудования 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/'); ?>">Главная</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo url('/?action=users'); ?>">Пользователи</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo getCurrentUser()['full_name']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text">Роль: <?php echo getRoleName(getCurrentUser()['role']); ?></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo url('/?action=logout'); ?>">Выйти</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/?action=login'); ?>">Войти</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">
        <?php echo $content; ?>
    </main>

    <footer class="bg-light py-3 mt-auto">
        <div class="container text-center">
            <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> Обслуживание и ремонт</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($scripts)): ?>
        <?php echo $scripts; ?>
    <?php endif; ?>
</body>
</html>