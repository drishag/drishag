<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'includes/header.php';
require_once 'includes/nav.php';

$currentYear = date('Y');
$years = range($currentYear - 2, $currentYear + 1);
$months = [
    '01' => 'يناير',
    '02' => 'فبراير',
    '03' => 'مارس',
    '04' => 'أبريل',
    '05' => 'مايو',
    '06' => 'يونيو',
    '07' => 'يوليو',
    '08' => 'أغسطس',
    '09' => 'سبتمبر',
    '10' => 'أكتوبر',
    '11' => 'نوفمبر',
    '12' => 'ديسمبر'
];

$selectedYear = $_GET['year'] ?? $currentYear;
$selectedMonth = $_GET['month'] ?? null;
$selectedCustomer = $_GET['customer_id'] ?? null;

$filters = ['year' => $selectedYear];
if ($selectedMonth) {
    $filters['month'] = $selectedMonth;
}
if ($selectedCustomer) {
    $filters['customer_id'] = $selectedCustomer;
}

$transactions = getTransactions($filters);
$summary = calculateVatSummary($transactions);
$customers = getCustomers();
?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card vat-card">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-file-earmark-bar-graph"></i> التقارير الضريبية</h4>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="year" class="form-label">السنة</label>
                        <select class="form-select" id="year" name="year">
                            <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>><?= $year ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="month" class="form-label">الشهر</label>
                        <select class="form-select" id="month" name="month">
                            <option value="">كل الأشهر</option>
                            <?php foreach ($months as $key => $month): ?>
                                <option value="<?= $key ?>" <?= $key == $selectedMonth ? 'selected' : '' ?>><?= $month ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="customer_id" class="form-label">العميل</label>
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">كل العملاء</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>" <?= $customer['id'] == $selectedCustomer ? 'selected' : '' ?>>
                                    <?= $customer['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2"><i class="bi bi-filter"></i> تصفية</button>
                        <a href="print_report.php?<?= http_build_query($_GET) ?>" class="btn btn-success" target="_blank">
                            <i class="bi bi-printer"></i> طباعة
                        </a>
                    </div>
                </form>
                
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5>ملخص الضريبة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>المشتريات:</h6>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>إجمالي المشتريات</th>
                                        <td><?= number_format($summary['total_purchases'], 2) ?> ر.س</td>
                                    </tr>
                                    <tr>
                                        <th>ضريبة المشتريات (مدخلة)</th>
                                        <td><?= number_format($summary['total_purchase_vat'], 2) ?> ر.س</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>المبيعات:</h6>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>إجمالي المبيعات</th>
                                        <td><?= number_format($summary['total_sales'], 2) ?> ر.س</td>
                                    </tr>
                                    <tr>
                                        <th>ضريبة المبيعات (مخرجة)</th>
                                        <td><?= number_format($summary['total_sale_vat'], 2) ?> ر.س</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="alert <?= $summary['vat_due'] >= 0 ? 'alert-success' : 'alert-warning' ?>">
                            <h5 class="alert-heading">صافي الضريبة المستحقة:</h5>
                            <p class="mb-0">
                                <strong><?= number_format(abs($summary['vat_due']), 2) ?> ر.س</strong>
                                <?php if ($summary['vat_due'] >= 0): ?>
                                    (مستحقة للدائنية الضريبية)
                                <?php else: ?>
                                    (مستحقة للذمم الضريبية)
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($transactions)): ?>
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
                                <th>طباعة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): 
                                $customer = null;
                                foreach ($customers as $c) {
                                    if ($c['id'] == $transaction['customer_id']) {
                                        $customer = $c;
                                        break;
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
                                <td><?= $transaction['invoice_number'] ?></td>
                                <td><?= number_format($transaction['net_amount'], 2) ?> ر.س</td>
                                <td><?= number_format($transaction['vat_amount'], 2) ?> ر.س</td>
                                <td><?= number_format($transaction['total_amount'], 2) ?> ر.س</td>
                                <td>
                                    <a href="view_invoice.php?id=<?= $transaction['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-info">لا توجد حركات مسجلة للفترة المحددة</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>