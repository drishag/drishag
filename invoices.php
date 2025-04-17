<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'includes/header.php';
require_once 'includes/nav.php';

// استبدل getInvoices() بـ getTransactions() لأننا نستخدم نظام الحركات وليس الفواتير
$transactions = getTransactions();
$summary = calculateVatSummary($transactions);
?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card vat-card mb-4">
            <div class="card-header bg-info text-white">
                <h4><i class="bi bi-graph-up"></i> ملخص الضريبة</h4>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>إجمالي المشتريات</h5>
                            <h3><?php echo number_format($summary['total_purchases'], 2); ?> ر.س</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>ضريبة المشتريات</h5>
                            <h3><?php echo number_format($summary['total_purchase_vat'], 2); ?> ر.س</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>إجمالي المبيعات</h5>
                            <h3><?php echo number_format($summary['total_sales'], 2); ?> ر.س</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded <?php echo $summary['vat_due'] >= 0 ? 'bg-success text-white' : 'bg-light'; ?>">
                            <h5>الضريبة المستحقة</h5>
                            <h3><?php echo number_format($summary['vat_due'], 2); ?> ر.س</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card vat-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-list-ul"></i> سجل الحركات</h4>
                <a href="add_transaction.php" class="btn btn-light"><i class="bi bi-plus-circle"></i> إضافة حركة</a>
            </div>
            <div class="card-body">
                <?php if (empty($transactions)): ?>
                    <div class="alert alert-info">لا توجد حركات مسجلة بعد</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
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
                                <?php foreach ($transactions as $transaction): ?>
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
                                    <td><?php echo $transaction['description']; ?></td>
                                    <td><?php echo number_format($transaction['net_amount'], 2); ?> ر.س</td>
                                    <td><?php echo number_format($transaction['vat_amount'], 2); ?> ر.س</td>
                                    <td>
                                        <?php echo number_format($transaction['net_amount'] + $transaction['vat_amount'], 2); ?> ر.س
                                        <?php if ($transaction['is_tax_included']): ?>
                                            <small class="text-muted">(شامل الضريبة)</small>
                                        <?php endif; ?>
                                    </td>
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

<?php
require_once 'includes/footer.php';
?>