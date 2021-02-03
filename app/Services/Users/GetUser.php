<?php 

namespace App\Services\Users;

class GetUser
{
    public function execute($vars)
    {
        $action = new ListUsers;
        return $action->execute($vars)->first();
    }
}
