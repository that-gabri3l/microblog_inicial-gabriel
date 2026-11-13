<?php

use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/../includes/cabecalho-admin.php';

$tipoUsuario = mb_strtolower($_SESSION['tipo'] ?? '', 'UTF-8');
$idUsuario = (int) ($_SESSION['id'] ?? 0);
$mensagemErro = '';
$noticias = [];

try {
    $noticiaServico = new NoticiaServico();
    $noticias = $noticiaServico->listarParaPainel($idUsuario, $tipoUsuario);
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar as notícias.';
}
?>

<div class="row">
    <article class="col-12 bg-white rounded shadow my-1 py-4">
        <h2 class="text-center">Notícias <span class="badge bg-dark"><?= count($noticias) ?></span></h2>
        <p class="text-center mt-5">
            <a class="btn btn-primary" href="noticia-insere.php">
                <i class="bi bi-plus-circle"></i> Inserir nova notícia
            </a>
        </p>

        <?php if ($mensagemErro !== ''): ?>
            <div class="alert alert-danger text-center">
                <?= Utils::escaparHtml($mensagemErro) ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>Data</th>
                        <?php if ($tipoUsuario === 'admin'): ?>
                            <th>Autor</th>
                        <?php endif; ?>
                        <th class="text-center">Destaque</th>
                        <th class="text-center" colspan="2">Operações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($noticias === []): ?>
                        <tr>
                            <td colspan="<?= $tipoUsuario === 'admin' ? 6 : 5 ?>" class="text-center text-muted">Nenhuma notícia encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($noticias as $noticia): ?>
                            <tr>
                                <td><?= Utils::escaparHtml($noticia['titulo']) ?></td>
                                <td><?= date('d/m/Y', strtotime($noticia['data'] ?? 'now')) ?></td>
                                <?php if ($tipoUsuario === 'admin'): ?>
                                    <td><?= Utils::escaparHtml($noticia['autor'] ?? 'Desconhecido') ?></td>
                                <?php endif; ?>
                                <td class="text-center"><?= ($noticia['destaque'] ?? 'nao') === 'sim' ? 'sim' : 'não' ?></td>
                                <td class="text-center">
                                    <a class="btn btn-warning btn-sm" href="noticia-atualiza.php?id=<?= (int) $noticia['id'] ?>">
                                        <i class="bi bi-pencil"></i> Atualizar
                                    </a>
                                </td>
                                <td>
                                    <a class="btn btn-danger excluir btn-sm" href="noticia-exclui.php?id=<?= (int) $noticia['id'] ?>">
                                        <i class="bi bi-trash"></i> Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</div>

<script src="js/confirm.js"></script>
<?php require_once __DIR__ . '/../includes/rodape-admin.php'; ?>
