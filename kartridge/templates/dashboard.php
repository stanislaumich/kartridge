<h2 class="mb-4">Заказы на обслуживание</h2>

<?php if ($message = getFlashMessage()): ?>
    <div class="alert alert-<?php echo $message['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Фильтры -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="action" value="dashboard">
            
            <div class="col-md-3">
                <label class="form-label">Статус</label>
                <select class="form-select" name="status">
                    <option value="">Все статусы</option>
                    <?php foreach (getAllStatuses() as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo ($filters['status'] ?? '') === $status ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label"><?php echo isMechanic() ? 'Корпус' : 'Отдел'; ?></label>
                <?php if (isMechanic()): ?>
                <select class="form-select" name="building">
                    <option value="">Все корпуса</option>
                    <?php foreach (getBuildings() as $value => $label): ?>
                        <?php if ($value): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo ($filters['building'] ?? '') === $value ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <input type="text" class="form-control" name="department" value="<?php echo htmlspecialchars($filters['department'] ?? ''); ?>" placeholder="Название отдела">
                <?php endif; ?>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Дата с</label>
                <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Дата по</label>
                <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <div class="d-grid gap-2 w-100">
                    <button type="submit" class="btn btn-outline-primary">Фильтр</button>
                    <?php if (!empty($filters)): ?>
                        <a href="<?php echo url('/?action=dashboard'); ?>" class="btn btn-outline-secondary">Сброс</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Кнопки действий -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <?php if (isClient()): ?>
            <a href="<?php echo url('/?action=create_order'); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Принтеры
            </a>
        <?php endif; ?>
        
        <?php if (isClient() || isMechanic()): ?>
            <a href="<?php echo url('/?action=create_wish'); ?>" class="btn btn-info">
                <i class="bi bi-lightbulb"></i> Новое пожелание
            </a>
        <?php endif; ?>
        
        <?php if (isAdmin() || isMechanic()): ?>
            <a href="<?php echo url('/?action=export&' . http_build_query($filters)); ?>" class="btn btn-success">
                <i class="bi bi-file-excel"></i> Экспорт в Excel
            </a>
        <?php endif; ?>
        
        <!-- Ссылка на список пожеланий для всех -->
        <a href="<?php echo url('/?action=wishes'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-list-check"></i> Пожелания
        </a>
        <!-- Ссылка на список заявок на ремонт (не для заправщика картриджей) -->
        <?php if (!isMechanic()): ?>
            <a href="<?php echo url('/?action=repairs'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-tools"></i> Ремонт
            </a>
        <?php endif; ?>
    </div>
    
    <div class="text-muted">
        Всего заказов: <strong><?php echo count($orders); ?></strong>
    </div>
</div>

<!-- Таблица заказов -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>№</th>
                    <th>Дата</th>
                    <th>Отдел</th>
                    <th>Корпус</th>
                    <th>Кабинет</th>
                    <th>Модель принтера</th>
                    <th>Тип ремонта</th>
                    <th>Статус</th>
                    <th>Изменен</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Заказов не найдено</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo formatDate($order['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($order['department']); ?></td>
                            <td><?php echo htmlspecialchars($order['building']); ?></td>
                            <td><?php echo htmlspecialchars($order['room']); ?></td>
                            <td><?php echo htmlspecialchars($order['printer_model']); ?></td>
                            <td><?php echo htmlspecialchars($order['repair_type']); ?></td>
                            <td>
                                <span class="<?php echo getStatusClass($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($order['status_changed_at']); ?></td>
                            <td>
                                <a href="<?php echo url('/?action=view_order&id=' . $order['id']); ?>" class="btn btn-sm btn-outline-primary" title="Просмотр">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <?php if (isAdmin()): ?>
                                    <a href="<?php echo url('/?action=edit_order&id=' . $order['id']); ?>" class="btn btn-sm btn-outline-secondary" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?php echo url('/?action=delete_order&id=' . $order['id']); ?>" class="btn btn-sm btn-outline-danger confirm-delete" title="Удалить">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php elseif (isMechanic() && $order['status'] !== STATUS_COMPLETED): ?>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown">
                                            Изменить статус
                                        </button>
                                        <ul class="dropdown-menu status-dropdown dropdown-menu-end">
                                            <?php foreach (getAllStatuses() as $status): ?>
                                                <?php if ($status !== $order['status']): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="<?php echo url('/?action=update_status&id=' . $order['id'] . '&status=' . urlencode($status)); ?>">
                                                            <?php echo htmlspecialchars($status); ?>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.confirm-delete').forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите удалить этот заказ?')) {
                e.preventDefault();
            }
        });
    });
});
</script>