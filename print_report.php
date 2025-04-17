<?php
require_once 'config.php';
require_once 'functions.php';

$currentYear = date('Y');
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

$reportTitle = "تقرير ضريبي";
if ($selectedMonth) {
    $reportTitle .= " لشهر " . $months[$selectedMonth];
}
$reportTitle .= " لعام " . $selectedYear;

if ($selectedCustomer) {
    foreach ($customers as $customer) {
        if ($customer['id'] == $selectedCustomer) {
            $reportTitle .= " - العميل: " . $customer['name'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= $reportTitle ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .report-container { max-width: 100%; }
        .header { text-align: center; margin-bottom: 20px; }
        .report-title { font-size: 24px; font-weight: bold; }
        .report-period { font-size: 18px; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        .table th { background-color: #f2f2f2; }
        .summary { margin-bottom: 30px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        .summary-table th { background-color: #f2f2f2; }
        .no-print { display: none; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="header">
            <div class="report-title"><?= $reportTitle ?></div>
            <div class="report-period">تاريخ التقرير: <?= date('Y-m-d') ?></div>
        </div>
        
        <div class="summary">
            <h3>ملخص الضريبة:</h3>
            <table class="summary-table">
                <tr>
                    <th>إجمالي المشتريات</th>
                    <td><?= number_format($summary['total_purchases'], 2) ?> ر.س</td>
                    <th>ضريبة المشتريات</th>
                    <td><?= number_format($summary['total_purchase_vat'], 2) ?> ر.س</td>
                </tr>
                <tr>
                    <th>إجمالي المبيعات</th>
                    <td><?= number_format($summary['total_sales'], 2) ?> ر.س</td>
                    <th>ضريبة المبيعات</th>
                    <td><?= number_format($summary['total_sale_vat'], 2) ?> ر.س</td>
                </tr>
                <tr>
                    <th colspan="3">صافي الضريبة المستحقة</th>
                    <td><?= number_format($summary['vat_due'], 2) ?> ر.س</td>
                </tr>
            </table>
        </div>
        
        <h3>تفاصيل الحركات:</h3>
        <table class="table">
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
                    <td><?= $transaction['type'] == TRANSACTION_TYPE_SALE ? 'بيع' : 'شراء' ?></td>
                    <td><?= $transaction['invoice_number'] ?></td>
                    <td><?= number_format($transaction['net_amount'], 2) ?> ر.س</td>
                    <td><?= number_format($transaction['vat_amount'], 2) ?> ر.س</td>
                    <td><?= number_format($transaction['total_amount'], 2) ?> ر.س</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="footer no-print" style="text-align: center; margin-top: 30px;">
            <button onclick="window.print()" class="btn btn-primary">طباعة التقرير</button>
            <button onclick="window.close()" class="btn btn-secondary">إغلاق</button>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>