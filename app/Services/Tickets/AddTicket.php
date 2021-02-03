<?php 

namespace App\Services\Tickets;

// Default
use Exception;
use App\Exceptions\ValidatorException;

//Specifc
use App\Models\Tickets;
use App\Models\Messages;
use App\Models\Users;
use App\Models\Attachements;
use Illuminate\Support\Facades\Validator;
use App\Services\Vendors\TomTicket\AddTomTicket;
use Illuminate\Http\Request;
use App\Mail\NewTicket;
use Mail;
use Illuminate\Support\Facades\Http;
use Config;
use Crypt;
use App\Helpers\Utils;
use Illuminate\Database\Elloquent\Collection;

class AddTicket
{
    public function execute($vars, $files)
    {        
        try
        {
            //Validação padrão
            $this->validate($vars);
            
            //Recuperar user_name e company_name
            $user = auth()->user();
            //Se tiver arquivos, gravar data localmente e metadata no banco
            if(isset($files)){
                $stored_files = Utils::addFile($files);
                $files = [];
                foreach($stored_files as $file){
                    array_push($files, ['path' => $file['path'], 'name' => $file['original_name']]);
                }
            }else{
                $stored_files = null;
                $files = null;
            }

            //Checar se o cliente é artbackup ou nexxun
            strtoupper(session('product')) === 'NEXXUN' ? $product = 'Nexxun' : $product = 'Artbackup';
            
            //Gerar token para validação de autoacesso
            $token = uniqid();
            //Gravar chamado
            $insert = Tickets::create([
                'product' => $product,
                'category' => $vars['category'],
                'situation' => 'Em andamento',
                'client_name' => $user->name,
                'client_id' => $user->id,
                'description' => $vars['message'],
                'attachement' => serialize($files),
                'company_name' => $user->company->name,
                'token' => $token
            ]);

            //Enviar e-mail
            $this->sendEmail($insert, $user, $vars);
            
            return [
                'success' => true,
                'message' => 'Chamado registrado com sucesso!',
                'ticket' => $insert
            ];
            
        }catch(ValidatorException $e){
            return ['success' => false, 'message' => $e->getMessage()];
        }catch(Exception $e){
            report($e);
            return ['error' => $e->getMessage()];
        }
    }

    private function validate($vars)
    {
        if (strlen($vars['message']) >= 2000) {
            throw new ValidatorException ("Campo mensagem muito grande. Favor diminuir o número de caracteres presente");
        }

        $validator = Validator::make($vars,[ 
            'category' => 'required',
            'message' => 'required|min:4'
        ],
        [
            'min' => 'Por favor forneça mais detalhes sobre o chamado',
            'required' => 'Por favor preencha todos os campos corretamente'
        ]);
        if ($validator->fails()) {
            throw new ValidatorException ($validator->errors()->first());
        } 
    }

    private function sendEmail(Tickets $insert, Users $user, array $vars): void
    {
        $link = env('APP_URL'). '/tickets/ticket/' .Crypt::encrypt($insert->id). '/autologin?token=' . $insert->token;
            $data = [
                'id' => $insert->id,
                'company_name' => $insert->company_name,
                'client_name' => $insert->client_name,
                'created_at' => $insert->created_at,
                'category' => $insert->category,
                'description' => $insert->description,
                'situation' => $insert->situation,
                'status' => $insert->status,
                'link' => $link
            ];

            //Dispatch no envio de email
            Mail::to($user)->bcc(config::get('variables.support_email'))->queue(new NewTicket($data));
    }

}