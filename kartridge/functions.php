<?php
/**
 * Вспомогательные функции
 */

// Проверка авторизации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Получение текущего пользователя
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

// Проверка роли администратора
function isAdmin() {
    return isLoggedIn() && $_SESSION['user']['role'] === ROLE_ADMIN;
}

// Проверка роли ремонтника
function isMechanic() {
    return isLoggedIn() && $_SESSION['user']['role'] === ROLE_MECHANIC;
}

// Проверка роли ремонтника (специалист по ремонту)
function isRemontnik() {
    return isLoggedIn() && $_SESSION['user']['role'] === ROLE_REMONTNIK;
}

// Проверка роли заказчика
function isClient() {
    return isLoggedIn() && $_SESSION['user']['role'] === ROLE_CLIENT;
}

// Получение названия роли
function getRoleName($role) {
    $roles = [
        ROLE_ADMIN => 'Администратор',
        ROLE_MECHANIC => 'Ремонтник',
        ROLE_REMONTNIK => 'Специалист по ремонту',
        ROLE_CLIENT => 'Заказчик'
    ];
    return $roles[$role] ?? $role;
}

// Получение класса для статуса
function getStatusClass($status) {
    $classes = [
        STATUS_WAITING => 'status-waiting',
        STATUS_IN_PROGRESS => 'status-in-progress',
        STATUS_CLOSED => 'status-closed',
        STATUS_COMPLETED => 'status-completed',
        STATUS_VIEWED => 'status-viewed'
    ];
    return $classes[$status] ?? '';
}

// Форматирование даты (с учётом GMT+3)
function formatDate($date) {
    if (empty($date)) return '';
    // Предполагаем, что дата из базы данных хранится в UTC
    $dt = new DateTime($date, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Europe/Moscow'));
    return $dt->format('d.m.Y H:i');
}

// Генерация URL с учетом базового пути
function url($path = '') {
    if (empty($path)) {
        return BASE_PATH . '/';
    }
    
    // Если путь уже содержит базовый путь, возвращаем как есть
    if (strpos($path, BASE_PATH) === 0) {
        return $path;
    }
    
    // Если путь начинается с /, добавляем BASE_PATH
    if (strpos($path, '/') === 0) {
        return BASE_PATH . $path;
    }
    
    // Для относительных путей
    return BASE_PATH . '/' . $path;
}

// Перенаправление
function redirect($url) {
    // Добавляем базовый путь если URL начинается с /
    if (strpos($url, '/') === 0 && strpos($url, BASE_PATH) !== 0) {
        $url = BASE_PATH . $url;
    }
    header("Location: $url");
    exit;
}

// Получение flash-сообщений
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $message = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $message;
    }
    return null;
}

// Установка flash-сообщения
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// Рендеринг шаблона
function render($template, $data = []) {
    extract($data);
    
    $templateFile = TEMPLATE_DIR . '/' . $template . '.php';
    
    if (!file_exists($templateFile)) {
        throw new Exception("Template not found: $template");
    }
    
    ob_start();
    include $templateFile;
    $content = ob_get_clean();
    
    return $content;
}

// Рендеринг страницы с layout
function renderPage($template, $data = []) {
    $content = render($template, $data);
    
    include LAYOUT_DIR . '/layout.php';
}

// Проверка CSRF токена
function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Проверка CSRF токена
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Экспорт в CSV (работает в Excel)
function exportToCsv($data, $filename = 'export.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Добавляем BOM для корректного отображения UTF-8 в Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    foreach ($data as $row) {
        fputcsv($output, $row, ';');
    }
    
    fclose($output);
    exit;
}

// Список типов ремонта
function getRepairTypes() {
    return [
        'Заправка картриджа' => 'Заправка картриджа',
        'Замена картриджа' => 'Замена картриджа',
        'Ремонт принтера' => 'Ремонт принтера',
        'Чистка принтера' => 'Чистка принтера',
        'Замена деталей' => 'Замена деталей',
        'Диагностика' => 'Диагностика',
        'Другое' => 'Другое'
    ];
}

// Получение списка моделей принтеров из БД
function getPrinterModels() {
    $db = Database::getInstance();
    $models = $db->getPrinterModels();
    $result = ['' => 'Выберите модель'];
    foreach ($models as $model) {
        $result[$model['name']] = $model['name'];
    }
    return $result;
}

// Добавление новой модели принтера
function addPrinterModel($name) {
    $db = Database::getInstance();
    return $db->addPrinterModel($name);
}

// Получение списка корпусов из БД
function getBuildings() {
    $db = Database::getInstance();
    $buildings = $db->getBuildings();
    $result = ['' => 'Выберите корпус'];
    foreach ($buildings as $building) {
        $result[$building['name']] = $building['name'];
    }
    return $result;
}

// Получение всех статусов для фильтра
function getAllStatuses() {
    return [
        STATUS_WAITING,
        STATUS_IN_PROGRESS,
        STATUS_CLOSED,
        STATUS_COMPLETED,
        STATUS_VIEWED
    ];
}

// Получение всех статусов пожеланий
function getAllWishStatuses() {
    return [
        WISH_STATUS_ACTIVE,
        WISH_STATUS_COMPLETED
    ];
}

// Получение класса для статуса пожелания
function getWishStatusClass($status) {
    $classes = [
        WISH_STATUS_ACTIVE => 'badge bg-warning',
        WISH_STATUS_COMPLETED => 'badge bg-success'
    ];
    return $classes[$status] ?? 'badge bg-secondary';
}

// Получение всех статусов ремонта
function getAllRepairStatuses() {
    return [
        REPAIR_STATUS_WAITING,
        REPAIR_STATUS_IN_PROGRESS,
        REPAIR_STATUS_COMPLETED,
        REPAIR_STATUS_CLOSED
    ];
}

// Получение класса для статуса ремонта
function getRepairStatusClass($status) {
    $classes = [
        REPAIR_STATUS_WAITING => 'badge bg-warning',
        REPAIR_STATUS_IN_PROGRESS => 'badge bg-info',
        REPAIR_STATUS_COMPLETED => 'badge bg-success',
        REPAIR_STATUS_CLOSED => 'badge bg-secondary'
    ];
    return $classes[$status] ?? 'badge bg-secondary';
}