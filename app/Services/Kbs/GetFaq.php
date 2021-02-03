<?php 

namespace App\Services\Kbs;

use Exception;
use \App\Models\Faqs;

class GetFaq
{
    function execute(int $id): object
    {          
        return Faqs::find($id);
    }
}