<h2 class="mb-4">Отправить пожелание по работе системы</h2>

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
        <form method="POST" action="<?php echo url('/?action=create_wish'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="mb-3">
                <label for="text" class="form-label">Текст пожелания <span class="text-danger">*</span></label>
                <textarea class="form-control" id="text" name="text" rows="6" required 
                          placeholder="Опишите ваши пожелания, предложения или замечания по работе системы..."><?php echo htmlspecialchars($_POST['text'] ?? ''); ?></textarea>
                <div class="form-text">Ваше пожелание будет рассмотрено администратором. Статус пожелания можно отслеживать в списке пожеланий.</div>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Отправить пожелание</button>
                <a href="<?php echo url('/?action=wishes'); ?>" class="btn btn-outline-secondary">К списку пожеланий</a>
                <a href="<?php echo url('/?action=dashboard'); ?>" class="btn btn-outline-secondary">На главную</a>
            </div>
        </form>
    </div>
</div>