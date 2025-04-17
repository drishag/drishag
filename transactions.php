<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'includes/header.php';
require_once 'includes/nav.php';

$transactions = getTransactions();
$summary = calculateVatSummary($transactions);
$customers = getCustomers();
?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card vat-card mb-4">
            <div class="card-header bg-info text-white">
                <h4><i class="bi bi-graph-up"></i> ملخص الضريبة</h4>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>إجمالي المشتريات</h5>
                            <h3><?= number_format($summary['total_purchases'], 2) ?> ر.س</h3>
                            <small class="text-muted">ضريبة: <?= number_format($summary['total_purchase_vat'], 2) ?> ر.س</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>إجمالي المبيعات</h5>
                            <h3><?= number_format($summary['total_sales'], 2) ?> ر.س</h3>
                            <small class="text-muted">ضريبة: <?= number_format($summary['total_sale_vat'], 2) ?> ر.س</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded <?= $summary['vat_due'] >= 0 ? 'bg-success text-white' : 'bg-warning' ?>">
                            <h5>الضريبة المستحقة</h5>
                            <h2><?= number_format(abs($summary['vat_due']), 2) ?> ر.س</h2>
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
        </div>

        <div class="card vat-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-receipt"></i> سجل الفواتير</h4>
                <div>
                    <a href="add_transaction.php" class="btn btn-light me-2"><i class="bi bi-plus-circle"></i> إضافة فاتورة</a>
                    <a href="add_customer.php" class="btn btn-outline-light"><i class="bi bi-person-plus"></i> إضافة عميل</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($transactions)): ?>
                    <div class="alert alert-info">لا توجد فواتير مسجلة بعد</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>التاريخ</th>
                                    <th>العميل</th>
                                    <th>النوع</th>
                                    <th>رقم الفاتورة</th>
                                    <th>المبلغ</th>
                                    <th>الضريبة</th>
                                    <th>الإجمالي</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction):
                                    $customer = null;
                                    // التحقق من وجود customer_id في الحركة قبل البحث
                                    if (isset($transaction['customer_id'])) {
                                        foreach ($customers as $c) {
                                            if ($c['id'] == $transaction['customer_id']) {
                                                $customer = $c;
                                                break;
                                            }
                                        }
                                    }
                                ?>
                                    <tr>
                                        <td><?= $transaction['id'] ?></td>
                                        <td><?= date('Y-m-d', strtotime($transaction['date'])) ?></td>
                                        <td><?= $customer ? $customer['name'] : 'غير معروف' ?></td>
                                        <td>
                                            <?php if ($transaction['type'] == TRANSACTION_TYPE_PURCHASE): ?>
                                                <span class="badge bg-warning text-dark">شراء</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">بيع</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $transaction['invoice_number'] ?? 'غير معروف' ?></td>
                                        <td><?= number_format($transaction['net_amount'], 2) ?> ر.س</td>
                                        <td><?= number_format($transaction['vat_amount'], 2) ?> ر.س</td>
                                        <td><?= number_format($transaction['total_amount'], 2) ?> ر.س</td>
                                        <td>
                                            <a href="view_invoice.php?id=<?= $transaction['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="طباعة">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <a href="edit_transaction.php?id=<?= $transaction['id'] ?>" class="btn btn-sm btn-outline-secondary" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button onclick="confirmDelete(<?= $transaction['id'] ?>)" class="btn btn-sm btn-outline-danger" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
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
<script>
function confirmDelete(id) {
    if (confirm('هل أنت متأكد من رغبتك في حذف هذه الفاتورة؟')) {
        window.location.href = 'delete_transaction.php?id=' + id;
    }
}
</script>