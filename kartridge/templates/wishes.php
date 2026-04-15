<h2 class="mb-4">Пожелания по работе системы</h2>

<?php if ($message = getFlashMessage()): ?>
    <div class="alert alert-<?php echo $message['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isAdmin()): ?>
<!-- Фильтры для администратора -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="action" value="wishes">
            
            <div class="col-md-4">
                <label class="form-label">Статус</label>
                <select class="form-select" name="status">
                    <option value="">Все статусы</option>
                    <option value="<?php echo WISH_STATUS_ACTIVE; ?>" <?php echo ($filters['status'] ?? '') === WISH_STATUS_ACTIVE ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(WISH_STATUS_ACTIVE); ?>
                    </option>
                    <option value="<?php echo WISH_STATUS_COMPLETED; ?>" <?php echo ($filters['status'] ?? '') === WISH_STATUS_COMPLETED ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(WISH_STATUS_COMPLETED); ?>
                    </option>
                </select>
            </div>
            
            <div class="col-md-4 d-flex align-items-end">
                <div class="d-grid gap-2 w-100">
                    <button type="submit" class="btn btn-outline-primary">Фильтр</button>
                    <?php if (!empty($filters)): ?>
                        <a href="<?php echo url('/?action=wishes'); ?>" class="btn btn-outline-secondary">Сброс</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Кнопки действий -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <?php if (isClient() || isMechanic()): ?>
            <a href="<?php echo url('/?action=create_wish'); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Новое пожелание
            </a>
        <?php endif; ?>
    </div>
    
    <div class="text-muted">
        Всего пожеланий: <strong><?php echo count($wishes); ?></strong>
    </div>
</div>

<!-- Таблица пожеланий -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>№</th>
                    <th>Дата</th>
                    <?php if (isAdmin()): ?>
                        <th>Пользователь</th>
                    <?php endif; ?>
                    <th>Текст пожелания</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($wishes)): ?>
                    <tr>
                        <td colspan="<?php echo isAdmin() ? 6 : 5; ?>" class="text-center text-muted py-4">Пожеланий не найдено</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($wishes as $wish): ?>
                        <tr>
                            <td><?php echo $wish['id']; ?></td>
                            <td><?php echo formatDate($wish['created_at']); ?></td>
                            <?php if (isAdmin()): ?>
                                <td>
                                    <?php echo htmlspecialchars($wish['full_name'] ?? $wish['username']); ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($wish['username']); ?></small>
                                </td>
                            <?php endif; ?>
                            <td>
                                <div style="max-width: 400px; white-space: pre-wrap;"><?php echo htmlspecialchars($wish['text']); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $wish['status'] === WISH_STATUS_ACTIVE ? 'warning' : 'success'; ?>">
                                    <?php echo htmlspecialchars($wish['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (isAdmin()): ?>
                                    <?php if ($wish['status'] === WISH_STATUS_ACTIVE): ?>
                                        <a href="<?php echo url('/?action=update_wish_status&id=' . $wish['id'] . '&status=' . WISH_STATUS_COMPLETED); ?>" 
                                           class="btn btn-sm btn-outline-success" title="Отметить как выполненное">
                                            <i class="bi bi-check-circle"></i> Выполнено
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo url('/?action=update_wish_status&id=' . $wish['id'] . '&status=' . WISH_STATUS_ACTIVE); ?>" 
                                           class="btn btn-sm btn-outline-warning" title="Вернуть в активные">
                                            <i class="bi bi-arrow-counterclockwise"></i> Активно
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo url('/?action=delete_wish&id=' . $wish['id']); ?>" 
                                       class="btn btn-sm btn-outline-danger confirm-delete" title="Удалить">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <!-- Для клиентов и ремонтников только просмотр -->
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
            if (!confirm('Вы уверены, что хотите удалить это пожелание?')) {
                e.preventDefault();
            }
        });
    });
});
</script>