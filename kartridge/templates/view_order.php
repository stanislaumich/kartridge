<h2 class="mb-4">Просмотр заказа №<?php echo $order['id']; ?></h2>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Информация о заказе</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-muted">Отдел</label>
                <p class="form-control-plaintext"><?php echo htmlspecialchars($order['department']); ?></p>
            </div>
            
            <div class="col-md-2">
                <label class="form-label text-muted">Корпус</label>
                <p class="form-control-plaintext"><?php echo htmlspecialchars($order['building']); ?></p>
            </div>
            
            <div class="col-md-2">
                <label class="form-label text-muted">Кабинет</label>
                <p class="form-control-plaintext"><?php echo htmlspecialchars($order['room']); ?></p>
            </div>
            
            <div class="col-md-4">
                <label class="form-label text-muted">Модель принтера</label>
                <p class="form-control-plaintext"><?php echo htmlspecialchars($order['printer_model']); ?></p>
            </div>
            
            <div class="col-md-4">
                <label class="form-label text-muted">Тип ремонта</label>
                <p class="form-control-plaintext"><?php echo htmlspecialchars($order['repair_type']); ?></p>
            </div>
            
            <div class="col-md-4">
                <label class="form-label text-muted">Статус</label>
                <p class="form-control-plaintext">
                    <span class="<?php echo getStatusClass($order['status']); ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </p>
            </div>
            
            <div class="col-md-4">
                <label class="form-label text-muted">Заказчик</label>
                <p class="form-control-plaintext"><?php echo htmlspecialchars($order['full_name']); ?></p>
            </div>
            
            <div class="col-md-12">
                <label class="form-label text-muted">Описание</label>
                <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($order['description'] ?? '')); ?></p>
            </div>
            
            <div class="col-md-4">
                <label class="form-label text-muted">Дата создания</label>
                <p class="form-control-plaintext"><?php echo formatDate($order['created_at']); ?></p>
            </div>
            
            <div class="col-md-4">
                <label class="form-label text-muted">Последнее изменение</label>
                <p class="form-control-plaintext"><?php echo formatDate($order['updated_at']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- История статусов -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">История изменения статусов</h5>
    </div>
    <div class="card-body">
        <?php if (empty($history)): ?>
            <p class="text-muted">История пуста</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Изменил</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $item): ?>
                            <tr>
                                <td><?php echo formatDate($item['changed_at']); ?></td>
                                <td>
                                    <span class="<?php echo getStatusClass($item['status']); ?>">
                                        <?php echo htmlspecialchars($item['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($item['full_name'] ?? 'Система'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex gap-2">
    <a href="<?php echo url('/?action=dashboard'); ?>" class="btn btn-outline-secondary">Назад к списку</a>
    
    <?php if (isAdmin()): ?>
        <a href="<?php echo url('/?action=edit_order&id=' . $order['id']); ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
    <?php elseif (isMechanic() && $order['status'] !== STATUS_COMPLETED): ?>
        <div class="btn-group">
            <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
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
</div>