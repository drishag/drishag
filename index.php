<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'includes/header.php';
require_once 'includes/nav.php';

// جلب آخر 5 حركات
$recentTransactions = array_slice(array_reverse(getTransactions()), 0, 5);
$summary = calculateVatSummary(getTransactions());
?>

<div class="row mt-4">
    <!-- بطاقة ملخص الضريبة -->
    <div class="col-lg-6 mb-4">
        <div class="card vat-card h-100">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-graph-up"></i> ملخص الضريبة الحالي</h4>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>ضريبة المبيعات</h5>
                            <h3 class="text-success"><?php echo number_format($summary['total_sale_vat'], 2); ?> ر.س</h3>
                            <small class="text-muted">إجمالي المبيعات: <?php echo number_format($summary['total_sales'], 2); ?> ر.س</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>ضريبة المشتريات</h5>
                            <h3 class="text-danger"><?php echo number_format($summary['total_purchase_vat'], 2); ?> ر.س</h3>
                            <small class="text-muted">إجمالي المشتريات: <?php echo number_format($summary['total_purchases'], 2); ?> ر.س</small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3 p-3 rounded <?php echo $summary['vat_due'] >= 0 ? 'bg-success text-white' : 'bg-warning'; ?>">
                    <h4>الضريبة المستحقة</h4>
                    <h2><?php echo number_format(abs($summary['vat_due']), 2); ?> ر.س</h2>
                    <p>
                        <?php if ($summary['vat_due'] >= 0): ?>
                            <i class="bi bi-arrow-up-circle"></i> مستحقة للدائنية الضريبية
                        <?php else: ?>
                            <i class="bi bi-arrow-down-circle"></i> مستحقة للذمم الضريبية
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- بطاقة الإجراءات السريعة -->
    <div class="col-lg-6 mb-4">
        <div class="card vat-card h-100">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-lightning-charge"></i> إجراءات سريعة</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="add_transaction.php?type=<?php echo TRANSACTION_TYPE_PURCHASE; ?>" class="btn btn-outline-warning w-100 py-3">
                            <i class="bi bi-cart-plus fs-1"></i><br>
                            <span>إضافة مشتريات</span>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="add_transaction.php?type=<?php echo TRANSACTION_TYPE_SALE; ?>" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-receipt fs-1"></i><br>
                            <span>إضافة مبيعات</span>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="calculate.php" class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-calculator fs-1"></i><br>
                            <span>حاسبة الضريبة</span>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="reports.php" class="btn btn-outline-secondary w-100 py-3">
                            <i class="bi bi-file-earmark-bar-graph fs-1"></i><br>
                            <span>التقارير</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- آخر الحركات المسجلة -->
<div class="row mt-2">
    <div class="col-12">
        <div class="card vat-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-clock-history"></i> آخر الحركات</h4>
                <a href="transactions.php" class="btn btn-light btn-sm">عرض الكل</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentTransactions)): ?>
                    <div class="alert alert-info">لا توجد حركات مسجلة بعد</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>الوصف</th>
                                    <th>المبلغ</th>
                                    <th>الضريبة</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                <tr>
                                    <td><?php echo $transaction['id']; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($transaction['date'])); ?></td>
                                    <td>
                                        <?php if ($transaction['type'] == TRANSACTION_TYPE_PURCHASE): ?>
                                            <span class="badge bg-warning text-dark">شراء</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">بيع</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo mb_substr($transaction['description'], 0, 20) . (mb_strlen($transaction['description']) > 20 ? '...' : ''); ?></td>
                                    <td><?php echo number_format($transaction['net_amount'], 2); ?> ر.س</td>
                                    <td><?php echo number_format($transaction['vat_amount'], 2); ?> ر.س</td>
                                    <td><?php echo number_format($transaction['net_amount'] + $transaction['vat_amount'], 2); ?> ر.س</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- بطاقات إحصائية -->
<div class="row mt-4">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body text-center">
                <i class="bi bi-receipt fs-1"></i>
                <h5 class="card-title mt-2">إجمالي المبيعات</h5>
                <h3><?php echo number_format($summary['total_sales'], 2); ?> ر.س</h3>
                <p class="small">ضريبة: <?php echo number_format($summary['total_sale_vat'], 2); ?> ر.س</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning h-100">
            <div class="card-body text-center">
                <i class="bi bi-cart fs-1"></i>
                <h5 class="card-title mt-2">إجمالي المشتريات</h5>
                <h3><?php echo number_format($summary['total_purchases'], 2); ?> ر.س</h3>
                <p class="small">ضريبة: <?php echo number_format($summary['total_purchase_vat'], 2); ?> ر.س</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card text-white <?php echo $summary['vat_due'] >= 0 ? 'bg-info' : 'bg-danger'; ?> h-100">
            <div class="card-body text-center">
                <i class="bi bi-cash-coin fs-1"></i>
                <h5 class="card-title mt-2">الرصيد الضريبي</h5>
                <h3><?php echo number_format(abs($summary['vat_due']), 2); ?> ر.س</h3>
                <p class="small">
                    <?php if ($summary['vat_due'] >= 0): ?>
                        <i class="bi bi-arrow-up"></i> مستحقة للدائنية
                    <?php else: ?>
                        <i class="bi bi-arrow-down"></i> مستحقة للذمم
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>