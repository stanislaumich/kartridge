<h2 class="mb-4">Управление пользователями</h2>

<?php if ($message = getFlashMessage()): ?>
    <div class="alert alert-<?php echo $message['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted">
        Всего пользователей: <strong><?php echo count($users); ?></strong>
    </div>
    <div>
        <a href="<?php echo url('/?action=create_user'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать пользователя
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Логин</th>
                    <th>ФИО</th>
                    <th>Роль</th>
                    <th>Email</th>
                    <th>Отдел</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Пользователей не найдено</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo getRoleName($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['department'] ?? ''); ?></td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo url('/?action=edit_user&id=' . $user['id']); ?>" class="btn btn-outline-primary" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="<?php echo url('/?action=delete_user&id=' . $user['id']); ?>" class="btn btn-outline-danger confirm-delete" title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    <a href="<?php echo url('/?action=dashboard'); ?>" class="btn btn-outline-secondary">Назад к заказам</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.confirm-delete').forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) {
                e.preventDefault();
            }
        });
    });
});
</script>