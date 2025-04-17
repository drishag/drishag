<?php
// بدء الجلسة
session_start();

// إعدادات المسارات
define('DATA_DIR', __DIR__ . '/data/');
define('TRANSACTIONS_FILE', DATA_DIR . 'transactions.json');
define('CUSTOMERS_FILE', DATA_DIR . 'customers.json');

// إنشاء مجلد البيانات إذا لم يكن موجوداً
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// تهيئة ملفات JSON إذا لم تكن موجودة
$initialData = [
    'transactions' => [],
    'customers' => [],
    'next_id' => 1
];

foreach ([TRANSACTIONS_FILE, CUSTOMERS_FILE] as $file) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($initialData, JSON_PRETTY_PRINT));
    }
}
// تعطيل عرض الأخطاء للزوار (تفعيله أثناء التطوير فقط)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// تعيين اللغة الافتراضية
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ar'; // العربية كلغة افتراضية
}

// معالجة تغيير اللغة
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_SESSION['lang'], time() + (86400 * 30), "/");
}

// تحميل ملف اللغة بشكل آمن
$langFile = __DIR__ . '/lang/' . ($_SESSION['lang'] ?? 'ar') . '.php';

if (!file_exists($langFile)) {
    die("ملف اللغة المطلوب غير موجود: " . $langFile);
}

$lang = include $langFile;

if (!is_array($lang)) {
    die("ملف اللغة يجب أن يعيد مصفوفة صالحة");
}

// تعريف متغير اللغة كمتغير عام
$GLOBALS['lang'] = $lang;

// دالة مساعدة للترجمة الآمنة
function trans($key, $default = '') {
    global $lang;
    return $lang[$key] ?? $default ?? $key;
}
// إعدادات التطبيق
define('VAT_RATE', 0.15); // معدل الضريبة 15%
define('SITE_NAME', 'نظام الفواتير الضريبية|VAT System');
date_default_timezone_set('Asia/Riyadh');

// أنواع الحركات
define('TRANSACTION_TYPE_PURCHASE', 'purchase');
define('TRANSACTION_TYPE_SALE', 'sale');

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$lang = require_once 'lang/' . $_SESSION['lang'] . '.php';

// اللغة الافتراضية
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ar'; // العربية افتراضياً
}

// تغيير اللغة إذا طلب المستخدم ذلك
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = in_array($_GET['lang'], ['ar', 'en']) ? $_GET['lang'] : 'ar';
}

// ملفات اللغة
require_once 'lang/' . $_SESSION['lang'] . '.php';

// تهيئة بيانات التطبيق
if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
    $_SESSION['customers'] = [];
    $_SESSION['next_id'] = 1;
    $_SESSION['next_customer_id'] = 1;
}
?>