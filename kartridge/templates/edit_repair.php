<h2 class="mb-4">Редактирование заявки на ремонт</h2>

<?php if ($message = getFlashMessage()): ?>
    <div class="alert alert-<?php echo $message['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('/?action=edit_repair&id=' . $repair['id']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="mb-3">
                <label for="title" class="form-label">Название заявки <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" required
                       value="<?php echo htmlspecialchars($repair['title']); ?>">
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="building" class="form-label">Корпус <span class="text-danger">*</span></label>
                    <select class="form-select" id="building" name="building" required>
                        <option value="">Выберите корпус</option>
                        <?php
                        $buildings = getBuildings();
                        $selectedBuilding = $repair['building'] ?? '';
                        foreach ($buildings as $value => $label):
                        ?>
                            <option value="<?php echo htmlspecialchars($label); ?>" <?php echo $selectedBuilding === $label ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="room" class="form-label">Кабинет <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="room" name="room" required
                           value="<?php echo htmlspecialchars($repair['room'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Описание проблемы <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($repair['description']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Статус</label>
                <select class="form-select" id="status" name="status">
                    <?php
                    $db = Database::getInstance();
                    $statuses = $db->getRepairStatuses();
                    foreach ($statuses as $status):
                    ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $repair['status'] === $status ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="<?php echo url('/?action=repairs'); ?>" class="btn btn-outline-secondary">Отмена</a>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo url('/?action=delete_repair&id=' . $repair['id']); ?>" class="btn btn-outline-danger confirm-delete">Удалить</a>
                <?php endif; ?>
            </div>
        </form>
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