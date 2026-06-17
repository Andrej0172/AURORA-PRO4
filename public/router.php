<?php
// Router voor PHP built-in development server (vervangt .htaccess)
// Stuurt alle requests die niet naar een bestaand bestand gaan naar index.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// Laat bestaande bestanden (CSS, JS, afbeeldingen) direct serveren
if ($uri !== '/' && is_file($file)) {
    return false;
}

// Alle andere URLs via index.php afhandelen met MVC-router
$_GET['url'] = ltrim($uri, '/');
include __DIR__ . '/index.php';
