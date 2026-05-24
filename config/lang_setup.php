<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$available_languages = ['en','kh'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $available_languages)) {
    $_SESSION['lang'] = $_GET['lang'];
}
$current_lang = $_SESSION['lang'] ?? 'en';

$lang_file = __DIR__ . "/../app/languages/{$current_lang}.php";

$translations = [];

if (file_exists($lang_file)) {
    $translations = include $lang_file;
}
function __($key)
{
    global $translations;
    return $translations[$key] ?? $key;
}