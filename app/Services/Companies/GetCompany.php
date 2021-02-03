<?php 

namespace App\Services\Companies;

// Specific
use App\Models\Companies;

class GetCompany
{
    public function execute(int $company_id)
    {
        return Companies::find($company_id);
    }
}
