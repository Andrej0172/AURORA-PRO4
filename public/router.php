<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

$_GET['url'] = ltrim($uri, '/');
include __DIR__ . '/index.php';
