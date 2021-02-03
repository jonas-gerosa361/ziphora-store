<?php 

namespace App\Services\Kbs;

use Exception;
use \App\Models\Faqs;
use \App\Models\Feedbacks;
use Config;
use Mail;

class SaveThumb
{
    public function execute(array $vars)
    {     
        try {
            //recuperar FAQ 
            $faq = Faqs::find($vars['id']);
            //validar voto e salvar no banco
            if ($vars['thumbs'] == 'up') {
                $faq->thumbs_up++;
                $faq->save();
                return ['success' => true, 'message' => 'Agradecemos pela participação, sua opinião é muito importante para nós!'];
            } 

            $faq->thumbs_down++;
            $faq->thumbs_up--;
            $faq->save();

            //gravar feedback
            $insert = Feedbacks::create([
                'message' => $vars['feedback'],
                'faqs_id' => $faq->id,
            ]);
            //Preparar campo body do email
            //checar se está logado
            $data = [
                'message' => $vars['feedback'],
                'faq_id' => $faq->id,
                'faq_name' => $faq->name,
                'client_id' => 'acesso guest',
                'client_name' => 'acesso guest',
                'client_email' => 'acesso guest',
                'client_company_name' => 'acesso guest'
            ];

            if (auth()->check()) {
                $user = auth()->user();
                $data = [
                    'message' => $vars['feedback'],
                    'faq_id' => $faq->id,
                    'faq_name' => $faq->name,
                    'client_id' => $user->id,
                    'client_name' => $user->name,
                    'client_email' => $user->email,
                    'client_company_name' => $user->company->name
                ];
            } 
            
            Mail::to(config::get('variables.support_email'))->queue(new \App\Mail\FaqFeedback($data));
            return ['success' => true, 'message' => 'Agradecemos pela participação, sua opinião é muito importante para nós!'];

        } catch (\Throwable $th) {
            report($th);
            return ['success' => false, 'message' => 'Erro inesperado'];
        }     
    }

}