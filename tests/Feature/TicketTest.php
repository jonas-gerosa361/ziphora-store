<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Tickets\AddTicket;
use App\Services\Tickets\CloseTicket;
use App\Services\Tickets\GetTicket;
use App\Services\Tickets\ListTickets;
use App\Services\Tickets\UpdateTicket;
use App\Models\Messages;
use App\Models\Tickets;

class TicketTest extends TestCase
{
    /**
     * @dataProvider ticketDataProvider
     */
    public function testCreateNexxunTicket($data)
    {
        //Assert
        $this->loginWithFakeUser();
        session(['product' => 'Nexxun']);
        //Act
        $action = new AddTicket;
        $test = $action->execute($data, null);
        //Assert
        self::assertTrue($test['success']);
        self::assertSame("Chamado registrado com sucesso!", $test['message']);
        self::assertIsInt($test['ticket']->id);
        self::assertSame('Nexxun', $test['ticket']->product);

        //Excluir chamado criado
        Tickets::destroy($test['ticket']->id);
    }

    /**
     * @dataProvider ticketDataProvider
     */
    public function testCreateArtbackupTicket($data)
    {
        //Assert
        $this->loginWithFakeUser();
        session(['product' => 'Artbackup']);
        //Act
        $action = new AddTicket;
        $test = $action->execute($data, null);
        //Assert
        self::assertTrue($test['success']);
        self::assertSame("Chamado registrado com sucesso!", $test['message']);
        self::assertIsInt($test['ticket']->id);
        self::assertSame('Artbackup', $test['ticket']->product);

        //Excluir chamado criado
        Tickets::destroy($test['ticket']->id);
    }
    /**
     * @dataProvider ticketDataProvider
     */
    public function testClosedTicketShouldNotAcceptNewMessage($data)
    {
        //Assert
        $ticket = $this->createTicket($data);
        $action = new CloseTicket;
        $action->execute($ticket->id);
        $vars = [
            'id' => $ticket->id,
            'message' => 'Testando um update em chamado fechado'
        ];
        //Act
        $action = new UpdateTicket;
        $test = $action->execute($vars, null);
        //Assert
        self::assertFalse($test['success']);
        self::assertSame('Chamado finalizado não pode aceitar mensagem', $test['message']);

        //Limpar teste
        $this->clearTicketData($ticket->id);

    }

    /**
     * @dataProvider ticketDataProvider
     */
    public function testCloseTicket($data)
    {
        //Assert
        $ticket = $this->createTicket($data);
        //Act
        $action = new CloseTicket;
        $test = $action->execute($ticket->id);
        //Assert
        $action = new GetTicket();
        $checkTicket = $action->execute($ticket->id);
        self::assertTrue($test['success']);
        self::assertSame('Chamado finalizado com sucesso', $test['message']);
        self::assertSame('Finalizado', $checkTicket->situation);
        self::assertNull($checkTicket->status);

        //Limpar testes
        $this->clearTicketData($ticket->id);
    }

    /** 
     * @dataProvider ticketDataProvider
     */
    public function testUpdateTicket($data)
    {
        //Assert
        $ticket = $this->createTicket($data);
        $vars = [
            'id' => $ticket->id,
            'message'=> 'Testando update de chamado'
        ];
        //Act
        $action = new UpdateTicket;
        $test = $action->execute($vars, null);
        //Assert
        $message = Messages::where('ticket_id', $ticket->id)->orderBy('id', 'ASC')->first();
        self::assertTrue($test['success']);
        self::assertSame('Comentário registrado com sucesso!', $test['message']);
        self::assertSame('Testando update de chamado', $message->message);

        //Limpar dados
        $this->clearTicketData($ticket->id);
    }

    public function testListOpenTickets()
    {
        //Assert
        $this->loginWithFakeUser();
        //Act
        $action = new ListTickets;
        $tickets = $action->execute('');
        //Assert
        foreach ($tickets as $ticket) {
            self::assertNotSame('Finalizado', $ticket->situation);
        }
    }

    public function testListClosedTickets()
    {
        //Assert
        $this->loginWithFakeUser();
        //Act
        $action = new ListTickets;
        $tickets = $action->execute('Finalizado');
        //Assert
        foreach ($tickets as $ticket) {
            self::assertSame('Finalizado', $ticket->situation);
        }
    }

    public function testListAllTickets()
    {
        //Assert
        $this->loginWithFakeUser();
        //Act
        $action = new ListTickets;
        $tickets = $action->execute('Todos');
        //Assert
        foreach ($tickets as $ticket) {
            self::assertContains($ticket->situation, ['Finalizado', 'Em andamento']);
        }
    }

    //DataProviders
    function ticketDataProvider()
    {
        return [
            [
                [
                    'category' => 'Problema',
                    'message' => 'Testando abertura de um chamado'
                ]
            ]
        ];
    }

    /**
     * @dataProvider ticketDataProvider
     */
    private function createTicket($data)
    {
        $this->loginWithFakeUser();
        session(['product' => 'Artbackup']);
        $action = new AddTicket;
        $ticket = $action->execute($data, null);
        return $ticket['ticket'];
    }

    private function clearTicketData(int $id): void
    {
        Messages::where('ticket_id', $id)->delete();
        Tickets::destroy($id);
    }
}