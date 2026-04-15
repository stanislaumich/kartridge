<?php
/**
 * Конфигурация приложения для заказа обслуживания картриджей
 */

// Базовый путь приложения (если проект находится в подпапке)
define('BASE_PATH', '/kartridge');

// Настройки временной зоны (GMT+3 / Europe/Moscow)
date_default_timezone_set('Europe/Moscow');

// Настройки базы данных SQLite
define('DB_PATH', __DIR__ . '/database.sqlite');

// Настройки сессии
define('SESSION_NAME', 'CARTRIDGE_APP');
define('SESSION_LIFETIME', 3600);

// Настройки шаблонов
define('TEMPLATE_DIR', __DIR__ . '/templates');
define('LAYOUT_DIR', TEMPLATE_DIR);

// Статусы заказов
define('STATUS_WAITING', 'ОЖИДАЕТ');
define('STATUS_IN_PROGRESS', 'В ПРОЦЕССЕ');
define('STATUS_CLOSED', 'ЗАКРЫТО');
define('STATUS_COMPLETED', 'ЗАВЕРШЕНО');
define('STATUS_VIEWED', 'ВЕДОМОСТЬ');

// Статусы пожеланий
define('WISH_STATUS_ACTIVE', 'активно');
define('WISH_STATUS_COMPLETED', 'выполнено');

// Статусы ремонта
define('REPAIR_STATUS_WAITING', 'ОЖИДАЕТ');
define('REPAIR_STATUS_IN_PROGRESS', 'В ПРОЦЕССЕ');
define('REPAIR_STATUS_COMPLETED', 'ЗАВЕРШЕНО');
define('REPAIR_STATUS_CLOSED', 'ЗАКРЫТО');

// Роли пользователей
define('ROLE_CLIENT', 'client');
define('ROLE_MECHANIC', 'mechanic');
define('ROLE_REMONTNIK', 'remontnik');
define('ROLE_ADMIN', 'admin');

// Пути к файлам (с учетом базового пути)
define('CSS_PATH', BASE_PATH . '/css');
define('JS_PATH', BASE_PATH . '/js');
define('UPLOAD_PATH', __DIR__ . '/uploads');