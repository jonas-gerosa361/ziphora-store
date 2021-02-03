<?php 

namespace App\Services\Tickets;

// Default
use Exception;

// Specif
use Storage;

class GetTicket
{
    function execute(int $id)
    {           
        $ticket = \App\Models\Tickets::find($id); //recuperando chamado da base local
        $ticket->messages = \App\Models\Messages::where('ticket_id', $ticket->id)->orderBy('created_at', 'DESC')->get();
        foreach($ticket->messages as $message){
            if(empty($message->client_name)) $message->client_name = 'DataSafer';
        }
        return $ticket;
    }
}
