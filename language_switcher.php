<?php
function getCurrentLang() {
    return $_SESSION['lang'] ?? 'ar';
}

function switchLanguage() {
    $currentLang = getCurrentLang();
    return $currentLang == 'ar' ? 'en' : 'ar';
}

function getLanguageUrl() {
    return '?lang=' . switchLanguage();
}
?>