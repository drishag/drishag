<?php
require_once 'config.php';
require_once 'functions.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    if (deleteTransaction($id)) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'تم حذف الفاتورة بنجاح'
        ];
    } else {
        $_SESSION['message'] = [
            'type' => 'danger',
            'text' => 'حدث خطأ أثناء محاولة حذف الفاتورة'
        ];
    }
}

header('Location: transactions.php');
exit;
?>