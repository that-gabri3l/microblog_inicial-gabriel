<?php

use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$responder = static function (array $dados, int $status = 200): void {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    $responder(['erro' => 'Método não permitido.'], 405);
    exit;
}

$termo = Utils::normalizarEntrada($_POST['busca'] ?? '', 'texto');
if ($termo === '') {
    $responder(['erro' => 'Informe um termo para a busca.'], 400);
    exit;
}

if (mb_strlen($termo, 'UTF-8') > 100) {
    $responder(['erro' => 'O termo de busca excede o limite permitido.'], 400);
    exit;
}

if (mb_strlen($termo, 'UTF-8') < 2) {
    $responder(['resultados' => []]);
    exit;
}

try {
    $resultados = (new NoticiaServico())->buscarPublicas($termo);
    $responder(['resultados' => $resultados]);
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $responder(['erro' => 'Não foi possível realizar a busca.'], 500);
}
