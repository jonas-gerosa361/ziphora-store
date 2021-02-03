<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Kbs\GetFaq;


class GetFaqTest extends TestCase
{
    public function testGetFaq()
    {
        $action = new GetFaq;
        $faq = $action->execute(1);
        self::assertIsInt($faq->id);
        self::assertIsString($faq->name);
    }
}
