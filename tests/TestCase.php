<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function loginWithFakeUser()
    {
        $company = new \App\Models\Companies([
            'id' => 1,
            'name' => 'Empresa teste'
        ]);

        $user = new \App\Models\Users([
            'id' => 1,
            'name' => 'yish',
            'email' => 'ti.jonas361@gmail.com',
            'company_id' => 1
        ]);

        $this->be($user);
    }
}
