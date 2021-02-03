<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Companies\GetCompany;


class GetCompanyTest extends TestCase
{
    public function testGetCompany()
    {
        $action = new GetCompany;
        $company = $action->execute(1);
        self::assertIsInt($company->id);
        self::assertIsString($company->name);
    }
}
