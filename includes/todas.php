<?php

use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/../vendor/autoload.php';

$noticias = [];
$mensagemErro = '';

try {
    $noticias = (new NoticiaServico())->listarPublicas();
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar todas as notícias.';
}
?>

<hr class="my-5 w-50 mx-auto">

<div class="row my-1">
    <div class="col-12 px-md-1">
        <div class="list-group">
            <h2 class="fs-6 text-center text-muted">Todas as notícias</h2>

            <?php if ($mensagemErro !== ''): ?>
                <p class="list-group-item text-danger text-center">
                    <?= Utils::escaparHtml($mensagemErro) ?>
                </p>
            <?php elseif ($noticias === []): ?>
                <p class="list-group-item text-muted text-center">Nenhuma notícia encontrada.</p>
            <?php else: ?>
                <?php foreach ($noticias as $noticia): ?>
                    <a href="noticia.php?id=<?= (int) $noticia['id'] ?>" class="list-group-item list-group-item-action">
                        <h3 class="fs-6">
                            <time datetime="<?= Utils::escaparHtml($noticia['data']) ?>">
                                <?= Utils::escaparHtml(date('d/m/Y', strtotime($noticia['data']))) ?>
                            </time>
                            - <?= Utils::escaparHtml($noticia['titulo']) ?>
                        </h3>
                        <p class="mb-0"><?= Utils::escaparHtml($noticia['resumo']) ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
