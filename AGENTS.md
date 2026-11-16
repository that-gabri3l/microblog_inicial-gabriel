# Referência para desenvolvimento

## Visão geral

Microblog em PHP, com páginas PHP tradicionais que misturam processamento de formulário e HTML. O sistema usa MySQL/MariaDB via PDO, Composer para autoload e Bootstrap na interface. O projeto está organizado para desenvolvimento local com XAMPP.

Use esta referência como contexto inicial; antes de alterar algo, confira o código atual e preserve mudanças preexistentes.

## Ambiente e execução local

- Requer PHP 8.1 ou superior: o código usa enums, tipos `mixed` e propriedades tipadas.
- Requer Apache/PHP, MySQL ou MariaDB e a extensão PDO para MySQL.
- O autoload PSR-4 do Composer associa `Microblog\` à pasta `src/`.
- A conexão está configurada em `src/Database/ConexaoBD.php` para `localhost`, usuário `root`, senha vazia e banco `microblog_2026`. Ajuste a configuração localmente se seu ambiente for diferente; não versione credenciais.
- Para preparar o banco, crie/selecione `microblog_2026` e importe `_materiais/microblog_2026_completo.sql`. `_materiais/microblog_2026_inicial.sql` é uma alternativa com notícias sem os dados iniciais completos.
- A aplicação e os caminhos de imagens esperam que o projeto seja servido pela raiz do site, como em `http://localhost/microblog_inicial-gabriel/`.
- `vendor/` é dependência gerada e está ignorada pelo Git. Use `composer install` se precisar restaurar as dependências; rode `composer dump-autoload` se alterar o mapeamento/autoload.
- Não há suíte de testes nem scripts de lint/build configurados no Composer. Para verificar sintaxe PHP em arquivos alterados, use `php -l caminho/do/arquivo.php`; valide fluxos de interface no ambiente web com o banco configurado.

## Estrutura principal

- `src/Auth/`: autenticação e controle de sessão (`ControleDeAcesso`).
- `src/Database/`: conexão PDO (`ConexaoBD`).
- `src/Enums/`: valores fechados de domínio (`TipoUsuario`, `Destaque`).
- `src/Helpers/`: normalização, escape, upload, logging e validações (`Utils`, `Validacoes`).
- `src/Models/`: modelos de domínio (`Usuario`, `Categoria`, `Noticia`).
- `src/Services/`: consultas e persistência de usuários, categorias e notícias.
- `admin/`: páginas de painel e formulários de administração.
- `includes/`: cabeçalhos, rodapés e componentes PHP compartilhados.
- `images/`: imagens usadas pelo site e destino dos uploads de notícias.
- `_materiais/`: dumps SQL e material de referência; não é código executado pela aplicação.
- `logs/erros.log`: destino do log definido em `Utils::registrarLog`.

## Convenções e padrões existentes

- Use namespaces `Microblog\...` correspondentes às pastas em `src/`; classes em `PascalCase`, métodos e variáveis em `camelCase`, com nomes em português.
- Os arquivos das classes acompanham o nome da classe, por exemplo `NoticiaServico.php`. As páginas administrativas seguem nomes descritivos com hífens, como `noticia-insere.php`.
- Modelos são classes finais, com propriedades privadas tipadas, construtor e getters. Enums persistem seus valores escalares com `->value`; converta valores validados usando `Enum::from()` ou `Enum::tryFrom()`.
- Serviços recebem modelos nos métodos de escrita e usam consultas PDO parametrizadas. Mantenha SQL e persistência nos serviços, não nas páginas de formulário.
- Páginas normalmente inicializam variáveis de mensagem e formulário, processam `POST`, normalizam e validam campos, chamam o serviço e redirecionam com `header()` seguido de `exit`.
- Erros de validação são comunicados ao usuário; erros inesperados ou de persistência devem ser registrados por `Utils::registrarLog()` e exibidos com mensagem genérica. Não deixe blocos `catch` vazios.
- Normalize entrada com `Utils::normalizarEntrada()`, valide com os métodos específicos de `Validacoes` e escape valores ao imprimir HTML com `Utils::escaparHtml()`. A normalização de texto remove espaços das extremidades, mas preserva o texto; não substitua o escape de saída por transformação da entrada.
- Use `htmlspecialchars` via `escaparHtml()` para conteúdo em HTML. Texto de notícias pode conter conteúdo multilinha; mantenha a forma de apresentação adequada ao contexto e nunca imprima entrada não escapada.
- Upload de notícia usa `Utils::upload()`: imagens JPEG, GIF, PNG ou WebP, limite de 2 MB, nome de até 60 caracteres e gravação em `images/`. Formulários com upload precisam de `enctype="multipart/form-data"`.
- A interface usa Bootstrap e classes já presentes nos templates. Reutilize cabeçalho/rodapé de `includes/` em vez de duplicar a estrutura comum.

## Domínio e validações

- Usuários têm `id`, `nome`, `email`, senha com hash e tipo (`admin` ou `editor`). Senhas são tratadas com `password_hash`/`password_verify`; na edição, campo vazio preserva o hash existente.
- Categorias têm `id` e `nome`.
- Notícias têm título, texto, resumo, nome da imagem, destaque (`sim`/`nao`), autor (`usuario_id`), categoria (`categoria_id`) e data gerada pelo banco.
- As validações atuais exigem nome, e-mail válido, senha com pelo menos 5 caracteres, título, texto e resumo não vazios; o resumo aceita até 300 caracteres; categoria deve ter ID positivo; tipo e destaque devem corresponder aos enums.
- Os dumps SQL definem `microblog_2026`; as tabelas centrais são `usuarios`, `categorias` e `noticias`. Confira o dump antes de alterar o esquema e mantenha consultas, modelos e formulários consistentes.
- Há material potencialmente desatualizado: `_materiais/modelagem-fisica.md` ainda cita `microblog_2025`, e seu limite de resumo difere da validação atual. Para o ambiente local, a conexão e o dump `microblog_2026` refletem melhor o código corrente.

## Acesso e páginas administrativas

- `login.php` busca o usuário por e-mail e verifica a senha com `password_verify()`. `ControleDeAcesso` mantém `id`, `nome` e `tipo` na sessão.
- `includes/cabecalho-admin.php` chama `ControleDeAcesso::exigirLogin()`. Inclua o controle de acesso em qualquer página administrativa e valide autorização no servidor; esconder links na interface não substitui essa validação.
- Em `NoticiaServico::listarParaPainel()`, notícias são filtradas pelo autor quando o tipo normalizado é `editor`; administradores recebem a lista geral.
- As páginas de usuários e categorias implementam listagem, inserção, atualização e exclusão. A atualização de usuário atualmente tem blocos `catch` vazios; corrija-os caso altere esse fluxo.
- O cadastro e a listagem de notícias estão implementados. `admin/noticia-atualiza.php`, `admin/noticia-exclui.php` e `admin/sair.php` ainda são páginas provisórias, embora exista um método `logout()` em `ControleDeAcesso`.
- O painel inicial restringe visualmente alguns links por tipo, mas confira autorização nas páginas de destino ao implementar ou modificar operações.

## Cuidados ao alterar

- Faça mudanças pequenas e relacionadas à solicitação; não reescreva páginas inteiras para corrigir um ponto localizado.
- Não altere `vendor/` manualmente nem inclua arquivos gerados, logs, uploads ou credenciais em commits.
- Ao mudar validações, enums ou formato de campos, verifique todos os formulários e serviços que consomem esses valores.
- Ao mudar operações de notícias, preserve a regra de propriedade para editores e verifique listagem, inserção, atualização e exclusão.
- Registre divergências observadas entre o estado real do código e documentos de evolução antes de usá-los como fonte de verdade.
