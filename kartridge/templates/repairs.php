<h2 class="mb-4">Заявки на ремонт</h2>

<?php if ($message = getFlashMessage()): ?>
    <div class="alert alert-<?php echo $message['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Фильтры для всех пользователей -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="action" value="repairs">
            
            <div class="col-md-4">
                <label class="form-label">Статус</label>
                <select class="form-select" name="status">
                    <option value="">Все статусы</option>
                    <?php
                    $db = Database::getInstance();
                    $statuses = $db->getRepairStatuses();
                    foreach ($statuses as $status):
                    ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo ($filters['status'] ?? '') === $status ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4 d-flex align-items-end">
                <div class="d-grid gap-2 w-100">
                    <button type="submit" class="btn btn-outline-primary">Фильтр</button>
                    <?php if (!empty($filters)): ?>
                        <a href="<?php echo url('/?action=repairs'); ?>" class="btn btn-outline-secondary">Сброс</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Кнопки действий -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <?php if (isClient()): ?>
            <a href="<?php echo url('/?action=create_repair'); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Новая заявка
            </a>
        <?php endif; ?>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <div class="text-muted me-3">
            Всего заявок: <strong><?php echo count($repairs); ?></strong>
        </div>
        
        <?php if (isAdmin() || isRemontnik()): ?>
            <a href="<?php
                echo url('/?action=export_repairs' .
                    (isset($filters['status']) && $filters['status'] ? '&status=' . urlencode($filters['status']) : '') .
                    (isset($filters['date_from']) && $filters['date_from'] ? '&date_from=' . urlencode($filters['date_from']) : '') .
                    (isset($filters['date_to']) && $filters['date_to'] ? '&date_to=' . urlencode($filters['date_to']) : '')
                );
            ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Экспорт в Excel
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Таблица заявок -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>№</th>
                    <th>Дата</th>
                    <?php if (isAdmin() || isRemontnik()): ?>
                        <th>Пользователь</th>
                    <?php endif; ?>
                    <th>Корпус</th>
                    <th>Кабинет</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($repairs)): ?>
                    <tr>
                        <td colspan="<?php echo (isAdmin() || isRemontnik()) ? 9 : 8; ?>" class="text-center text-muted py-4">Заявок не найдено</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($repairs as $repair): ?>
                        <tr>
                            <td><?php echo $repair['id']; ?></td>
                            <td><?php echo formatDate($repair['created_at']); ?></td>
                            <?php if (isAdmin() || isRemontnik()): ?>
                                <td>
                                    <?php echo htmlspecialchars($repair['full_name'] ?? $repair['username']); ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($repair['username']); ?></small>
                                </td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($repair['building'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($repair['room'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($repair['title']); ?></td>
                            <td>
                                <div style="max-width: 300px; white-space: pre-wrap;"><?php echo htmlspecialchars($repair['description']); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $repair['status'] === REPAIR_STATUS_WAITING ? 'warning' : 
                                        ($repair['status'] === REPAIR_STATUS_IN_PROGRESS ? 'info' : 
                                        ($repair['status'] === REPAIR_STATUS_COMPLETED ? 'success' : 'secondary')); 
                                ?>">
                                    <?php echo htmlspecialchars($repair['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (isAdmin() || isRemontnik()): ?>
                                    <a href="<?php echo url('/?action=edit_repair&id=' . $repair['id']); ?>"
                                       class="btn btn-sm btn-outline-secondary" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($repair['status'] !== REPAIR_STATUS_COMPLETED): ?>
                                        <a href="<?php echo url('/?action=update_repair_status&id=' . $repair['id'] . '&status=' . REPAIR_STATUS_COMPLETED); ?>"
                                           class="btn btn-sm btn-outline-success" title="Отметить как выполненную">
                                            <i class="bi bi-check-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (isAdmin()): ?>
                                        <a href="<?php echo url('/?action=delete_repair&id=' . $repair['id']); ?>"
                                           class="btn btn-sm btn-outline-danger confirm-delete" title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Для клиентов только просмотр -->
                                    <span class="text-muted">Только просмотр</span>
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
            if (!confirm('Вы уверены, что хотите удалить эту заявку?')) {
                e.preventDefault();
            }
        });
    });
});
</script>