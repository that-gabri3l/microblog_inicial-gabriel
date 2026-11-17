<?php

use Microblog\Helpers\Utils;
use Microblog\Services\CategoriaServico;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/vendor/autoload.php';

Utils::verificarId($_GET['id'] ?? null);
$categoriaId = (int) $_GET['id'];
$categoriaSelecionada = null;
$noticias = [];
$mensagemErro = '';

try {
    $categoriaSelecionada = (new CategoriaServico())->buscarPorId($categoriaId);
    if ($categoriaSelecionada !== null) {
        $noticias = (new NoticiaServico())->listarPublicasPorCategoria($categoriaId);
    }
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar as notícias desta categoria.';
}

require_once __DIR__ . '/includes/cabecalho.php';
?>

<div class="row my-1 mx-md-n1">
    <article class="col-12">
        <h2 class="text-center">
            <?php if ($categoriaSelecionada !== null): ?>
                Notícias sobre <span class="badge bg-primary"><?= Utils::escaparHtml($categoriaSelecionada['nome']) ?></span>
            <?php else: ?>
                Notícias por categoria
            <?php endif; ?>
        </h2>

        <?php if ($mensagemErro !== ''): ?>
            <div class="alert alert-danger text-center">
                <?= Utils::escaparHtml($mensagemErro) ?>
            </div>
        <?php elseif ($categoriaSelecionada === null): ?>
            <div class="alert alert-warning text-center">Categoria não encontrada.</div>
        <?php elseif ($noticias === []): ?>
            <p class="text-center text-muted">Nenhuma notícia encontrada nesta categoria.</p>
        <?php else: ?>
            <div class="list-group my-3">
                <?php foreach ($noticias as $noticia): ?>
                    <a href="noticia.php?id=<?= (int) $noticia['id'] ?>" class="list-group-item list-group-item-action">
                        <h3 class="fs-5"><?= Utils::escaparHtml($noticia['titulo']) ?></h3>
                        <p class="text-muted">
                            <time datetime="<?= Utils::escaparHtml($noticia['data']) ?>">
                                <?= Utils::escaparHtml(date('d/m/Y', strtotime($noticia['data']))) ?>
                            </time>
                            - <?= Utils::escaparHtml($noticia['autor']) ?>
                        </p>
                        <p class="mb-0"><?= Utils::escaparHtml($noticia['resumo']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</div>

<?php
require_once __DIR__ . '/includes/todas.php';
require_once __DIR__ . '/includes/rodape.php';
?>
