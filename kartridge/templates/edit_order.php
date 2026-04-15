<h2 class="mb-4">Редактирование заказа №<?php echo $order['id']; ?></h2>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/?action=edit_order&id=<?php echo $order['id']; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="department" class="form-label">Отдел <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="department" name="department" required 
                           value="<?php echo htmlspecialchars($order['department']); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="building" class="form-label">Корпус <span class="text-danger">*</span></label>
                    <select class="form-select" id="building" name="building" required>
                        <?php foreach (getBuildings() as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $order['building'] === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="room" class="form-label">Кабинет <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="room" name="room" required 
                           value="<?php echo htmlspecialchars($order['room']); ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="printer_model" class="form-label">Модель принтера <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select class="form-select" id="printer_model" name="printer_model" required>
                            <?php foreach (getPrinterModels() as $value => $label): ?>
                                <option value="<?php echo htmlspecialchars($label); ?>" <?php echo $order['printer_model'] === $label ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="__new__">+ Другая (ввести)</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" id="addPrinterBtn" title="Добавить в справочник" style="display:none;">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    </div>
                    <input type="text" class="form-control mt-2" id="new_printer_model" name="new_printer_model" 
                           placeholder="Введите название новой модели" style="display:none;">
                </div>
                
                <div class="col-md-6">
                    <label for="repair_type" class="form-label">Тип ремонта <span class="text-danger">*</span></label>
                    <select class="form-select" id="repair_type" name="repair_type" required>
                        <?php foreach (getRepairTypes() as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($label); ?>" <?php echo $order['repair_type'] === $label ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="status" class="form-label">Статус <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <?php foreach (getAllStatuses() as $status): ?>
                            <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <label for="description" class="form-label">Описание проблемы</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($order['description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="<?php echo url('/?action=view_order&id=' . $order['id']); ?>" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const printerSelect = document.getElementById('printer_model');
    const newPrinterInput = document.getElementById('new_printer_model');
    const addPrinterBtn = document.getElementById('addPrinterBtn');
    
    printerSelect.addEventListener('change', function() {
        if (this.value === '__new__') {
            newPrinterInput.style.display = 'block';
            newPrinterInput.required = true;
            addPrinterBtn.style.display = 'block';
            this.name = 'printer_model_hidden';
        } else {
            newPrinterInput.style.display = 'none';
            newPrinterInput.required = false;
            addPrinterBtn.style.display = 'none';
            this.name = 'printer_model';
        }
    });
    
    addPrinterBtn.addEventListener('click', function() {
        const newModel = newPrinterInput.value.trim();
        if (!newModel) {
            alert('Введите название модели принтера');
            return;
        }
        
        fetch('/?action=add_printer_model', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'name=' + encodeURIComponent(newModel) + '&csrf_token=<?php echo csrfToken(); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const option = document.createElement('option');
                option.value = data.name;
                option.textContent = data.name;
                printerSelect.insertBefore(option, printerSelect.lastElementChild);
                printerSelect.value = data.name;
                newPrinterInput.style.display = 'none';
                addPrinterBtn.style.display = 'none';
                printerSelect.name = 'printer_model';
            } else {
                alert(data.message || 'Ошибка при добавлении модели');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при добавлении модели');
        });
    });
});
</script>
