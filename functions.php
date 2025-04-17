<?php
require_once 'config.php';

// إضافة عميل جديد
function addCustomer($name, $phone, $address, $taxNumber) {
    $customer = [
        'id' => $_SESSION['next_customer_id']++,
        'name' => $name,
        'phone' => $phone,
        'address' => $address,
        'tax_number' => $taxNumber
    ];
    
    $_SESSION['customers'][] = $customer;
    return $customer['id'];
}

// جلب جميع العملاء
function getCustomers() {
    return $_SESSION['customers'];
}

// إضافة حركة/فاتورة جديدة
function addTransaction($type, $customerId, $amount, $description, $date, $isTaxIncluded = false, $items = []) {
    $vatAmount = 0;
    $netAmount = $amount;
    
    if (!empty($items)) {
        $netAmount = 0;
        $vatAmount = 0;
        foreach ($items as $item) {
            $netAmount += $item['net_amount'];
            $vatAmount += $item['vat_amount'];
        }
        $amount = $netAmount + $vatAmount;
    } elseif ($isTaxIncluded) {
        $vatAmount = $amount * VAT_RATE / (1 + VAT_RATE);
        $netAmount = $amount - $vatAmount;
    } else {
        $vatAmount = $amount * VAT_RATE;
    }
    
    $transaction = [
        'id' => $_SESSION['next_id']++,
        'type' => $type,
        'customer_id' => $customerId,
        'date' => $date,
        'amount' => $amount,
        'net_amount' => $netAmount,
        'vat_amount' => $vatAmount,
        'total_amount' => $netAmount + $vatAmount,
        'description' => $description,
        'is_tax_included' => $isTaxIncluded,
        'items' => $items,
        'created_at' => date('Y-m-d H:i:s'),
        'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($_SESSION['next_id'], 4, '0', STR_PAD_LEFT)
    ];
    
    $_SESSION['transactions'][] = $transaction;
    return $transaction;
}

// جلب حركة/فاتورة معينة
function getTransaction($id) {
    foreach ($_SESSION['transactions'] as $transaction) {
        if ($transaction['id'] == $id) {
            // التأكد من وجود مصفوفة items
            if (!isset($transaction['items'])) {
                $transaction['items'] = [
                    [
                        'description' => $transaction['description'],
                        'quantity' => 1,
                        'price' => $transaction['net_amount'],
                        'net_amount' => $transaction['net_amount'],
                        'vat_amount' => $transaction['vat_amount'],
                        'total_amount' => $transaction['total_amount'],
                        'is_tax_included' => $transaction['is_tax_included']
                    ]
                ];
            }
            return $transaction;
        }
    }
    return null;
}

// جلب جميع الحركات/الفواتير
function getTransactions($filters = []) {
    $transactions = $_SESSION['transactions'];
    
    if (!empty($filters)) {
        $filtered = [];
        foreach ($transactions as $transaction) {
            $match = true;
            
            if (isset($filters['type']) && $transaction['type'] != $filters['type']) {
                $match = false;
            }
            
            if (isset($filters['month'])) {
                $transactionMonth = date('m', strtotime($transaction['date']));
                if ($transactionMonth != $filters['month']) {
                    $match = false;
                }
            }
            
            if (isset($filters['year'])) {
                $transactionYear = date('Y', strtotime($transaction['date']));
                if ($transactionYear != $filters['year']) {
                    $match = false;
                }
            }
            
            if (isset($filters['customer_id']) && $transaction['customer_id'] != $filters['customer_id']) {
                $match = false;
            }
            
            if ($match) {
                $filtered[] = $transaction;
            }
        }
        return $filtered;
    }
    
    return $transactions;
}

// حساب إجماليات الضريبة
function calculateVatSummary($transactions) {
    $summary = [
        'total_purchases' => 0,
        'total_purchase_vat' => 0,
        'total_sales' => 0,
        'total_sale_vat' => 0,
        'vat_due' => 0
    ];
    
    foreach ($transactions as $transaction) {
        if ($transaction['type'] == TRANSACTION_TYPE_PURCHASE) {
            $summary['total_purchases'] += $transaction['net_amount'];
            $summary['total_purchase_vat'] += $transaction['vat_amount'];
        } else {
            $summary['total_sales'] += $transaction['net_amount'];
            $summary['total_sale_vat'] += $transaction['vat_amount'];
        }
    }
    
    $summary['vat_due'] = $summary['total_sale_vat'] - $summary['total_purchase_vat'];
    
    return $summary;
}

// دالة لطباعة الفاتورة
function generateInvoiceHtml($invoice) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title>فاتورة ضريبية #<?= $invoice['invoice_number'] ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
            .invoice-container { max-width: 800px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
            .header { text-align: center; margin-bottom: 20px; }
            .invoice-title { font-size: 24px; font-weight: bold; }
            .invoice-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
            .customer-info, .invoice-details { width: 48%; }
            .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
            .table th { background-color: #f2f2f2; }
            .total-row { font-weight: bold; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; }
            @media print {
                .no-print { display: none; }
                body { padding: 0; }
            }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            <div class="header">
                <div class="invoice-title">فاتورة ضريبية</div>
                <div>نظام الفواتير الضريبية - المملكة العربية السعودية</div>
            </div>
            
            <div class="invoice-info">
                <div class="customer-info">
                    <h3>بيانات العميل:</h3>
                    <p><strong>اسم العميل:</strong> <?= $invoice['customer_name'] ?></p>
                    <p><strong>رقم الهاتف:</strong> <?= $invoice['customer_phone'] ?></p>
                    <p><strong>الرقم الضريبي:</strong> <?= $invoice['customer_tax_number'] ?></p>
                </div>
                
                <div class="invoice-details">
                    <h3>بيانات الفاتورة:</h3>
                    <p><strong>رقم الفاتورة:</strong> <?= $invoice['invoice_number'] ?></p>
                    <p><strong>تاريخ الفاتورة:</strong> <?= date('Y-m-d', strtotime($invoice['date'])) ?></p>
                    <p><strong>نوع الفاتورة:</strong> <?= $invoice['type'] == TRANSACTION_TYPE_SALE ? 'مبيعات' : 'مشتريات' ?></p>
                </div>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الوصف</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الضريبة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoice['items'])): ?>
                        <?php foreach ($invoice['items'] as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $item['description'] ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'], 2) ?> ر.س</td>
                            <td><?= $item['is_tax_included'] ? 'شامل' : 'غير شامل' ?></td>
                            <td><?= number_format($item['total_amount'], 2) ?> ر.س</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td>1</td>
                            <td><?= $invoice['description'] ?></td>
                            <td>1</td>
                            <td><?= number_format($invoice['net_amount'], 2) ?> ر.س</td>
                            <td><?= $invoice['is_tax_included'] ? 'شامل' : 'غير شامل' ?></td>
                            <td><?= number_format($invoice['total_amount'], 2) ?> ر.س</td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: left;">الإجمالي قبل الضريبة:</td>
                        <td><?= number_format($invoice['net_amount'], 2) ?> ر.س</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: left;">الضريبة (<?= (VAT_RATE * 100) ?>%):</td>
                        <td><?= number_format($invoice['vat_amount'], 2) ?> ر.س</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: left;">الإجمالي النهائي:</td>
                        <td><?= number_format($invoice['total_amount'], 2) ?> ر.س</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="footer">
                <p>شكراً لتعاملكم معنا</p>
                <p>للاستفسار يرجى الاتصال على: ٠٥٠٠٠٠٠٠٠٠</p>
                <button class="no-print" onclick="window.print()">طباعة الفاتورة</button>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
// حذف حركة/فاتورة
function deleteTransaction($id) {
    foreach ($_SESSION['transactions'] as $key => $transaction) {
        if ($transaction['id'] == $id) {
            unset($_SESSION['transactions'][$key]);
            // إعادة ترقيم المفاتيح بعد الحذف
            $_SESSION['transactions'] = array_values($_SESSION['transactions']);
            return true;
        }
    }
    return false;
}
?>