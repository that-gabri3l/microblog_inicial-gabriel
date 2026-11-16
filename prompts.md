# Histórico de prompts do projeto

Registro cronológico dos prompts enviados nesta conversa.

## 1. Análise inicial do código

> Analise todas as classes utilizadas até o momento (contidas na pasta src), em especial a classe NoticiaServico e a classe Noticoa.
>
> Veja também as paginas administrativas, em especial as paginas que gerenciam usuários, categorias e noticias.
>
> Identifique padrões, como nomes de métodos, propriedade, vairaveis, arquivos, namespaces, normalizações e validações
>
> Aguarde o próximo comando.

## 2. Documentação do projeto

> Gere um arquivo AGENTS.md contendo todas as informações essenciais a respeito do desenvolvimento e caracteristicas deste projeto. A ideia é que este arquvo sirva de referência para outras IAs ou caso ocorra a troca de maquina/VScode, siuação em que não há um historico de conversação

## 3. Busca de notícia por ID

> Programe em NoticiaServico o metodo buscarPorId. Leve em consideração que o SQL deve ser ajustado para, quando for usuarios do tipo ADMIN, carregar/exibir a noticia de qualquer pessoa de acord com o id do usuario e o id da noticia. Se for usuario do tipo EDITOR, ele/ela so pode carregar/exibir suas proprias noticias. Faça apenas o metodo e aguarde proximo comando.

## 4. Carregamento da página de atualização

> Analise a pagina admin/noticias.php e veja se o link dinâmico para pagina admin/notica-atualiza.php esta correto. Em seguida, programe a pagina admin/notica-atualiza.php permitindo que ela exiba os dados da notica a ser atualizada (conforme link que o usuario acionar em noticas.php).
>
> Atenção: o select de categorias deve carregar todas as categoras existentes, e deve trazr selecionada a categora da notica escolhida
>
> Não implmente ainda o codigo para atualizar a noticia. Foque apenas no carregamento e exibição dos dados na pagina



## 6. Finalização da atualização da notícia

> Com base no conteudo que ja tem, seja ja usando o arquivo AGENTES,prossiga com a criação/finalização da noticia-atualização

## 7. Implementação da exclusão da notícia

> Agora, utilizando os mesmos criterios da noticia atualizar, mas agora fazendo uma nova função, seguindo outros modelos do arquivo, faça o noticia-excluir

## 8. Registro dos prompts

> agora crie uma pagina chamada prompts e coloque todos os prompts que ja utilizei até agora
