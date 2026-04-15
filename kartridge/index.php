<?php
/**
 * Главный файл приложения
 * Обслуживание картриджей принтеров
 */

// Включаем буферизацию вывода для предотвращения случайного вывода перед заголовками
ob_start();

// Загрузка конфигурации
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// Инициализация сессии
session_name(SESSION_NAME);
session_start();

// Обработка действий
$action = $_GET['action'] ?? 'dashboard';
$error = null;

// Маршрутизация действий
switch ($action) {
    // Вход в систему
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Неверный токен безопасности';
            } else {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                
                $db = Database::getInstance();
                $user = $db->authenticate($username, $password);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user'] = $user;
                    setFlashMessage('Добро пожаловать, ' . $user['full_name'] . '!', 'success');
                    
                    // Перенаправление в зависимости от роли
                    if ($user['role'] === ROLE_REMONTNIK) {
                        redirect('/?action=remontnik_dashboard');
                    } else {
                        redirect('/?action=dashboard');
                    }
                } else {
                    $error = 'Неверный логин или пароль';
                }
            }
        }
        renderPage('login', ['error' => $error]);
        break;
    
    // Выход из системы
    case 'logout':
        session_destroy();
        setFlashMessage('Вы вышли из системы', 'success');
        redirect('/?action=login');
        break;
    
    // Просмотр списка заказов (дашборд)
    case 'dashboard':
        if (!isLoggedIn()) {
            redirect('/?action=login');
        }
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'department' => $_GET['department'] ?? '',
            'building' => $_GET['building'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        // Фильтры по роли
        if (isClient()) {
            $filters['user_id'] = $_SESSION['user_id'];
        }
        
        $db = Database::getInstance();
        $orders = $db->getOrders($filters);
        
        renderPage('dashboard', [
            'orders' => $orders,
            'filters' => $filters
        ]);
        break;
    
    // Дашборд для специалиста по ремонту (только заявки на ремонт)
    case 'remontnik_dashboard':
        if (!isLoggedIn() || !isRemontnik()) {
            redirect('/?action=login');
        }
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        // Для remontnik показываем все заявки (не фильтруем по user_id)
        $db = Database::getInstance();
        $repairs = $db->getRepairs($filters);
        
        renderPage('remontnik_dashboard', [
            'repairs' => $repairs,
            'filters' => $filters
        ]);
        break;
    
    // Создание нового заказа
    case 'create_order':
        if (!isLoggedIn() || !isClient()) {
            redirect('/?action=login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Неверный токен безопасности';
            } else {
                $data = [
                    'department' => $_POST['department'] ?? '',
                    'building' => $_POST['building'] ?? '',
                    'room' => $_POST['room'] ?? '',
                    'printer_model' => $_POST['printer_model'] ?: ($_POST['printer_model_hidden'] ?? ''),
                    'repair_type' => $_POST['repair_type'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                
                // Проверка обязательных полей
                if (empty($data['department']) || empty($data['building']) || 
                    empty($data['room']) || empty($data['printer_model']) || 
                    empty($data['repair_type'])) {
                    $error = 'Заполните все обязательные поля';
                } else {
                    $db = Database::getInstance();
                    $orderId = $db->createOrder($_SESSION['user_id'], $data);
                    
                    setFlashMessage('Заказ №' . $orderId . ' успешно создан', 'success');
                    redirect('/?action=dashboard');
                }
            }
        }
        
        renderPage('create_order', [
            'user' => $_SESSION['user'],
            'data' => $_POST,
            'error' => $error
        ]);
        break;
    
    // Просмотр заказа
    case 'view_order':
        if (!isLoggedIn()) {
            redirect('/?action=login');
        }
        
        $orderId = $_GET['id'] ?? 0;
        
        $db = Database::getInstance();
        $order = $db->getOrderById($orderId);
        
        if (!$order) {
            setFlashMessage('Заказ не найден', 'error');
            redirect('/?action=dashboard');
        }
        
        // Клиент может просматривать только свои заказы
        if (isClient() && $order['user_id'] != $_SESSION['user_id']) {
            setFlashMessage('Доступ запрещен', 'error');
            redirect('/?action=dashboard');
        }
        
        $history = $db->getStatusHistory($orderId);
        
        renderPage('view_order', [
            'order' => $order,
            'history' => $history
        ]);
        break;
    
    // Редактирование заказа
    case 'edit_order':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        $orderId = $_GET['id'] ?? 0;
        
        $db = Database::getInstance();
        $order = $db->getOrderById($orderId);
        
        if (!$order) {
            setFlashMessage('Заказ не найден', 'error');
            redirect('/?action=dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Неверный токен безопасности';
            } else {
                $data = [
                    'department' => $_POST['department'] ?? '',
                    'building' => $_POST['building'] ?? '',
                    'room' => $_POST['room'] ?? '',
                    'printer_model' => $_POST['printer_model'] ?: ($_POST['printer_model_hidden'] ?? ''),
                    'repair_type' => $_POST['repair_type'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                
                $newStatus = $_POST['status'] ?? '';
                
                // Проверка обязательных полей
                if (empty($data['department']) || empty($data['building']) || 
                    empty($data['room']) || empty($data['printer_model']) || 
                    empty($data['repair_type'])) {
                    $error = 'Заполните все обязательные поля';
                } else {
                    $db = Database::getInstance();
                    $db->updateOrder($orderId, $data);
                    
                    // Обновление статуса если изменен
                    if ($newStatus && $newStatus !== $order['status']) {
                        $db->updateOrderStatus($orderId, $newStatus, $_SESSION['user_id']);
                    }
                    
                    setFlashMessage('Заказ успешно обновлен', 'success');
                    redirect('/?action=view_order&id=' . $orderId);
                }
            }
        }
        
        renderPage('edit_order', [
            'order' => $order,
            'error' => $error
        ]);
        break;
    
    // Удаление заказа
    case 'delete_order':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        $orderId = $_GET['id'] ?? 0;
        
        $db = Database::getInstance();
        $db->deleteOrder($orderId);
        
        setFlashMessage('Заказ успешно удален', 'success');
        redirect('/?action=dashboard');
        break;
    
    // Обновление статуса заказа
    case 'update_status':
        if (!isLoggedIn() || (!isMechanic() && !isAdmin())) {
            redirect('/?action=login');
        }
        
        $orderId = $_GET['id'] ?? 0;
        $newStatus = $_GET['status'] ?? '';
        
        if (!in_array($newStatus, getAllStatuses())) {
            setFlashMessage('Неверный статус', 'error');
            redirect('/?action=dashboard');
        }
        
        $db = Database::getInstance();
        $db->updateOrderStatus($orderId, $newStatus, $_SESSION['user_id']);
        
        setFlashMessage('Статус заказа обновлен на "' . $newStatus . '"', 'success');
        redirect('/?action=view_order&id=' . $orderId);
        break;
    
    // Экспорт в Excel
    case 'export':
        if (!isLoggedIn() || (!isAdmin() && !isMechanic())) {
            redirect('/?action=login');
        }
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'department' => $_GET['department'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        $db = Database::getInstance();
        $data = $db->exportOrders($filters);
        
        $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';
        exportToCsv($data, $filename);
        break;
    
    // Экспорт заявок на ремонт в Excel
    case 'export_repairs':
        if (!isLoggedIn() || (!isAdmin() && !isRemontnik())) {
            redirect('/?action=login');
        }
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        // Для клиентов и ремонтников показываем только их заявки
        if (isClient()) {
            $filters['user_id'] = $_SESSION['user_id'];
        }
        
        $db = Database::getInstance();
        $data = $db->exportRepairs($filters);
        
        $filename = 'repairs_export_' . date('Y-m-d_H-i-s') . '.csv';
        exportToCsv($data, $filename);
        break;
    
    // Управление пользователями (для администратора)
    case 'users':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        $db = Database::getInstance();
        $users = $db->getAllUsers();
        
        renderPage('users', ['users' => $users]);
        break;
    
    // Создание нового пользователя
    case 'create_user':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Неверный токен безопасности', 'error');
                redirect('/?action=users');
            }
            
            $data = [
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? 'client',
                'full_name' => $_POST['full_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'department' => $_POST['department'] ?? ''
            ];
            
            // Валидация
            if (empty($data['username']) || empty($data['password']) || empty($data['full_name'])) {
                setFlashMessage('Логин, пароль и ФИО обязательны', 'error');
                redirect('/?action=create_user');
            }
            
            $db = Database::getInstance();
            
            // Проверка уникальности логина
            $existing = $db->getUserByUsername($data['username']);
            if ($existing) {
                setFlashMessage('Пользователь с таким логином уже существует', 'error');
                redirect('/?action=create_user');
            }
            
            if ($db->registerUser($data)) {
                setFlashMessage('Пользователь успешно создан', 'success');
                redirect('/?action=users');
            } else {
                setFlashMessage('Ошибка при создании пользователя', 'error');
                redirect('/?action=create_user');
            }
        }
        
        renderPage('create_user');
        break;
    
    // Редактирование пользователя
    case 'edit_user':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        $userId = $_GET['id'] ?? 0;
        if (!$userId) {
            setFlashMessage('Пользователь не указан', 'error');
            redirect('/?action=users');
        }
        
        $db = Database::getInstance();
        $user = $db->getUserById($userId);
        if (!$user) {
            setFlashMessage('Пользователь не найден', 'error');
            redirect('/?action=users');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Неверный токен безопасности', 'error');
                redirect('/?action=edit_user&id=' . $userId);
            }
            
            $data = [
                'username' => $_POST['username'] ?? '',
                'role' => $_POST['role'] ?? 'client',
                'full_name' => $_POST['full_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'department' => $_POST['department'] ?? ''
            ];
            
            // Валидация
            if (empty($data['username']) || empty($data['full_name'])) {
                setFlashMessage('Логин и ФИО обязательны', 'error');
                redirect('/?action=edit_user&id=' . $userId);
            }
            
            // Проверка уникальности логина, кроме текущего пользователя
            $existing = $db->getUserByUsername($data['username']);
            if ($existing && $existing['id'] != $userId) {
                setFlashMessage('Пользователь с таким логином уже существует', 'error');
                redirect('/?action=edit_user&id=' . $userId);
            }
            
            if ($db->updateUser($userId, $data)) {
                setFlashMessage('Данные пользователя обновлены', 'success');
                redirect('/?action=users');
            } else {
                setFlashMessage('Ошибка при обновлении данных', 'error');
                redirect('/?action=edit_user&id=' . $userId);
            }
        }
        
        renderPage('edit_user', ['user' => $user]);
        break;
    
    // Смена пароля пользователя
    case 'change_password':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/?action=users');
        }
        
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('Неверный токен безопасности', 'error');
            redirect('/?action=users');
        }
        
        $userId = $_POST['user_id'] ?? 0;
        $newPassword = $_POST['new_password'] ?? '';
        
        if (!$userId || empty($newPassword)) {
            setFlashMessage('Пользователь и новый пароль обязательны', 'error');
            redirect('/?action=users');
        }
        
        $db = Database::getInstance();
        if ($db->changePassword($userId, $newPassword)) {
            setFlashMessage('Пароль успешно изменен', 'success');
        } else {
            setFlashMessage('Ошибка при изменении пароля', 'error');
        }
        
        redirect('/?action=users');
        break;
    
    // Удаление пользователя
    case 'delete_user':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        $userId = $_GET['id'] ?? 0;
        if (!$userId) {
            setFlashMessage('Пользователь не указан', 'error');
            redirect('/?action=users');
        }
        
        // Нельзя удалить самого себя
        if ($userId == $_SESSION['user_id']) {
            setFlashMessage('Нельзя удалить самого себя', 'error');
            redirect('/?action=users');
        }
        
        $db = Database::getInstance();
        if ($db->deleteUser($userId)) {
            setFlashMessage('Пользователь удален', 'success');
        } else {
            setFlashMessage('Ошибка при удалении пользователя', 'error');
        }
        
        redirect('/?action=users');
        break;
    
    // Создание нового пожелания
    case 'create_wish':
        if (!isLoggedIn() || (!isClient() && !isMechanic())) {
            redirect('/?action=login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Неверный токен безопасности';
            } else {
                $text = trim($_POST['text'] ?? '');
                
                if (empty($text)) {
                    $error = 'Текст пожелания не может быть пустым';
                } else {
                    $db = Database::getInstance();
                    if ($db->createWish($_SESSION['user_id'], $text)) {
                        setFlashMessage('Пожелание успешно отправлено', 'success');
                        redirect('/?action=wishes');
                    } else {
                        $error = 'Ошибка при сохранении пожелания';
                    }
                }
            }
        }
        
        renderPage('create_wish', [
            'error' => $error ?? null
        ]);
        break;
    
    // Просмотр списка пожеланий
    case 'wishes':
        if (!isLoggedIn()) {
            redirect('/?action=login');
        }
        
        $filters = [];
        if (isAdmin()) {
            // Админ видит все пожелания
            if (!empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
        } else {
            // Клиент и ремонтник видят только свои
            $filters['user_id'] = $_SESSION['user_id'];
        }
        
        $db = Database::getInstance();
        $wishes = $db->getWishes($filters);
        
        renderPage('wishes', [
            'wishes' => $wishes,
            'filters' => $filters
        ]);
        break;
    
    // Обновление статуса пожелания
    case 'update_wish_status':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        $wishId = $_GET['id'] ?? 0;
        $newStatus = $_GET['status'] ?? '';
        
        if (!$wishId || !in_array($newStatus, [WISH_STATUS_ACTIVE, WISH_STATUS_COMPLETED])) {
            setFlashMessage('Неверные параметры', 'error');
            redirect('/?action=wishes');
        }
        
        $db = Database::getInstance();
        if ($db->updateWishStatus($wishId, $newStatus)) {
            setFlashMessage('Статус пожелания обновлен', 'success');
        } else {
            setFlashMessage('Ошибка при обновлении статуса', 'error');
        }
        
        redirect('/?action=wishes');
        break;
    
    // Удаление пожелания
    case 'delete_wish':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        $wishId = $_GET['id'] ?? 0;
        if (!$wishId) {
            setFlashMessage('Пожелание не указано', 'error');
            redirect('/?action=wishes');
        }
        
        $db = Database::getInstance();
        if ($db->deleteWish($wishId)) {
            setFlashMessage('Пожелание удалено', 'success');
        } else {
            setFlashMessage('Ошибка при удалении пожелания', 'error');
        }
        
        redirect('/?action=wishes');
        break;
    
    // Создание новой заявки на ремонт
    case 'create_repair':
        if (!isLoggedIn() || !isClient()) {
            redirect('/?action=login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Неверный токен безопасности';
            } else {
                $data = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'building' => trim($_POST['building'] ?? ''),
                    'room' => trim($_POST['room'] ?? '')
                ];
                
                if (empty($data['title']) || empty($data['building']) || empty($data['room'])) {
                    $error = 'Заполните обязательные поля: название, корпус и кабинет';
                } else {
                    $db = Database::getInstance();
                    if ($db->createRepair($_SESSION['user_id'], $data)) {
                        setFlashMessage('Заявка на ремонт успешно создана', 'success');
                        redirect('/?action=repairs');
                    } else {
                        $error = 'Ошибка при сохранении заявки';
                    }
                }
            }
        }
        
        renderPage('create_repair', [
            'error' => $error ?? null
        ]);
        break;
    
    // Просмотр списка заявок на ремонт
    case 'repairs':
        if (!isLoggedIn() || isMechanic()) {
            // Заправщик картриджей не имеет доступа к ремонтам
            redirect('/?action=login');
        }
        
        $filters = [];
        if (isClient()) {
            // Клиент видит только свои заявки
            $filters['user_id'] = $_SESSION['user_id'];
        }
        // Для ремонтника и администратора фильтр по статусу (если передан)
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        
        $db = Database::getInstance();
        $repairs = $db->getRepairs($filters);
        
        renderPage('repairs', [
            'repairs' => $repairs,
            'filters' => $filters
        ]);
        break;
    
    // Редактирование заявки на ремонт
    case 'edit_repair':
        if (!isLoggedIn() || (!isRemontnik() && !isAdmin())) {
            redirect('/?action=login');
        }
        
        $repairId = $_GET['id'] ?? 0;
        $db = Database::getInstance();
        $repair = $db->getRepairById($repairId);
        
        if (!$repair) {
            setFlashMessage('Заявка не найдена', 'error');
            redirect('/?action=repairs');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Неверный токен безопасности';
            } else {
                $data = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'status' => $_POST['status'] ?? ''
                ];
                
                if (empty($data['title']) || empty($data['description'])) {
                    $error = 'Заполните все обязательные поля';
                } else {
                    if ($db->updateRepair($repairId, $data)) {
                        setFlashMessage('Заявка успешно обновлена', 'success');
                        redirect('/?action=repairs');
                    } else {
                        $error = 'Ошибка при обновлении заявки';
                    }
                }
            }
        }
        
        renderPage('edit_repair', [
            'repair' => $repair,
            'error' => $error ?? null
        ]);
        break;
    
    // Обновление статуса заявки на ремонт
    case 'update_repair_status':
        if (!isLoggedIn() || (!isRemontnik() && !isAdmin())) {
            redirect('/?action=login');
        }
        
        $repairId = $_GET['id'] ?? 0;
        $newStatus = $_GET['status'] ?? '';
        
        $db = Database::getInstance();
        $statuses = $db->getRepairStatuses();
        
        if (!$repairId || !in_array($newStatus, $statuses)) {
            setFlashMessage('Неверные параметры', 'error');
            redirect('/?action=repairs');
        }
        
        if ($db->updateRepairStatus($repairId, $newStatus)) {
            setFlashMessage('Статус заявки обновлен', 'success');
        } else {
            setFlashMessage('Ошибка при обновлении статуса', 'error');
        }
        
        redirect('/?action=repairs');
        break;
    
    // Удаление заявки на ремонт
    case 'delete_repair':
        if (!isLoggedIn() || !isAdmin()) {
            redirect('/?action=login');
        }
        
        $repairId = $_GET['id'] ?? 0;
        if (!$repairId) {
            setFlashMessage('Заявка не указана', 'error');
            redirect('/?action=repairs');
        }
        
        $db = Database::getInstance();
        if ($db->deleteRepair($repairId)) {
            setFlashMessage('Заявка удалена', 'success');
        } else {
            setFlashMessage('Ошибка при удалении заявки', 'error');
        }
        
        redirect('/?action=repairs');
        break;
    
    // По умолчанию - редирект на дашборд или логин
    default:
        if (isLoggedIn()) {
            // Перенаправление в зависимости от роли
            if (isRemontnik()) {
                redirect('/?action=remontnik_dashboard');
            } else {
                redirect('/?action=dashboard');
            }
        } else {
            redirect('/?action=login');
        }
        break;
}

// AJAX: Добавление новой модели принтера
if ($action === 'add_printer_model') {
    // Очищаем буфер вывода, чтобы гарантировать чистый JSON ответ
    ob_clean();
    header('Content-Type: application/json');
    
    if (!isLoggedIn() || (!isClient() && !isAdmin())) {
        echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
        exit;
    }
    
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Неверный токен безопасности']);
        exit;
    }
    
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Название модели не может быть пустым']);
        exit;
    }
    
    $db = Database::getInstance();
    
    if ($db->printerModelExists($name)) {
        echo json_encode(['success' => true, 'name' => $name, 'message' => 'Модель уже существует']);
        exit;
    }
    
    if ($db->addPrinterModel($name)) {
        echo json_encode(['success' => true, 'name' => $name]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении модели']);
    }
    exit;
}
