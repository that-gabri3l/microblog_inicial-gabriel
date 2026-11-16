<?php

use Microblog\Auth\ControleDeAcesso;
use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/../vendor/autoload.php';

ControleDeAcesso::exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: noticias.php?erro_exclusao');
    exit;
}

$tokenSessao = $_SESSION['token_csrf'] ?? null;
$tokenFormulario = $_POST['token_csrf'] ?? null;
if (
    !is_string($tokenSessao) ||
    $tokenSessao === '' ||
    !is_string($tokenFormulario) ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    header('Location: noticias.php?erro_exclusao');
    exit;
}

$id = Utils::normalizarEntrada($_POST['id'] ?? null, 'inteiro');
Utils::verificarId($id);

$usuarioId = (int) ($_SESSION['id'] ?? 0);
$tipoUsuario = mb_strtolower($_SESSION['tipo'] ?? '', 'UTF-8');

try {
    $noticiaServico = new NoticiaServico();
    $noticiaServico->excluir($id, $usuarioId, $tipoUsuario);
    header('Location: noticias.php?excluida=1');
    exit;
} catch (InvalidArgumentException $e) {
    header('Location: noticias.php?erro_exclusao');
    exit;
} catch (Throwable $e) {
    Utils::registrarLog($e);
    header('Location: noticias.php?erro_exclusao');
    exit;
}
