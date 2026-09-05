<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/cadastro.php');
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    definirMensagemFlash('erro', 'Não foi possível continuar', 'Sua sessão expirou. Atualize a página e tente novamente.');
    header('Location: ../pages/cadastro.php');
    exit;
}

require_once dirname(__DIR__) . '/config/conn.php';

function voltarComErro(string $mensagem): never
{
    definirMensagemFlash('erro', 'Revise os dados', $mensagem);
    header('Location: ../pages/cadastro.php');
    exit;
}

function cpfValido(string $cpf): bool
{
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    for ($tamanho = 9; $tamanho < 11; $tamanho++) {
        $soma = 0;

        for ($indice = 0; $indice < $tamanho; $indice++) {
            $soma += (int) $cpf[$indice] * (($tamanho + 1) - $indice);
        }

        $digito = ((10 * $soma) % 11) % 10;

        if ((int) $cpf[$tamanho] !== $digito) {
            return false;
        }
    }

    return true;
}

$nome = trim((string) ($_POST['nome'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$celular = preg_replace('/\D/', '', (string) ($_POST['celular'] ?? '')) ?? '';
$cpf = preg_replace('/\D/', '', (string) ($_POST['cpf'] ?? '')) ?? '';
$senha = (string) ($_POST['senha'] ?? '');
$confirmarSenha = (string) ($_POST['confirmarSenha'] ?? '');
$aceite = $_POST['aceite'] ?? null;

if ($nome === '' || $email === '' || $celular === '' || $cpf === '' || $senha === '' || $confirmarSenha === '') {
    voltarComErro('Preencha todos os campos.');
}

if (strlen($nome) < 3 || strlen($nome) > 150 || !preg_match("/^[\p{L}][\p{L}\s'’-]*$/u", $nome)) {
    voltarComErro('Digite um nome válido.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
    voltarComErro('Digite um e-mail válido.');
}

if (!preg_match('/^[1-9]{2}9?\d{8}$/', $celular)) {
    voltarComErro('Digite um celular válido com DDD.');
}

if (!cpfValido($cpf)) {
    voltarComErro('Digite um CPF válido.');
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.#_-]).{8,}$/', $senha)) {
    voltarComErro('A senha deve ter no mínimo 8 caracteres, com maiúscula, minúscula, número e símbolo.');
}

if ($senha !== $confirmarSenha) {
    voltarComErro('As senhas não coincidem.');
}

if ($aceite !== '1') {
    voltarComErro('Você precisa aceitar os Termos de Uso e a Política de Privacidade.');
}

$stmt = $con->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    voltarComErro('Este e-mail já está cadastrado.');
}

$stmt->close();

// Remove a máscara também dos CPFs antigos antes de comparar.
$stmt = $con->prepare("SELECT id FROM usuarios WHERE REPLACE(REPLACE(cpf, '.', ''), '-', '') = ? LIMIT 1");
$stmt->bind_param('s', $cpf);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    voltarComErro('Este CPF já está cadastrado.');
}

$stmt->close();
$senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $con->prepare('INSERT INTO usuarios (nome, email, celular, cpf, senha) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $nome, $email, $celular, $cpf, $senhaCriptografada);

try {
    $stmt->execute();
} catch (mysqli_sql_exception $erro) {
    error_log('Erro ao cadastrar usuário: ' . $erro->getMessage());

    if ($erro->getCode() === 1062) {
        voltarComErro('O e-mail ou CPF informado já está cadastrado.');
    }

    voltarComErro('Não foi possível realizar o cadastro. Tente novamente.');
}

$stmt->close();
$con->close();

definirMensagemFlash(
    'sucesso',
    'Cadastro concluído!',
    'Seu e-mail foi cadastrado com sucesso. Agora você já pode entrar na sua conta.'
);
header('Location: ../pages/login.php');
exit;
