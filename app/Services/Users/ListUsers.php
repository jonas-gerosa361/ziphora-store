<?php namespace App\Services\Users;

use App\Models\Users;

class ListUsers {

    public function execute($vars) 
    {
        return Users::where($vars)->orderBy('name')->with('company')->get();
    }

}