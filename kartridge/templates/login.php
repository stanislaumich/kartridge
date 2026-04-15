<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">
                    <i class="bi bi-printer"></i> Обслуживание картриджей
                </h3>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($message = getFlashMessage()): ?>
                    <div class="alert alert-<?php echo $message['type'] === 'error' ? 'danger' : 'success'; ?>">
                        <?php echo htmlspecialchars($message['message']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo url('/?action=login'); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Логин</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Войти</button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-muted small">
                    <p class="mb-1"><strong>Тестовые аккаунты:</strong></p>
                    <p class="mb-0">admin / admin123 (Администратор)</p>
                    <p class="mb-0">mechanic / mechanic123 (Ремонтник)</p>
                    <p class="mb-0">client / client123 (Заказчик)</p>
                </div>
            </div>
        </div>
    </div>
</div>