<?php
function readDatabase($file) {
    if (!file_exists($file)) {
        return ['data' => [], 'next_id' => 1];
    }
    $content = file_get_contents($file);
    return json_decode($content, true);
}

function writeDatabase($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function getNextId($file) {
    $db = readDatabase($file);
    return $db['next_id'] ?? 1;
}

function incrementId($file) {
    $db = readDatabase($file);
    $db['next_id'] = ($db['next_id'] ?? 1) + 1;
    writeDatabase($file, $db);
}
?>