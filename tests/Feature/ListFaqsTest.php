<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Kbs\ListFaqs;

class ListFaqsTest extends TestCase
{
    /** Testar faqs deslogado *******/
    public function testPublicFaq()
    {
        $action = new ListFaqs;
        $faqs = $action->execute();
        foreach ($faqs as $faq) {
            self::assertEquals('public', $faq->visibility);
            self::assertIsString($faq->message);
            self::assertIsString($faq->name);
        }
    }

    /***** Testar faqs logado *******/
    public function testFaqLoggedin()
    {
        $user = $this->loginWithFakeUser();
        $action = new ListFaqs;
        $faqs = $action->execute();
        foreach ($faqs as $faq) {
            self::assertNotEquals('internal', $faq->visibility);
            self::assertIsString($faq->name);
            self::assertIsString($faq->message);
        }
    }
}