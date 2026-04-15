<h2 class="mb-4">Создание нового пользователя</h2>

<?php if ($message = getFlashMessage()): ?>
    <div class="alert alert-<?php echo $message['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('/?action=create_user'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="username" class="form-label">Логин <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="password" class="form-label">Пароль <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="col-md-6">
                    <label for="full_name" class="form-label">ФИО <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="role" class="form-label">Роль <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                        <option value="mechanic" <?php echo ($_POST['role'] ?? '') === 'mechanic' ? 'selected' : ''; ?>>Ремонтник</option>
                        <option value="client" <?php echo ($_POST['role'] ?? '') === 'client' ? 'selected' : ''; ?>>Заказчик</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="department" class="form-label">Отдел (для заказчика)</label>
                    <input type="text" class="form-control" id="department" name="department" 
                           value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Создать пользователя</button>
                <a href="<?php echo url('/?action=users'); ?>" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>