<?php 

namespace App\Services\Tickets;

// Default
use Exception;
use App\Exceptions\ValidatorException;

//Specifc
use App\Models\Tickets;
use App\Models\Messages;
use App\Models\Users;
use Illuminate\Http\Request;
use Mail;
use Config;
use Crypt;

class ReopenTicket
{
    public function execute(int $ticket_id): void
    {        
        //Recuperar chamado e alterar na base a situação do chamado e flagar o reopen
        $ticket = Tickets::find($ticket_id);
        $ticket->situation = 'Em andamento';
        $ticket->save();
        //Logar entrada nos comentários do chamado
        Messages::create([
            'message' => 'Chamado reaberto.',
            'ticket_id' => $ticket->id,
            'client_name' => $ticket->client_name
        ]);
        //Enviar e-mail
        $this->sendEmail($ticket);
    }
    
    private function sendEmail(Tickets $ticket): void
    {
        $user = auth()->user();
        $link = env('APP_URL') . 'http://suporte.local/tickets/ticket/' .Crypt::encrypt($ticket->id). '/autologin?token=' .$ticket->token; 
        $data = [
            'link' => $link,
            'id' => $ticket->id,
            'company_name' => $ticket->company_name,
            'client_name' => $ticket->client_name,
            'created_at' => $ticket->created_at,
            'category' => $ticket->category,
            'description' => $ticket->description,
            'situation' => $ticket->situation
        ];
        //Enviar o email
        Mail::to($user)->bcc(config::get('variables.support_email'))->queue(new \App\Mail\ReopenTicket($data));
    }
}