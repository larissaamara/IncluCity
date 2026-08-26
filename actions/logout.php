<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/session.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], $parametros['secure'], $parametros['httponly']);
}

session_destroy();
header('Location: ../pages/login.php');
exit;
