<?php 

namespace App\Services\Tickets;

use \App\Models\Users;
use Exception;

class CheckToken
{
    /**
     * Checa se token é válido para acesso ao chamado e retorna verdadeiro ou falso
     */
    function execute(object $ticket, string $token):bool
    {    
        if ($ticket->token != $token) {
            throw new Exception ('Token de login inválido', 404); 
        } 

        return true;
    }
}
