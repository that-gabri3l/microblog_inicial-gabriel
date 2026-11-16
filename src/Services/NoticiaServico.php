<?php
namespace Microblog\Services;

use Exception;
use InvalidArgumentException;
use Microblog\Database\ConexaoBD;
use Microblog\Helpers\Utils;
use Microblog\Models\Noticia;
use PDO;
use PDOException;

final class NoticiaServico
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }

    public function inserir(Noticia $noticia): void
    {
        $sql = "INSERT INTO noticias ( titulo, texto, resumo, imagem, destaque, usuario_id, categoria_id) VALUES ( :titulo, :texto, :resumo, :imagem, :destaque, :usuario_id, :categoria_id)";


        try {
            $consulta = $this->conexao->prepare($sql);

            $consulta->bindValue(":titulo", $noticia->getTitulo());
            $consulta->bindValue(":texto", $noticia->getTexto());
            $consulta->bindValue(":resumo", $noticia->getResumo());
            $consulta->bindValue(":imagem", $noticia->getImagem());
            $consulta->bindValue(":destaque", $noticia->getDestaque()->value);
            $consulta->bindValue(":usuario_id", $noticia->getUsuarioId());
            $consulta->bindValue(":categoria_id", $noticia->getCategoriaId());
            $consulta->execute();

        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao inserir noticia");
        }
    }

    public function listarParaPainel(int $usuarioId, string $tipoUsuario): array
    {
        $sql = "
            SELECT n.id, n.data, n.titulo, n.destaque, u.nome AS autor
            FROM noticias n
            INNER JOIN usuarios u ON u.id = n.usuario_id
        ";

        if (mb_strtolower($tipoUsuario, 'UTF-8') === 'editor') {
            $sql .= " WHERE n.usuario_id = :usuario_id ";
        }

        $sql .= " ORDER BY n.data DESC";

        try {
            $consulta = $this->conexao->prepare($sql);

            if (mb_strtolower($tipoUsuario, 'UTF-8') === 'editor') {
                $consulta->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            }

            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao listar notícias para o painel");
        }
    }

    public function buscarPorId(int $noticiaId, int $usuarioId, string $tipoUsuario): ?array
    {
        $tipoUsuario = mb_strtolower($tipoUsuario, 'UTF-8');

        $sql = "
            SELECT n.id, n.data, n.titulo, n.texto, n.resumo, n.imagem,
                   n.destaque, n.usuario_id, n.categoria_id
            FROM noticias n
            WHERE n.id = :noticia_id
        ";

        if ($tipoUsuario === 'editor') {
            $sql .= " AND n.usuario_id = :usuario_id";
        } elseif ($tipoUsuario !== 'admin') {
            $sql .= " AND 1 = 0";
        }

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(':noticia_id', $noticiaId, PDO::PARAM_INT);
            if ($tipoUsuario === 'editor') {
                $consulta->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            }
            $consulta->execute();

            return $consulta->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao buscar notícia por ID");
        }
    }

    public function atualizar(Noticia $noticia, int $usuarioId, string $tipoUsuario): void
    {
        $id = $noticia->getId();
        $tipoUsuario = mb_strtolower($tipoUsuario, 'UTF-8');

        if ($id === null) {
            throw new InvalidArgumentException("A notícia precisa ter um ID para ser atualizada.");
        }

        if ($tipoUsuario !== 'admin' && $tipoUsuario !== 'editor') {
            throw new InvalidArgumentException("Tipo de usuário inválido.");
        }

        $condicaoUsuario = $tipoUsuario === 'editor' ? " AND usuario_id = :usuario_id" : "";

        try {
            $this->conexao->beginTransaction();

            $consultaVerificacao = $this->conexao->prepare(
                "SELECT id FROM noticias WHERE id = :id" . $condicaoUsuario . " FOR UPDATE"
            );
            $consultaVerificacao->bindValue(':id', $id, PDO::PARAM_INT);
            if ($tipoUsuario === 'editor') {
                $consultaVerificacao->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            }
            $consultaVerificacao->execute();

            if ($consultaVerificacao->fetchColumn() === false) {
                throw new InvalidArgumentException("Notícia não encontrada ou sem permissão para atualizá-la.");
            }

            $sql = "
                UPDATE noticias
                SET titulo = :titulo,
                    texto = :texto,
                    resumo = :resumo,
                    imagem = :imagem,
                    destaque = :destaque,
                    categoria_id = :categoria_id
                WHERE id = :id" . $condicaoUsuario;

            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(':titulo', $noticia->getTitulo());
            $consulta->bindValue(':texto', $noticia->getTexto());
            $consulta->bindValue(':resumo', $noticia->getResumo());
            $consulta->bindValue(':imagem', $noticia->getImagem());
            $consulta->bindValue(':destaque', $noticia->getDestaque()->value);
            $consulta->bindValue(':categoria_id', $noticia->getCategoriaId(), PDO::PARAM_INT);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            if ($tipoUsuario === 'editor') {
                $consulta->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            }
            $consulta->execute();

            $this->conexao->commit();
        } catch (PDOException $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }

            Utils::registrarLog($e);
            throw new Exception("Erro ao atualizar notícia");
        } catch (\Throwable $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }

            throw $e;
        }
    }
}