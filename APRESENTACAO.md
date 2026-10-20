# Preparação para a apresentação — 22/10/2026

## Atualizar o projeto de cada integrante

```powershell
git switch develop
git pull origin develop
composer install
```

Cada integrante deve configurar seu próprio `.env`, conforme o `.env.example`. Senhas e configurações locais não são enviadas ao GitHub. Para um banco existente, siga as migrações descritas no README antes de testar o mapa.

## Abrir o site

1. Ligue Apache e MySQL no XAMPP.
2. Abra `http://localhost/IncluCity/`. A pasta do projeto precisa estar em `C:/xampp/htdocs/IncluCity` ou vinculada a esse caminho.
3. No VS Code, `Ctrl+Shift+B` executa a tarefa de abrir o site no navegador normal.

O atalho `Shift+F6` é uma configuração pessoal do VS Code. Para configurá-lo em outro computador, abra **Preferências: Abrir Atalhos de Teclado (JSON)** na paleta de comandos e acrescente esta entrada ao array existente:

```json
{
  "key": "shift+f6",
  "command": "workbench.action.tasks.runTask",
  "args": "IncluCity: abrir no navegador normal (login Google)",
  "when": "workspaceFolderCount != 0"
}
```

## Roteiro de demonstração

1. Apresente a página inicial e a página Como funciona.
2. Escolha um dos filtros na página inicial: o mapa abre com a necessidade selecionada.
3. Pesquise um local, combine filtros e use Limpar filtros.
4. Cadastre uma conta com e-mail, celular, CPF válido e senha forte. O cadastro inicia a sessão.
5. No mapa, abra Contribuir e sugira um local com endereço, coordenadas, categoria, recursos e pelo menos uma foto. Ele fica pendente.
6. Em outra sessão, entre com uma conta administradora e aprove a solicitação.
7. Atualize o mapa para mostrar o local aprovado; abra suas avaliações e envie uma nota e um comentário.
8. Saia da conta e confirme que a área do usuário exige login.

## Conferir antes de apresentar

- Tenha uma conta de usuário e uma conta administradora disponíveis; não compartilhe suas senhas no repositório.
- O banco local revisado estava sem locais aprovados. Prepare os locais com antecedência. O script opcional `database/locais_demonstracao.sql` contém dados para demonstração; revise o conteúdo antes de importar no banco escolhido.
- O envio de e-mails não estava configurado no ambiente revisado: faltava `MAIL_PASSWORD`. Configure o SMTP e teste o recebimento. A alternativa local descrita no README é `APP_ENV=development` e `RECOVERY_DELIVERY=screen`.
- O login Google depende do cliente OAuth, do endereço de retorno autorizado e da conta Google. Faça um login real no navegador normal antes da apresentação; o teste automatizado não conclui essa autenticação externa.
- Bootstrap, ícones, mapa, geocodificação, VLibras e Sienna dependem da internet. Teste a rede do local da apresentação.

## Revisão de 19/10/2026

O menu preserva a identidade azul e amarela com texto escuro no destaque amarelo, foco de teclado visível e contraste também no menu de celular. Os formulários se ajustam a telas menores e ao zoom. Os filtros da página inicial levam ao mapa. A ausência dos widgets opcionais de acessibilidade não interrompe o JavaScript da página.

As páginas inicial, Como funciona, login, cadastro, recuperação inicial e mapa foram verificadas no Edge. Também foram conferidos menu e largura em tela de 375 pixels, filtros por URL e ausência dos widgets externos. Os 30 arquivos PHP e os oito arquivos JavaScript passaram nas verificações de sintaxe.

Com uma conta temporária no banco local, passaram os testes de cadastro, área do usuário, login pelo navegador, envio de local com foto, ocultação de pendentes, aprovação administrativa, publicação no mapa, criação e consulta de avaliações e logout. Também foram verificadas a rejeição de login sem token CSRF e a restrição da API administrativa para usuários comuns. A conta, o local, a avaliação e a foto de teste foram removidos ao final. Esses testes não validam o envio de e-mail nem a conclusão do login Google.
