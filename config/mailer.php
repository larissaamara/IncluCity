<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (class_exists(Dotenv\Dotenv::class) && is_file(dirname(__DIR__) . '/.env')) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

function variavelEmail(string $nome, string $padrao = ''): string
{
    $valor = $_ENV[$nome] ?? getenv($nome);
    return is_string($valor) && trim($valor) !== '' ? trim($valor) : $padrao;
}

function criarMailer(): PHPMailer
{
    $usuario = variavelEmail('MAIL_USERNAME');
    $senha = variavelEmail('MAIL_PASSWORD');
    $remetente = variavelEmail('MAIL_FROM', $usuario);

    if ($usuario === '' || $senha === '' || $remetente === '') {
        throw new RuntimeException('As credenciais SMTP não foram configuradas.');
    }

    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = variavelEmail('MAIL_HOST', 'smtp.gmail.com');
    $mailer->Port = (int) variavelEmail('MAIL_PORT', '587');
    $mailer->SMTPAuth = true;
    $mailer->Username = $usuario;
    $mailer->Password = $senha;
    $mailer->Timeout = 15;
    $mailer->CharSet = PHPMailer::CHARSET_UTF8;

    $criptografia = strtolower(variavelEmail('MAIL_ENCRYPTION', 'tls'));
    if ($criptografia === 'ssl' || $criptografia === 'smtps') {
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($criptografia === 'tls' || $criptografia === 'starttls') {
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mailer->SMTPAutoTLS = false;
        $mailer->SMTPSecure = '';
    }

    $mailer->setFrom($remetente, variavelEmail('MAIL_FROM_NAME', 'IncluCity'));
    $mailer->isHTML(true);
    return $mailer;
}

function enviarEmailBoasVindas(string $email, string $nome): void
{
    $mailer = criarMailer();
    $mailer->addAddress($email, $nome);
    $mailer->Subject = 'Bem-vindo ao IncluCity';

    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $mailer->Body = <<<HTML
<!doctype html>
<html lang="pt-BR">
<body style="margin:0;padding:24px;background:#eef4f2;font-family:Arial,sans-serif;color:#173f35">
  <div style="max-width:600px;margin:auto;padding:28px;border-radius:14px;background:#ffffff">
    <h1 style="margin-top:0;color:#0f6b55">Bem-vindo ao IncluCity!</h1>
    <p>Olá, {$nomeSeguro}.</p>
    <p>Seu e-mail foi cadastrado com sucesso. Agora você pode consultar locais acessíveis, publicar avaliações e contribuir com novos locais.</p>
    <p style="margin-bottom:0">Obrigado por ajudar a construir uma cidade mais inclusiva.</p>
  </div>
</body>
</html>
HTML;
    $mailer->AltBody = "Olá, {$nome}. Seu e-mail foi cadastrado com sucesso no IncluCity. Obrigado por ajudar a construir uma cidade mais inclusiva.";
    $mailer->send();
}

function enviarCodigoRecuperacao(string $email, string $codigo): void
{
    $mailer = criarMailer();
    $mailer->addAddress($email);
    $mailer->Subject = 'Código para redefinir sua senha no IncluCity';

    $codigoSeguro = htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');
    $mailer->Body = <<<HTML
<!doctype html>
<html lang="pt-BR">
<body style="margin:0;padding:24px;background:#eef4f2;font-family:Arial,sans-serif;color:#173f35">
  <div style="max-width:600px;margin:auto;padding:28px;border-radius:14px;background:#ffffff">
    <h1 style="margin-top:0;color:#0f6b55">Redefinição de senha</h1>
    <p>Use o código abaixo para continuar:</p>
    <p style="margin:24px 0;padding:16px;border-radius:10px;background:#e7f4ef;font-size:32px;font-weight:bold;letter-spacing:8px;text-align:center">{$codigoSeguro}</p>
    <p>O código expira em 10 minutos e pode ser usado somente neste processo de recuperação.</p>
    <p style="margin-bottom:0;color:#60736e">Se você não solicitou uma nova senha, ignore este e-mail.</p>
  </div>
</body>
</html>
HTML;
    $mailer->AltBody = "Seu código de recuperação do IncluCity é {$codigo}. Ele expira em 10 minutos. Se você não solicitou uma nova senha, ignore este e-mail.";
    $mailer->send();
}
