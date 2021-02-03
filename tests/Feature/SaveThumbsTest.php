<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Kbs\SaveThumb;
use App\Services\Kbs\GetFaq;


class SaveThumbsTest extends TestCase
{
    public function testSaveThumbsUp()
    {
        $data = [
            'id' => 1,
            'thumbs' => 'up'
        ];
        $action = new GetFaq;
        $faq = $action->execute(1);
        $preUp = $faq->thumbs_up;
        $postUp = ++$preUp;
        $action = new SaveThumb;
        $test = $action->execute($data);
        self::assertTrue($test['success']);
        self::assertSame('Agradecemos pela participação, sua opinião é muito importante para nós!', $test['message']);
        self::assertSame($preUp, $postUp);
    }

    public function testSaveThumbsDown()
    {
        //Arrange
        $data = [
            'id' => 1,
            'thumbs' => 'down',
            'feedback' => 'Não gostei desta faq'
        ];
        $action = new GetFaq;
        $faq = $action->execute(1);
        $preDown = $faq->thumbs_down;
        $preUp = $faq->thumbs_up;
        $postDown = ++$preDown;
        $postUp = --$preUp;
        //Act
        $action = new SaveThumb;
        $test = $action->execute($data);
        //Assert
        self::assertTrue($test['success']);
        self::assertSame('Agradecemos pela participação, sua opinião é muito importante para nós!', $test['message']);
        self::assertSame($preDown, $postDown);
        self::assertSame($preUp, $postUp);
    }
}
