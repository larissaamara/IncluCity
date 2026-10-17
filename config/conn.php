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
// Uma senha vazia e valida no XAMPP e deve ser preservada.
$lerConfiguracaoBanco = static function (string $nome, string $padrao): string {
    $valor = $_ENV[$nome] ?? getenv($nome);
    return $valor === false ? $padrao : (string) $valor;
};

$servidor = $lerConfiguracaoBanco('DB_HOST', '127.0.0.1');
$porta = (int) $lerConfiguracaoBanco('DB_PORT', '3306');
$usuario = $lerConfiguracaoBanco('DB_USER', 'root');
$senha = $lerConfiguracaoBanco('DB_PASSWORD', '');
$banco = $lerConfiguracaoBanco('DB_NAME', 'inclucity_db');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = new mysqli($servidor, $usuario, $senha, $banco, $porta);
    $con->set_charset('utf8mb4');
} catch (mysqli_sql_exception $erro) {
    error_log('Erro na conexão com o banco: ' . $erro->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}
