<?php
// Database-instellingen
define('DB_HOST', 'localhost');
define('DB_NAME', 'AuroraDb');            // Database voor Aurora Theater
define('DB_NAME_ACCOUNTS', 'AuroraAccountsDb'); // Aparte database voor accounts en reserveringen
define('DB_USER', 'root');
define('DB_PASS', '');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$basePath = preg_replace('#/public$#', '', $scriptDir);
if ($basePath === '/' || $basePath === '.') {
	$basePath = '';
}
define('URLROOT', $scheme . '://' . $host . $basePath . '/');
define('APPROOT', dirname(dirname(__FILE__))); // absoluut pad naar de app-map

// Zet op true om de onderhoudspagina voor iedereen te tonen
define('ONDERHOUD', false);
