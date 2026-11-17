<?php

require_once __DIR__ . '/vendor/autoload.php';

use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

$noticiasDestaque = [];
$mensagemErro = '';

try {
    $noticiasDestaque = (new NoticiaServico())->listarDestaquesPublicos();
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar as notícias em destaque.';
}

require_once __DIR__ . '/includes/cabecalho.php'; ?>

<div class="row my-1 mx-md-n1">
    <article class="col-12">
        <h2 class="text-center">Notícias em destaque</h2>

        <?php if ($mensagemErro !== ''): ?>
            <div class="alert alert-danger text-center">
                <?= Utils::escaparHtml($mensagemErro) ?>
            </div>
        <?php elseif ($noticiasDestaque === []): ?>
            <p class="text-center text-muted">Nenhuma notícia em destaque no momento.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($noticiasDestaque as $noticia): ?>
                    <div class="col-md-6 my-1 px-md-1">
                        <article class="card shadow-sm h-100">
                            <a href="noticia.php?id=<?= (int) $noticia['id'] ?>" class="card-link text-decoration-none">
                                <img
                                    src="images/<?= rawurlencode((string) $noticia['imagem']) ?>"
                                    class="card-img-top"
                                    alt="<?= Utils::escaparHtml($noticia['titulo']) ?>">
                                <div class="card-body">
                                    <h3 class="fs-4 card-title"><?= Utils::escaparHtml($noticia['titulo']) ?></h3>
                                    <p class="card-text"><?= Utils::escaparHtml($noticia['resumo']) ?></p>
                                </div>
                            </a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</div>

<?php
require_once __DIR__ . '/includes/todas.php';
require_once __DIR__ . '/includes/rodape.php';
?>
