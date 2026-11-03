<?php
namespace Microblog\Services;

use Exception;
use Microblog\Database\ConexaoBD;
use Microblog\Helpers\Utils;
use PDO;
use PDOException;

final class UsuarioServico
{
    private \PDO $conexao;

        //Ao criar um objeto UsuarioServico, o construtor fica responsavel por chamar/executar a conexão com o banco de dados (proveniente do métdo estatico getConexao). Os dados da conexão ficarão guardados na propriedade conexao.
    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }


    public function listarTodos(): array
    {
        $sql = "SELECT id, nome, email, tipo FROM usuarios ORDER BY nome";

        try {
            $consulta = $this->conexao->query($sql);
            return $consulta->fetchAll();
        } catch (PDOException $erros) {
            //Registrar/logar os detalhes do erro em logs/erro.log
            Utils::registrarLog($erros);

            //Lançando uma mensagem de rro amigavel (sem detalhes)
            //Essa mensagem sera posteriormente capturada e exibida no Front
            throw new Exception("Erro ao listar usuarios");
        }
    }
}