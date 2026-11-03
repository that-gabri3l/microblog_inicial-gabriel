<?php
namespace Microblog\Services;

use Microblog\Database\ConexaoBD;
use PDO;

final class UsuarioServico
{
    private \PDO $conexao;

        //Ao criar um objeto UsuarioServico, o construtor fica responsavel por chamar/executar a conexão com o banco de dados (proveniente do métdo estatico getConexao). Os dados da conexão ficarão guardados na propriedade conexao.
    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }
}