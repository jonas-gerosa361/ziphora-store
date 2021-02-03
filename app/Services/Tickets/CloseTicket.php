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

class CloseTicket
{
    public function execute(int $id)
    {        
        try {
            //encerra o chamado na base
            $ticket = Tickets::find($id);
            $user = auth()->user();
            $ticket->situation = 'Finalizado';
            $ticket->status = null;
            $ticket->save();
            //Gravar log no campo mensagem
            Messages::create([
                'message' => 'Chamado encerrado.',
                'ticket_id' => $ticket->id,
                'client_name' => $user->name,
                'attachement' => serialize(null)
            ]);
            //preparar o envio de e-mail
            $this->sendEmail($ticket, $user);

            return [
                'success' => true,
                'message' => 'Chamado finalizado com sucesso'
            ];
        } catch (\Exception $e) {
            report($e);
            return [
                'success' => false,
                'message' => 'Erro inesperado ao encerrar chamado'
            ];
        }
    }

    private function sendEmail(Tickets $ticket, Users $user): void
    {
        $link = env('APP_URL') . 'http://suporte.local/tickets/ticket/' .Crypt::encrypt($ticket->id). '/autologin?token=' .$ticket->token; 
            $data = [
                'link' => $link,
                'id' => $ticket->id,
                'company_name' => $ticket->company_name,
                'client_name' => $ticket->client_name,
                'created_at' => $ticket->created_at,
                'category' => $ticket->category,
                'description' => $ticket->description,
                'situation' => $ticket->situation,
                'status' => $ticket->status,
            ];
            //Enviar o email
            Mail::to($user)->bcc(config::get('variables.support_email'))->queue(new \App\Mail\CloseTicket($data));
    }

}