<?php
require_once 'config.php';

if (isset($_SESSION['invoices'])) {
    unset($_SESSION['invoices']);
}

header('Location: invoices.php');
exit;
?>