<?php 

namespace App\Services\Tickets;

// Default
use Exception;
use App\Exceptions\ValidatorException;

//Specifc
use App\Models\Tickets;
use App\Models\Messages;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;
use App\Services\Vendors\TomTicket\UpdateTomTicket;
use Illuminate\Http\Request;
use Mail;
use Config;
use Crypt;
use \App\Helpers\Utils;
use Illuminate\Database\Elloquent\Collection;

class UpdateTicket
{
    public function execute($vars, $files)
    {        
        try
        {
            $ticket = Tickets::find($vars['id']);
            if ($ticket->situation === 'Finalizado') {
                throw new ValidatorException('Chamado finalizado não pode aceitar mensagem');
            }
            $user = auth()->user();
            if (empty($user->id)) {
                $tmp_user = Users::find($ticket->client_id);
                $user = (object)[
                    'name' => $ticket->client_name . ' (acesso via link)',
                    'email' => $tmp_user->email
                ];
            }
            //Validação padrão
            $this->validate($vars);

            //Se tiver arquivos, gravar data localmente e metadata no banco
            if (isset($files)) {
                $stored_files = Utils::addFile($files); 
                $files = [];
                foreach($stored_files as $file){
                    array_push($files, ['path' => $file['path'], 'name' => $file['original_name']]);
                }
            } else {
                $stored_files = null;
                $files = null;
            }

            //Gravar mensagem
            $insertMessage = Messages::create([
                'message' => $vars['message'],
                'ticket_id' => $vars['id'],
                'client_name' => $user->name,
                'attachement' => serialize($files)
            ]);

            //Alterar situação do chamado e resetar o notifed 'caso ativo'
            $ticket->situation = 'Em andamento';
            $ticket->notified = false;
            $ticket->save();

            //Enviar e-mail
            $this->sendEmail($vars, $ticket, $user);

            return [
                'success' => true,
                'message' => 'Comentário registrado com sucesso!'
            ];
            
        }catch (ValidatorException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }catch (Exception $e) {
            report($e);
            return ['success' => false, 'message' => $e->getMessage() ];
        }
    }

    private function validate($vars)
    {
        $validator = Validator::make($vars,[ 
            'message' => 'required'
        ],
        [
            'required' => 'Por favor preencha todos os campos corretamente.'
        ]);
        if ($validator->fails()) throw new ValidatorException ($validator->errors()->first());
        if (strlen($vars['message']) >= 2000) throw new ValidatorException ("Campo mensagem muito grande. Favor diminuir o número de caracteres presente");
    }

    private function sendEmail(array $vars, Tickets $ticket, Users $user, Messages $messsage): void
    {
        $link = env('APP_URL') .'/tickets/ticket/' .Crypt::encrypt($vars['id']). '/autologin?token=' .$ticket->token; 
        $data = [
            'link' => $link,
            'message' => nl2br($messsage->message),
            'message_created_at' => $messsage->created_at,
            'id' => $ticket->id,
            'company_name' => $ticket->company_name,
            'client_name' => $user->name,
            'created_at' => $ticket->created_at,
            'category' => $ticket->category,
            'description' => $ticket->description,
            'situation' => $ticket->situation,
            'status' => $ticket->status
        ];

        Mail::to($user)->bcc(config::get('variables.support_email'))->queue(new \App\Mail\UpdateTicket($data));
    }

}