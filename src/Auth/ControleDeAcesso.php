<?php
namespace Microblog\Auth;

// Sessões no PHP. Funcionalidade usada para controlar o acesso a determinadas paginas e recursos de um site/aplicação.

// Exemplos: áreas admistrativas, painel de controle, área de cliente/aluno. Nessas áres, o acesso depende de alguma forma de autenticação, como login e senha, biometria, por dois fatores etc.

final class ControleDeAcesso
{
    //Impedir a criação de objeto/instância
    private function __construct(){}

    //Inicia a sessão se ela ainda não estiver ativa
    private static function iniciarSessao():void
    {   
        //Se o status da sessão NÃO FOR ativa (ou, se a sessão esta inativa)
        if(session_status() !== PHP_SESSION_ACTIVE){
        // Então, iniciamos a sessão
        session_start();
        }
    }

    private static function encerrarSessao():void
    {
        $_SESSION = []; //Limpar os dados do usuario armazenados na sessão
        session_destroy();// destroi/remove quaisquer dados
    }


    public static function exigirLogin(): void
    {
        self::iniciarSessao();

        //Se NÃO EXISTIR na sessão uma variavel chamada id,
        //significa que o usuario NÃO ESTA logado
        if(!isset($_SESSION['id'])){
            // encerrar a sessão

            //redirecionar para a pagina de login
            header("location:../login.php?acesso_proibido");
            exit;
        }
    }
}
