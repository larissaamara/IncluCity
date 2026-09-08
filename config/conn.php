<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (class_exists(Dotenv\Dotenv::class) && is_file(dirname(__DIR__) . '/.env')) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

/*
 * Conexão centralizada com o banco de dados.
 * Em produção, defina estas credenciais no ambiente do servidor.
 */
$servidor = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'inclucity.mysql.dbaas.com.br';
$porta = (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);
$usuario = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'inclucity';
$senha = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'Senac@n25';
$banco = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'inclucity';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = new mysqli($servidor, $usuario, $senha, $banco, $porta);
    $con->set_charset('utf8mb4');
} catch (mysqli_sql_exception $erro) {
    error_log('Erro na conexão com o banco: ' . $erro->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}
