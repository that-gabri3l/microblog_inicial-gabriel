<?php

use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/vendor/autoload.php';

Utils::verificarId($_GET['id'] ?? null);
$noticia = null;
$mensagemErro = '';

try {
    $noticia = (new NoticiaServico())->buscarPublicaPorId((int) $_GET['id']);
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar esta notícia.';
}

require_once __DIR__ . '/includes/cabecalho.php';
?>

<div class="row my-1 mx-md-n1">
    <article class="col-12 col-lg-10 col-xl-9 mx-auto bg-white rounded shadow-sm p-3 p-md-5">
        <?php if ($mensagemErro !== ''): ?>
            <div class="alert alert-danger text-center">
                <?= Utils::escaparHtml($mensagemErro) ?>
            </div>
        <?php elseif ($noticia === null): ?>
            <div class="alert alert-warning text-center">Notícia não encontrada.</div>
        <?php else: ?>
            <header class="mb-4">
                <h2 class="display-6 fw-semibold mb-3"><?= Utils::escaparHtml($noticia['titulo']) ?></h2>
                <p class="text-muted mb-0">
                    <time datetime="<?= Utils::escaparHtml($noticia['data']) ?>">
                        <?= Utils::escaparHtml(Utils::formataData($noticia['data'])) ?>
                    </time>
                    - <span><?= Utils::escaparHtml($noticia['autor'] ?? 'Equipe Microblog') ?></span>
                </p>
            </header>
            <img
                src="images/<?= rawurlencode((string) $noticia['imagem']) ?>"
                alt="<?= Utils::escaparHtml($noticia['titulo']) ?>"
                class="img-fluid rounded w-100 mb-4 noticia-imagem">
            <div class="noticia-conteudo mx-auto">
                <?php foreach (preg_split('/\R{2,}/u', trim($noticia['texto'])) ?: [] as $paragrafo): ?>
                    <?php if (trim($paragrafo) !== ''): ?>
                        <p><?= nl2br(Utils::escaparHtml(trim($paragrafo))) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</div>

<?php
require_once __DIR__ . '/includes/todas.php';
require_once __DIR__ . '/includes/rodape.php';
?>
