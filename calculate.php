<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'includes/header.php';
require_once 'includes/nav.php';

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $description = $_POST['description'];
    $date = $_POST['date'];
    $isTaxIncluded = isset($_POST['is_tax_included']) ? true : false;
    
    // حساب القيم الضريبية
    if ($isTaxIncluded) {
        $vatAmount = $amount * VAT_RATE / (1 + VAT_RATE);
        $netAmount = $amount - $vatAmount;
    } else {
        $vatAmount = $amount * VAT_RATE;
        $netAmount = $amount;
    }
    
    $totalAmount = $netAmount + $vatAmount;
    
    // تخزين البيانات لعرضها
    $calculationResult = [
        'type' => $type,
        'date' => $date,
        'amount' => $amount,
        'net_amount' => $netAmount,
        'vat_amount' => $vatAmount,
        'total_amount' => $totalAmount,
        'description' => $description,
        'is_tax_included' => $isTaxIncluded
    ];
}
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card vat-card">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-calculator"></i> حاسبة الضريبة للمشتريات والمبيعات</h4>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label">نوع المعاملة</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="<?php echo TRANSACTION_TYPE_PURCHASE; ?>">فاتورة مشتريات</option>
                                <option value="<?php echo TRANSACTION_TYPE_SALE; ?>">فاتورة مبيعات</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date" class="form-label">تاريخ الفاتورة</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">المبلغ</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   step="0.01" min="0" required>
                            <span class="input-group-text">ر.س</span>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_tax_included" name="is_tax_included">
                        <label class="form-check-label" for="is_tax_included">المبلغ شامل الضريبة</label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">وصف الفاتورة</label>
                        <input type="text" class="form-control" id="description" name="description" 
                               placeholder="مثال: شراء بضاعة من المورد X">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calculator-fill"></i> حساب الضريبة
                        </button>
                    </div>
                </form>
                
                <?php if (isset($calculationResult)): ?>
                <div class="mt-4">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5><i class="bi bi-check-circle"></i> نتائج الحساب</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>نوع الفاتورة</th>
                                            <td>
                                                <?php if ($calculationResult['type'] == TRANSACTION_TYPE_PURCHASE): ?>
                                                    <span class="badge bg-warning text-dark">مشتريات</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">مبيعات</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ الفاتورة</th>
                                            <td><?php echo $calculationResult['date']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>وصف الفاتورة</th>
                                            <td><?php echo $calculationResult['description']; ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>المبلغ <?php echo $calculationResult['is_tax_included'] ? '(شامل الضريبة)' : '(قبل الضريبة)'; ?></th>
                                            <td><?php echo number_format($calculationResult['amount'], 2); ?> ر.س</td>
                                        </tr>
                                        <tr>
                                            <th>قيمة الضريبة (<?php echo (VAT_RATE * 100); ?>%)</th>
                                            <td><?php echo number_format($calculationResult['vat_amount'], 2); ?> ر.س</td>
                                        </tr>
                                        <tr class="table-primary fw-bold">
                                            <th>المبلغ الإجمالي</th>
                                            <td><?php echo number_format($calculationResult['total_amount'], 2); ?> ر.س</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-3">
                                <a href="add_transaction.php?type=<?php echo $calculationResult['type']; ?>&amount=<?php echo $calculationResult['amount']; ?>&description=<?php echo urlencode($calculationResult['description']); ?>&date=<?php echo $calculationResult['date']; ?>&is_tax_included=<?php echo $calculationResult['is_tax_included'] ? 1 : 0; ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-save"></i> حفظ الفاتورة
                                </a>
                                <a href="calculate.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-repeat"></i> حساب جديد
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <h5><i class="bi bi-info-circle"></i> ملاحظات ضريبية:</h5>
                        <ul>
                            <?php if ($calculationResult['type'] == TRANSACTION_TYPE_PURCHASE): ?>
                                <li>ضريبة المشتريات تعتبر ضريبة مدخلة (قابلة للاسترداد أو التخصيم)</li>
                                <li>يجب الاحتفاظ بصورة من الفاتورة الضريبية</li>
                            <?php else: ?>
                                <li>ضريبة المبيعات تعتبر ضريبة مخرجة (تضاف إلى الذمم الضريبية المستحقة)</li>
                                <li>يجب إصدار فاتورة ضريبية معتمدة للعميل</li>
                            <?php endif; ?>
                            <li>معدل الضريبة الحالي في المملكة العربية السعودية: <?php echo (VAT_RATE * 100); ?>%</li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>