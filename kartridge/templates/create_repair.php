<h2 class="mb-4">Создание заявки на ремонт</h2>

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
        <form method="POST" action="<?php echo url('/?action=create_repair'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="mb-3">
                <label for="title" class="form-label">Название заявки <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" required
                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                       placeholder="Например: Ремонт принтера в кабинете 305">
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="building" class="form-label">Корпус <span class="text-danger">*</span></label>
                    <select class="form-select" id="building" name="building" required>
                        <option value="">Выберите корпус</option>
                        <?php
                        $buildings = getBuildings();
                        $selectedBuilding = $_POST['building'] ?? '';
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
                           value="<?php echo htmlspecialchars($_POST['room'] ?? ''); ?>"
                           placeholder="Например: 305">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Описание проблемы</label>
                <textarea class="form-control" id="description" name="description" rows="6"
                          placeholder="Подробно опишите проблему, что случилось, какие симптомы... (необязательно)"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <div class="form-text">Опишите проблему как можно подробнее, чтобы ремонтник мог быстрее понять суть. Поле необязательное.</div>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Создать заявку</button>
                <a href="<?php echo url('/?action=repairs'); ?>" class="btn btn-outline-secondary">К списку заявок</a>
                <a href="<?php echo url('/?action=dashboard'); ?>" class="btn btn-outline-secondary">На главную</a>
            </div>
        </form>
    </div>
</div>