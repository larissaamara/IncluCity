# IncluCity

Projeto web colaborativo para consulta e cadastro de informações sobre acessibilidade urbana.

## Tecnologias

- PHP 8.2 e MariaDB/MySQL;
- Composer e `vlucas/phpdotenv`;
- HTML, CSS e JavaScript;
- Bootstrap, Font Awesome e Leaflet por CDN;
- OpenStreetMap/Nominatim para mapas e geocodificação;
- VLibras e Sienna para recursos de acessibilidade.

## Configuração local

1. Coloque o projeto dentro do `htdocs` do XAMPP.
2. Execute `composer install`.
3. Copie `.env.example` para `.env` e ajuste somente as credenciais locais.
4. Em uma instalação nova, importe `database/inclucity_db.sql` pelo phpMyAdmin.
5. Para atualizar um banco criado por uma versão antiga, importe `database/estrutura_atual_migration.sql` sem apagar as tabelas existentes.
6. Para preparar a apresentação, importe `database/locais_demonstracao.sql`; o arquivo pode ser executado novamente sem duplicar os registros.
7. Inicie Apache e MySQL e abra a pasta do projeto pelo `localhost`.

O banco principal inclui `usuarios`, `locais` e `local_fotos`. A migração de atualização preserva os registros antigos, converte os campos usados pelo mapa e garante que exista um administrador quando já houver usuários cadastrados.

O arquivo `.env` não deve ser enviado ao Git. O Apache também bloqueia seu acesso direto, assim como o dump SQL e os arquivos do Composer.

## Recuperação de senha

Em produção, use no `.env`:

```dotenv
APP_ENV=production
RECOVERY_DELIVERY=mail
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=cabrabom.leandro@gmail.com
MAIL_PASSWORD=sua_senha_de_app
MAIL_FROM=cabrabom.leandro@gmail.com
MAIL_FROM_NAME=IncluCity
```

O envio usa PHPMailer com SMTP autenticado. Para uma conta Gmail, ative a verificação em duas etapas e use uma senha de app em `MAIL_PASSWORD`; não use a senha normal da conta. Para desenvolvimento local sem SMTP, use `APP_ENV=development` e `RECOVERY_DELIVERY=screen`; nessa combinação o código aparece apenas na página local de recuperação. O e-mail de boas-vindas continua dependendo do SMTP configurado.

### Abrir pelo VS Code

Não use a extensão **Live Server** nem abra diretamente um arquivo `.php`: esses modos não executam PHP e exibem o código-fonte na tela.

Com Apache e MySQL do XAMPP ligados, abra `http://localhost/IncluCity/` no navegador normal. A tarefa `Ctrl+Shift+B` abre esse endereço. O projeto precisa estar em `C:/xampp/htdocs/IncluCity` ou vinculado a esse caminho por um vínculo de diretório.

Use `F5` apenas para depurar JavaScript. Essa opção abre o Edge com o depurador conectado; o Google pode recusar o login nessa janela com a mensagem “Esse navegador ou app pode não ser seguro”. Para testar o login Google, comece novamente pelo site em uma janela normal do Chrome ou Edge, fora do depurador, sem copiar a URL de autorização da janela anterior.

A depuração usa o Microsoft Edge e o site servido pelo Apache. O Apache aplica as proteções do `.htaccess`.

Alternativa com servidor separado na porta 8000 pelo terminal PowerShell:

```powershell
& C:/xampp/php/php.exe -S 127.0.0.1:8000 -t . router.php
```

Para o Apache, use `APP_URL=http://localhost/IncluCity` e `OAUTH_REDIRECT_URI=http://localhost/IncluCity/oauth.php` no `.env`. Cadastre esse mesmo retorno no cliente OAuth do Google. Se optar pelo servidor separado na porta 8000, ajuste ambas as URLs e o retorno autorizado no Google. O servidor separado usa `router.php` porque não interpreta `.htaccess`.

## Funcionalidades

- cadastro com validação de nome, e-mail, celular, CPF e senha forte;
- login por e-mail ou CPF, sessão segura e logout;
- proteção CSRF e limite de tentativas nos fluxos sensíveis;
- recuperação e alteração de senha;
- mapa com filtros e formulário colaborativo acessado por menu hambúrguer;
- solicitações com endereço, coordenadas, categorias, recursos, fotos e informações adicionais;
- moderação por status; somente solicitações aprovadas aparecem publicamente no mapa;
- painel autenticado com dados e publicações do usuário.

## Estrutura principal

```text
assets/                 CSS, JavaScript e imagens
actions/                Processamento de formulários e logout
api/                    Endpoints JSON
database/               Estrutura e scripts do banco
pages/                  Páginas acessadas pelo navegador
config/conn.php         Conexão via variáveis de ambiente
config/session.php      Sessão e proteção CSRF
index.php               Entrada da aplicação
```

## Segurança

As consultas com dados externos usam prepared statements, senhas usam `password_hash()`/`password_verify()` e saídas dinâmicas são escapadas. Antes de publicar, use HTTPS, credenciais exclusivas, SMTP autenticado e `APP_ENV=production`.
