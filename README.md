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
MAIL_FROM=no-reply@seudominio.com
```

O servidor PHP precisa estar configurado para enviar e-mail. Para desenvolvimento local sem SMTP, use `APP_ENV=development` e `RECOVERY_DELIVERY=screen`; nessa combinação o código aparece apenas na página local de recuperação.

### Abrir pelo VS Code

Não use a extensão **Live Server** nem abra diretamente um arquivo `.php`: esses modos não executam PHP e exibem o código-fonte na tela.

Com o MySQL do XAMPP ligado, abra a pasta inteira no VS Code e pressione `F5`. A configuração em `.vscode` inicia o servidor PHP e abre `http://localhost:8000` no navegador. Para iniciar apenas o servidor, use `Ctrl+Shift+B`.

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
