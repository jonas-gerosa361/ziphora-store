<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Tickets\CheckToken;

class TicketTest extends TestCase
{
    //DataProviders
    public function ticketProvider()
    {
        return [
            [
                $ticket = [
                    'id' => 1,
                    'created_at' => "2020-12-09 11:02:14",
                    'updated_at' => "2020-12-10 15:35:51",
                    'product' => 'Nexxun',
                    'category' => 'Problema',
                    'description' => 'Bla bla bla bla bla teste 123556',
                    'attachement' => "N;",
                    'status' => null,
                    'situation' => 'Em andamento',
                    'company_name' => 'Datasafer LTDA',
                    'client_id' => 1,
                    'support_agent' => null,
                    'token' => '123456',
                    'notified' => null
                ]
            ]
        ];
    }
    
    /**
     * @dataProvider ticketProvider
     */
    public function testTicketTokenReceivedByEmailIsValid($ticket)
    {
        $token = $ticket['token'];
        $action = new CheckToken;
        $check = $action->execute((object) $ticket, $token);
        self::assertTrue($check);
    }

    /**
     * @dataProvider ticketProvider
     */
    public function testTicketTokenInvalidThrowsException($ticket)
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('Token de login inválido');
        
        $action = new CheckToken;
        $check = $action->execute((object) $ticket, '123');
    }
}
