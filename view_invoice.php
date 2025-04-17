<?php
require_once 'config.php';
require_once 'functions.php';

$id = $_GET['id'] ?? 0;
$transaction = getTransaction($id);

if (!$transaction) {
    die("الفاتورة غير موجودة");
}

// إضافة بيانات العميل للفاتورة
$customer = null;
foreach (getCustomers() as $c) {
    if ($c['id'] == $transaction['customer_id']) {
        $customer = $c;
        break;
    }
}

if ($customer) {
    $transaction['customer_name'] = $customer['name'];
    $transaction['customer_phone'] = $customer['phone'];
    $transaction['customer_address'] = $customer['address'];
    $transaction['customer_tax_number'] = $customer['tax_number'];
}

echo generateInvoiceHtml($transaction);
?>