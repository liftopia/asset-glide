<?php
date_default_timezone_set('America/Chicago');

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($uri === '/favicon.ico' || ($uri !== '/' && file_exists(__DIR__ . '/../public' . $uri))) {
    return false;
}

require_once __DIR__ . '/../public/index.php';