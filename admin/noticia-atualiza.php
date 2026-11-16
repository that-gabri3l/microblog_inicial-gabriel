<?php

use Microblog\Auth\ControleDeAcesso;
use Microblog\Enums\Destaque;
use Microblog\Helpers\Utils;
use Microblog\Helpers\Validacoes;
use Microblog\Models\Noticia;
use Microblog\Services\CategoriaServico;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/../vendor/autoload.php';

ControleDeAcesso::exigirLogin();

$id = Utils::normalizarEntrada($_GET['id'] ?? null, 'inteiro');
Utils::verificarId($id);

$usuarioId = (int) ($_SESSION['id'] ?? 0);
$tipoUsuario = mb_strtolower($_SESSION['tipo'] ?? '', 'UTF-8');
$mensagemErro = '';
$noticia = null;
$listaDeCategorias = [];
$categoriasCarregadas = false;
$categoriaServico = null;

try {
    $noticiaServico = new NoticiaServico();
    $noticia = $noticiaServico->buscarPorId($id, $usuarioId, $tipoUsuario);

    $categoriaServico = new CategoriaServico();
    $listaDeCategorias = $categoriaServico->listarTodos();
    $categoriasCarregadas = true;
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar os dados da notícia.';
}

if ($noticia === null && $mensagemErro === '') {
    $mensagemErro = 'Notícia não encontrada ou você não tem permissão para acessá-la.';
}

$valoresFormulario = [
    'categoria' => $noticia['categoria_id'] ?? '',
    'titulo' => $noticia['titulo'] ?? '',
    'texto' => $noticia['texto'] ?? '',
    'resumo' => $noticia['resumo'] ?? '',
    'destaque' => $noticia['destaque'] ?? Destaque::NAO->value,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $noticia !== null && $categoriasCarregadas) {
    try {
        $valoresFormulario['categoria'] = Utils::normalizarEntrada($_POST['categoria'] ?? '', 'inteiro');
        $valoresFormulario['titulo'] = Utils::normalizarEntrada($_POST['titulo'] ?? '');
        $valoresFormulario['texto'] = Utils::normalizarEntrada($_POST['texto'] ?? '');
        $valoresFormulario['resumo'] = Utils::normalizarEntrada($_POST['resumo'] ?? '');
        $valoresFormulario['destaque'] = Utils::normalizarEntrada($_POST['destaque'] ?? '');

        Validacoes::validarCategoria($valoresFormulario['categoria']);
        Validacoes::validarTitulo($valoresFormulario['titulo']);
        Validacoes::validarTexto($valoresFormulario['texto']);
        Validacoes::validarResumo($valoresFormulario['resumo']);
        Validacoes::validarDestaque($valoresFormulario['destaque']);

        if ($categoriaServico->buscarPorId($valoresFormulario['categoria']) === null) {
            throw new InvalidArgumentException('Selecione uma categoria existente.');
        }

        $imagem = $noticia['imagem'];
        if (array_key_exists('imagem', $_FILES)) {
            $arquivoImagem = $_FILES['imagem'];
            if (!is_array($arquivoImagem)) {
                throw new InvalidArgumentException('O arquivo enviado é inválido.');
            }

            if (($arquivoImagem['error'] ?? null) !== UPLOAD_ERR_NO_FILE) {
                $imagem = Utils::upload($arquivoImagem);
            }
        }

        $noticiaAtualizada = new Noticia(
            $valoresFormulario['titulo'],
            $valoresFormulario['texto'],
            $valoresFormulario['resumo'],
            $imagem,
            Destaque::from($valoresFormulario['destaque']),
            $noticia['usuario_id'] === null ? $usuarioId : (int) $noticia['usuario_id'],
            $valoresFormulario['categoria'],
            $id,
            $noticia['data']
        );

        $noticiaServico->atualizar($noticiaAtualizada, $usuarioId, $tipoUsuario);

        header('Location: noticias.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $mensagemErro = $e->getMessage();
    } catch (Throwable $e) {
        Utils::registrarLog($e);
        $mensagemErro = 'Não foi possível atualizar a notícia. Tente novamente.';
    }
}

require_once __DIR__ . '/../includes/cabecalho-admin.php';
?>

<div class="row">
    <article class="col-12 bg-white rounded shadow my-1 py-4">
        <h2 class="text-center">Atualizar dados da notícia</h2>

        <?php if ($mensagemErro !== ''): ?>
            <div class="alert alert-danger text-center">
                <?= Utils::escaparHtml($mensagemErro) ?>
            </div>
        <?php endif; ?>

        <?php if ($noticia !== null && $categoriasCarregadas): ?>
            <form class="mx-auto w-75" action="" method="post" id="form-atualizar" name="form-atualizar" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label" for="categoria">Categoria:</label>
                    <select class="form-select" name="categoria" id="categoria">
                        <option value="" <?= empty($valoresFormulario['categoria']) ? 'selected' : '' ?>>Selecione uma categoria</option>
                        <?php foreach ($listaDeCategorias as $itemCategoria): ?>
                            <option
                                value="<?= Utils::escaparHtml($itemCategoria['id']) ?>"
                                <?= (int) $itemCategoria['id'] === (int) $valoresFormulario['categoria'] ? 'selected' : '' ?>>
                                <?= Utils::escaparHtml($itemCategoria['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="titulo">Título:</label>
                    <input
                        value="<?= Utils::escaparHtml($valoresFormulario['titulo']) ?>"
                        class="form-control"
                        type="text"
                        id="titulo"
                        name="titulo">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="texto">Texto:</label>
                    <textarea class="form-control" name="texto" id="texto" cols="50" rows="6"><?= Utils::escaparHtml($valoresFormulario['texto']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="resumo">Resumo (máximo de 300 caracteres):</label>
                    <span id="maximo" class="badge bg-danger">0</span>
                    <textarea class="form-control" name="resumo" id="resumo" cols="50" rows="2" maxlength="300"><?= Utils::escaparHtml($valoresFormulario['resumo']) ?></textarea>
                </div>
                <div class="mb-3">
                    <div class="row">
                        <div class="col-5">
                            <label for="imagem-existente" class="form-label">Imagem atual:</label>
                            <input
                                value="<?= Utils::escaparHtml($noticia['imagem']) ?>"
                                class="form-control"
                                type="text"
                                id="imagem-existente"
                                readonly
                                disabled>
                        </div>
                        <div class="col-7 text-end">
                            <img
                                src="../images/<?= Utils::escaparHtml(rawurlencode($noticia['imagem'])) ?>"
                                alt="Imagem atual da notícia"
                                class="img-fluid rounded">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="imagem" class="form-label">Caso queira mudar, selecione outra imagem:</label>
                    <input class="form-control" type="file" id="imagem" name="imagem" accept="image/jpeg, image/gif, image/png, image/webp">
                </div>
                <div class="mb-3">
                    <p>Deixar a notícia em destaque?</p>
                    <input type="radio" class="btn-check" name="destaque" id="option1" autocomplete="off" value="nao" <?= $valoresFormulario['destaque'] === 'nao' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-danger" for="option1">Não</label>
                    <input type="radio" class="btn-check" name="destaque" id="option2" autocomplete="off" value="sim" <?= $valoresFormulario['destaque'] === 'sim' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-success" for="option2">Sim</label>
                </div>
                <button class="btn btn-primary" type="submit" name="atualizar">
                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                </button>
            </form>
        <?php endif; ?>
    </article>
</div>

<?php if ($noticia !== null && $categoriasCarregadas): ?>
    <script src="js/contador.js"></script>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/rodape-admin.php'; ?>
