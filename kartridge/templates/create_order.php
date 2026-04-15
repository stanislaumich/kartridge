<h2 class="mb-4">Создание нового заказа</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('/?action=create_order'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="department" class="form-label">Отдел <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="department" name="department" required 
                           value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="building" class="form-label">Корпус <span class="text-danger">*</span></label>
                    <select class="form-select" id="building" name="building" required>
                        <?php foreach (getBuildings() as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo ($data['building'] ?? '') === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="room" class="form-label">Кабинет <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="room" name="room" required 
                           value="<?php echo htmlspecialchars($data['room'] ?? ''); ?>" placeholder="Например: 305">
                </div>
                
                <div class="col-md-6">
                    <label for="printer_model" class="form-label">Модель принтера <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select class="form-select" id="printer_model" name="printer_model" required>
                            <?php
                            $printerModels = getPrinterModels();
                            $submittedModel = $data['printer_model'] ?? '';
                            $isNewModel = false;
                            
                            if (!empty($submittedModel)) {
                                // Проверяем, есть ли отправленная модель в списке
                                $isNewModel = true;
                                foreach ($printerModels as $value => $label) {
                                    if ($submittedModel === $label) {
                                        $isNewModel = false;
                                        break;
                                    }
                                }
                            }
                            
                            foreach ($printerModels as $value => $label):
                                if ($value === '') continue; // Пропускаем пустой вариант
                            ?>
                                <option value="<?php echo htmlspecialchars($label); ?>" <?php echo (!$isNewModel && $submittedModel === $label) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="__new__" <?php echo $isNewModel ? 'selected' : ''; ?>>+ Другая (ввести)</option>
                        </select>
                    </div>
                    <input type="text" class="form-control mt-2" id="new_printer_model" name="new_printer_model"
                           placeholder="Введите название новой модели"
                           value="<?php echo $isNewModel ? htmlspecialchars($submittedModel) : ''; ?>"
                           style="<?php echo $isNewModel ? 'display:block;' : 'display:none;' ?>">
                    <input type="hidden" id="printer_model_hidden" name="printer_model_hidden" value="">
                </div>
                
                <div class="col-md-6">
                    <label for="repair_type" class="form-label">Тип ремонта <span class="text-danger">*</span></label>
                    <select class="form-select" id="repair_type" name="repair_type" required>
                        <option value="">Выберите тип ремонта</option>
                        <?php foreach (getRepairTypes() as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($label); ?>" <?php echo ($data['repair_type'] ?? '') === $label ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <label for="description" class="form-label">Описание проблемы</label>
                    <textarea class="form-control" id="description" name="description" rows="4" 
                              placeholder="Опишите проблему подробнее..."><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Создать заказ</button>
                <a href="<?php echo url('/?action=dashboard'); ?>" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const printerSelect = document.getElementById('printer_model');
    const newPrinterInput = document.getElementById('new_printer_model');
    
    // При изменении выбора в dropdown
    printerSelect.addEventListener('change', function() {
        if (this.value === '__new__') {
            // Показать поле для ввода новой модели
            newPrinterInput.style.display = 'block';
            newPrinterInput.required = true;
            // Установить имя для текстового поля
            newPrinterInput.name = 'printer_model';
            // Очистить значение текстового поля, если оно было пустым
            if (!newPrinterInput.value.trim()) {
                newPrinterInput.value = '';
            }
        } else {
            // Скрыть поле для ввода новой модели
            newPrinterInput.style.display = 'none';
            newPrinterInput.required = false;
            // Убрать имя у текстового поля
            newPrinterInput.name = '';
        }
    });
    
    // При загрузке страницы, если уже выбрано "__new__", убедиться что поле ввода видно
    if (printerSelect.value === '__new__') {
        newPrinterInput.style.display = 'block';
        newPrinterInput.required = true;
        newPrinterInput.name = 'printer_model';
    }
});
</script>
