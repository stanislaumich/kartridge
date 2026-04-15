<?php
/**
 * Класс для работы с базой данных SQLite
 */

class Database {
    private $db;
    private static $instance = null;
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            $this->db = new PDO('sqlite:' . DB_PATH);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Ошибка подключения к базе данных: ' . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->db;
    }

    /**
     * Конвертация даты из локального времени (Europe/Moscow) в UTC
     * @param string $localDateTime Дата и время в формате 'Y-m-d H:i:s'
     * @return string Дата и время в UTC в формате 'Y-m-d H:i:s'
     */
    private function convertLocalDateToUTC($localDateTime) {
        try {
            $local = new DateTime($localDateTime, new DateTimeZone('Europe/Moscow'));
            $local->setTimezone(new DateTimeZone('UTC'));
            return $local->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            // В случае ошибки возвращаем исходную дату
            return $localDateTime;
        }
    }

    /**
     * Инициализация таблиц базы данных
     */
    public function initTables() {
        // Таблица пользователей
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL,
                full_name TEXT,
                email TEXT,
                department TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Таблица заказов
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                department TEXT NOT NULL,
                building TEXT NOT NULL,
                room TEXT NOT NULL,
                printer_model TEXT NOT NULL,
                repair_type TEXT NOT NULL,
                description TEXT,
                status TEXT DEFAULT 'ОЖИДАЕТ',
                status_changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // Таблица истории статусов
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS status_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                status TEXT NOT NULL,
                changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                changed_by INTEGER,
                FOREIGN KEY (order_id) REFERENCES orders(id),
                FOREIGN KEY (changed_by) REFERENCES users(id)
            )
        ");
        
        // Создаем таблицу корпусов
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS buildings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Создаем таблицу моделей принтеров
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS printer_models (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Создаем таблицу пожеланий (wishes)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS wishes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                text TEXT NOT NULL,
                status TEXT DEFAULT 'активно',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // Создаем таблицу заявок на ремонт (repairs)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS repairs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                description TEXT NOT NULL,
                building TEXT NOT NULL DEFAULT '',
                room TEXT NOT NULL DEFAULT '',
                status TEXT DEFAULT 'ОЖИДАЕТ',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // Добавляем столбцы building и room, если они не существуют (для существующих таблиц)
        try {
            $this->db->exec("ALTER TABLE repairs ADD COLUMN building TEXT NOT NULL DEFAULT ''");
        } catch (PDOException $e) {
            // Столбец уже существует, игнорируем ошибку
        }
        
        try {
            $this->db->exec("ALTER TABLE repairs ADD COLUMN room TEXT NOT NULL DEFAULT ''");
        } catch (PDOException $e) {
            // Столбец уже существует, игнорируем ошибку
        }
        
        // Создаем административного пользователя по умолчанию
        $this->createDefaultUsers();
        
        // Заполняем справочник моделей принтеров по умолчанию
        $this->seedPrinterModels();
        
        // Заполняем справочник корпусов по умолчанию
        $this->seedBuildings();
    }
    
    /**
     * Заполнение справочника корпусов
     */
    private function seedBuildings() {
        $defaultBuildings = [
            'Главный корпус',
            'Корпус А',
            'Корпус Б',
            'Корпус В',
            'Лабораторный корпус',
            'Другой'
        ];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM buildings");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $insert = $this->db->prepare("INSERT INTO buildings (name) VALUES (?)");
            foreach ($defaultBuildings as $building) {
                $insert->execute([$building]);
            }
        }
    }
    
    /**
     * Получение списка корпусов
     */
    public function getBuildings() {
        $stmt = $this->db->query("SELECT * FROM buildings ORDER BY name");
        return $stmt->fetchAll();
    }
    
    /**
     * Заполнение справочника моделей принтеров
     */
    private function seedPrinterModels() {
        $defaultModels = [
            'HP LaserJet',
            'HP DeskJet',
            'Canon Pixma',
            'Epson L',
            'Brother',
            'Samsung',
            'Xerox',
            'Lexmark',
            'Другая'
        ];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM printer_models");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $insert = $this->db->prepare("INSERT INTO printer_models (name) VALUES (?)");
            foreach ($defaultModels as $model) {
                $insert->execute([$model]);
            }
        }
    }
    
    /**
     * Получение списка моделей принтеров
     */
    public function getPrinterModels() {
        $stmt = $this->db->query("SELECT * FROM printer_models ORDER BY name");
        return $stmt->fetchAll();
    }
    
    /**
     * Проверка существования модели принтера
     */
    public function printerModelExists($name) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM printer_models WHERE name = ?");
        $stmt->execute([trim($name)]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Добавление новой модели принтера
     */
    public function addPrinterModel($name) {
        $name = trim($name);
        if (empty($name)) return false;
        
        try {
            $stmt = $this->db->prepare("INSERT INTO printer_models (name) VALUES (?)");
            $stmt->execute([$name]);
            return true;
        } catch (PDOException $e) {
            // Если модель уже существует, возвращаем true
            // SQLite возвращает код 19 для нарушения ограничения UNIQUE
            // Некоторые драйверы могут возвращать 23000 (SQLSTATE)
            $errorCode = $e->getCode();
            if ($errorCode == 23000 || $errorCode == 19) {
                return true;
            }
            // Логируем ошибку для отладки
            error_log("Ошибка при добавлении модели принтера: " . $e->getMessage() . " (код: $errorCode)");
            return false;
        }
    }
    
    /**
     * Создание пользователей по умолчанию
     */
    private function createDefaultUsers() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // Администратор
            $stmt = $this->db->prepare("
                INSERT INTO users (username, password, role, full_name, email) 
                VALUES ('admin', ?, 'admin', 'Администратор', 'admin@localhost')
            ");
            $stmt->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
            
            // Ремонтник
            $stmt = $this->db->prepare("
                INSERT INTO users (username, password, role, full_name, email)
                VALUES ('mechanic', ?, 'mechanic', 'Ремонтник', 'mechanic@localhost')
            ");
            $stmt->execute([password_hash('mechanic123', PASSWORD_DEFAULT)]);
            
            // Специалист по ремонту (remontnik)
            $stmt = $this->db->prepare("
                INSERT INTO users (username, password, role, full_name, email)
                VALUES ('remontnik', ?, 'remontnik', 'Специалист по ремонту', 'remontnik@localhost')
            ");
            $stmt->execute([password_hash('remontnik123', PASSWORD_DEFAULT)]);
            
            // Заказчик
            $stmt = $this->db->prepare("
                INSERT INTO users (username, password, role, full_name, email, department)
                VALUES ('client', ?, 'client', 'Заказчик', 'client@localhost', 'Отдел заказчика')
            ");
            $stmt->execute([password_hash('client123', PASSWORD_DEFAULT)]);
        }
    }
    
    /**
     * Получение пользователя по имени
     */
    public function getUserByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    /**
     * Получение пользователя по ID
     */
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Аутентификация пользователя
     */
    public function authenticate($username, $password) {
        $user = $this->getUserByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
    /**
     * Создание нового заказа
     */
    public function createOrder($userId, $data) {
        // Автоматически добавляем модель принтера, если её нет в справочнике
        $printerModel = trim($data['printer_model']);
        if (!empty($printerModel) && !$this->printerModelExists($printerModel)) {
            $this->addPrinterModel($printerModel);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO orders (user_id, department, building, room, printer_model, repair_type, description, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $data['department'],
            $data['building'],
            $data['room'],
            $printerModel,
            $data['repair_type'],
            $data['description'],
            STATUS_WAITING
        ]);
        
        $orderId = $this->db->lastInsertId();
        
        // Добавляем запись в историю
        $this->addStatusHistory($orderId, STATUS_WAITING, $userId);
        
        return $orderId;
    }
    
    /**
     * Получение списка заказов
     */
    public function getOrders($filters = []) {
        $sql = "SELECT o.*, u.username, u.full_name
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND o.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['department'])) {
            $sql .= " AND o.department LIKE ?";
            $params[] = '%' . $filters['department'] . '%';
        }
        
        if (!empty($filters['building'])) {
            $sql .= " AND o.building = ?";
            $params[] = $filters['building'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND o.created_at >= ?";
            // Convert from Moscow time to UTC for comparison
            $params[] = $this->convertLocalDateToUTC($filters['date_from'] . ' 00:00:00');
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND o.created_at <= ?";
            // Convert from Moscow time to UTC for comparison (end of day)
            $params[] = $this->convertLocalDateToUTC($filters['date_to'] . ' 23:59:59');
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Получение заказа по ID
     */
    public function getOrderById($id) {
        $stmt = $this->db->prepare("
            SELECT o.*, u.username, u.full_name 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Обновление статуса заказа
     */
    public function updateOrderStatus($orderId, $newStatus, $userId) {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET status = ?, status_changed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$newStatus, $orderId]);
        
        // Добавляем запись в историю
        $this->addStatusHistory($orderId, $newStatus, $userId);
    }
    
    /**
     * Обновление заказа
     */
    public function updateOrder($orderId, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['department', 'building', 'room', 'printer_model', 'repair_type', 'description'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) return false;
        
        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $orderId;
        
        $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
    
    /**
     * Удаление заказа
     */
    public function deleteOrder($orderId) {
        // Удаляем историю
        $stmt = $this->db->prepare("DELETE FROM status_history WHERE order_id = ?");
        $stmt->execute([$orderId]);
        
        // Удаляем заказ
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$orderId]);
    }
    
    /**
     * Добавление записи в историю статусов
     */
    private function addStatusHistory($orderId, $status, $userId) {
        $stmt = $this->db->prepare("
            INSERT INTO status_history (order_id, status, changed_by)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$orderId, $status, $userId]);
    }
    
    /**
     * Получение истории статусов заказа
     */
    public function getStatusHistory($orderId) {
        $stmt = $this->db->prepare("
            SELECT sh.*, u.username, u.full_name 
            FROM status_history sh
            LEFT JOIN users u ON sh.changed_by = u.id
            WHERE sh.order_id = ?
            ORDER BY sh.changed_at ASC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Регистрация нового пользователя
     */
    public function registerUser($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, password, role, full_name, email, department)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'],
            $data['full_name'],
            $data['email'],
            $data['department'] ?? ''
        ]);
    }
    
    /**
     * Получение всех пользователей
     */
    public function getAllUsers() {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Обновление данных пользователя
     */
    public function updateUser($userId, $data) {
        $allowedFields = ['username', 'full_name', 'email', 'department', 'role'];
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Смена пароля пользователя
     */
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }
    
    /**
     * Удаление пользователя
     */
    public function deleteUser($userId) {
        // Нельзя удалить самого себя (проверка должна быть на уровне контроллера)
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }
    
    /**
     * Получение списка отделов
     */
    public function getDepartments() {
        $stmt = $this->db->query("SELECT DISTINCT department FROM users WHERE department != '' ORDER BY department");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Экспорт заказов в Excel
     */
    public function exportOrders($filters = []) {
        $orders = $this->getOrders($filters);
        
        $data = [];
        $data[] = ['ID', 'Дата создания', 'Отдел', 'Корпус', 'Кабинет', 'Модель принтера', 'Тип ремонта', 'Описание', 'Статус', 'Дата изменения статуса', 'Создал'];
        
        foreach ($orders as $order) {
            $data[] = [
                $order['id'],
                $order['created_at'],
                $order['department'],
                $order['building'],
                $order['room'],
                $order['printer_model'],
                $order['repair_type'],
                $order['description'],
                $order['status'],
                $order['status_changed_at'],
                $order['full_name']
            ];
        }
        
        return $data;
    }
    
    /**
     * Создание нового пожелания
     */
    public function createWish($userId, $text) {
        $stmt = $this->db->prepare("
            INSERT INTO wishes (user_id, text, status)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $userId,
            $text,
            WISH_STATUS_ACTIVE
        ]);
    }
    
    /**
     * Получение списка пожеланий
     */
    public function getWishes($filters = []) {
        $sql = "SELECT w.*, u.username, u.full_name
                FROM wishes w
                LEFT JOIN users u ON w.user_id = u.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND w.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        $sql .= " ORDER BY w.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Получение пожелания по ID
     */
    public function getWishById($id) {
        $stmt = $this->db->prepare("
            SELECT w.*, u.username, u.full_name
            FROM wishes w
            LEFT JOIN users u ON w.user_id = u.id
            WHERE w.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Обновление статуса пожелания
     */
    public function updateWishStatus($wishId, $newStatus) {
        $stmt = $this->db->prepare("
            UPDATE wishes
            SET status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([$newStatus, $wishId]);
    }
    
    /**
     * Удаление пожелания
     */
    public function deleteWish($wishId) {
        $stmt = $this->db->prepare("DELETE FROM wishes WHERE id = ?");
        return $stmt->execute([$wishId]);
    }
    
    /**
     * Получение всех статусов пожеланий
     */
    public function getWishStatuses() {
        return [WISH_STATUS_ACTIVE, WISH_STATUS_COMPLETED];
    }
    
    /**
     * Создание новой заявки на ремонт
     */
    public function createRepair($userId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO repairs (user_id, title, description, building, room, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $userId,
            $data['title'],
            $data['description'],
            $data['building'] ?? '',
            $data['room'] ?? '',
            REPAIR_STATUS_WAITING
        ]);
    }
    
    /**
     * Получение списка заявок на ремонт
     */
    public function getRepairs($filters = []) {
        $sql = "SELECT r.*, u.username, u.full_name
                FROM repairs r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND r.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Получение заявки на ремонт по ID
     */
    public function getRepairById($id) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.username, u.full_name
            FROM repairs r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Обновление заявки на ремонт
     */
    public function updateRepair($repairId, $data) {
        $allowedFields = ['title', 'description', 'status', 'building', 'room'];
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $repairId;
        
        $sql = "UPDATE repairs SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Обновление статуса заявки на ремонт
     */
    public function updateRepairStatus($repairId, $newStatus) {
        $stmt = $this->db->prepare("
            UPDATE repairs
            SET status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([$newStatus, $repairId]);
    }
    
    /**
     * Удаление заявки на ремонт
     */
    public function deleteRepair($repairId) {
        $stmt = $this->db->prepare("DELETE FROM repairs WHERE id = ?");
        return $stmt->execute([$repairId]);
    }
    
    /**
     * Экспорт заявок на ремонт в Excel
     */
    public function exportRepairs($filters = []) {
        $repairs = $this->getRepairs($filters);
        
        $data = [];
        $data[] = ['ID', 'Дата создания', 'Пользователь', 'Название', 'Описание', 'Статус', 'Дата обновления'];
        
        foreach ($repairs as $repair) {
            $data[] = [
                $repair['id'],
                $repair['created_at'],
                $repair['full_name'] ?? $repair['username'],
                $repair['title'],
                $repair['description'],
                $repair['status'],
                $repair['updated_at']
            ];
        }
        
        return $data;
    }
    
    /**
     * Получение всех статусов ремонта
     */
    public function getRepairStatuses() {
        return [
            REPAIR_STATUS_WAITING,
            REPAIR_STATUS_IN_PROGRESS,
            REPAIR_STATUS_COMPLETED,
            REPAIR_STATUS_CLOSED
        ];
    }
}

// Инициализация базы данных при первом запуске
$db = Database::getInstance();
$db->initTables();