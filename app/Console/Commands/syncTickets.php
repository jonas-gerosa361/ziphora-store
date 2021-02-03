<?php

namespace App\Console\Commands;

//Default
use Illuminate\Console\Command;

//Specific
use App\Services\Vendors\TomTicket\AddTomTicket;
use App\Services\Vendors\TomTicket\ListTickets;
use App\Services\Vendors\TomTicket\UpdateTomTicket;
use App\Services\Vendors\TomTicket\GetTicket;
use App\Services\Vendors\TomTicket\CloseTomTicket;
use App\Services\Vendors\TomTicket\ReopenTomTicket;
use App\Models\Tickets;
use App\Models\Messages;
use DateTime;
use Exception;


class syncTickets extends Command
{
    protected $signature = 'sync:tickets';
    protected $description = 'Sincronizar chamados da base local com o tomticket e os status do tomticket para a base local';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // try
        // {
        //     //Sincronizar DE: Local PARA: Tomticket
        //     print "Realizand sync da base para o tomticket.";
        //     $this->toTomticket();
        //     print 'Realizando sync do Tomticket para a base local';
        //     $this->toLocal();
        // }catch(Exception $e){
        //     return ['error' => $e->getMessage()];
        // }

    }

    private function toLocal()
    {
        $client_side = [
            'Respondido, aguardando resposta do cliente'
        ];
        $support_side = [
            'Sem atendente vinculado',
            'Aguardando interação do atendente',
            'Respondido pelo cliente, aguardando resposta'
        ];
        $action = new ListTickets;
        $tickets = $action->execute();
        $i = 0;
        foreach($tickets[$i] as $ticket){
            print "\nSincronizando chamado: " . $ticket->protocolo . ' Cliente: ' . $ticket->nomecliente . "...\n";

            /********************** ATUALIZAR status do chamado e anexos do TomTicket (deixar de usar os arquivos da base local) ****************************/
            try
            {
                $objTicket = \App\Models\Tickets::where('tom_ticket_id', $ticket->idchamado)->first();

                /******************** RESTRIÇÕES DE SINCRONISMO ********************/
                //Não sincronizar chamados encerrados
                if($objTicket->status == 'Finalizado'){
                    print "Chamado encerrado localmente\n"; 
                    continue; 
                }elseif($objTicket->reopen && !$objTicket->reopen_sync){ // não sincronizar chamados reabertos que ainda não foram abertos no tomticket
                    continue;
                } 

                if(!empty($ticket->status)) $objTicket->status = $ticket->status;
                if(in_array($ticket->descsituacao, $client_side)){
                    $objTicket->status = 'Aguardando interação do cliente';
                }elseif(in_array($ticket->descsituacao, $support_side)){
                    $objTicket->status = "Em análise pelo suporte";
                }else{
                    $objTicket->status = $ticket->descsituacao;
                }
    
                /****************************** GRAVAR HISTORICO DE MENSAGES *********************/
                print "Sincronizando historico de mensagens";
                $action = new GetTicket;
                $ticket = $action->execute($ticket->idchamado);
                foreach($ticket->historico as $message){
                    if($message->origem === "A"){ // atualizar somente mensagens de atendente 
                        $date = DateTime::createFromFormat('d/m/Y H:i', "$message->data_hora");
                        $insertMessage = Messages::updateOrCreate([
                            'message' => strip_tags($message->mensagem),
                            'ticket_id' => $objTicket->id,
                            'created_at' => $date->format('Y-m-d H:i:s'), //gravar no formato default do laravel
                            'tom_ticket_sync' => 1
                        ]);
    
                        if(!empty($message->anexos)){
                            $files = [];
                            foreach($message->anexos as $anexo){
                                array_push($files, ['path' => $anexo->url, 'name' => $anexo->nome]);
                            }
                            $message = Messages::find($insertMessage->id);
                            $message->attachement = serialize($files);
                            $message->save();
                        }else{
                            $files = null;
                            $message = Messages::find($insertMessage->id);
                            $message->attachement = serialize($files);
                            $message->save();
                        }
                    }
                }
                //salvar alterações
                $objTicket->save();
            }catch(Exception $e){
                report($e);
                continue;
            }
            
            print "\nOK\n";
            $i++;
        }
        print 'Finalizado';
    }

    private function toTomticket()
    {
        //Recuperar obj com todos os chamados que ainda não estão sincronizados com o tomticket;
        $tickets = Tickets::where('tom_ticket_id', null)->get();
        foreach($tickets as $ticket){
            print "Sincronizando chamado $ticket->id no TomTicket";
            //variaveis para o tomticket
            $vars['tom_ticket_user_id'] = $ticket->client_id;
            $vars['category'] = $ticket->category;
            $vars['message'] = $ticket->description;
            $vars['product'] = $ticket->product;
            $vars['ticket_id'] = $ticket->id;
            $files = unserialize($ticket->attachement);

            $action = new AddTomTicket;
            $action->execute($vars, $files);
            
            print "\nOK";
        }

        /************************* GRAVAR HISTORICO DE MENSAGENS ********************/
        $messages = Messages::where('tom_ticket_sync', 0)->get(); // lista com todas as mensagens que não estão sincronizadas no tomticket
        foreach($messages as $message){
            print "Sincronizando mensagens do chamado $message->ticket_id";

            $ticket = Tickets::find($message->ticket_id);
            $vars['tom_ticket_id'] = $ticket->tom_ticket_id;
            $vars['message'] = $message->message;
            $stored_files = unserialize($message->attachement);

            $action = new UpdateTomTicket;
            $update = $action->execute($vars, $stored_files);
            if($update->erro) throw new Exception($update->mensagem); // se não conseguir gravar a mensagem no tomticket, abortar com erro

            //Salvar no banco para não mais registrar no tomticket
            $message->tom_ticket_sync = 1;
            $message->save();
        }

        // Sincronizar chamados que foram encerrados pelo cliente
        $tickets = Tickets::where('status', 'Finalizado')->get();
        foreach($tickets as $ticket){
            if($ticket->end_sync) continue;
            print 'Encerrando chamado '.$ticket->id. ' no tomticket';
            $action = new CloseTomTicket;
            $action->execute($ticket->tom_ticket_id);
            $ticket->end_sync = true; 
            $ticket->save();
            print 'OK';
        }

        //Sincronizar chamados reabertos
        $tickets = Tickets::where('reopen', true)->get();
        foreach($tickets as $ticket){
            if($ticket->reopen_sync) continue; // pular chamados já sincronizados no tomticket
            #Preparar campos para reabertura no tomticket     
            $user = \App\Models\Users::where('tom_ticket_user_id', $ticket->client_id)->first();       
            $vars['tom_ticket_user_id'] = $user->tom_ticket_user_id;
            $vars['category'] = $ticket->category;
            $vars['message'] = $ticket->description;
            $vars['product'] = $ticket->product;
            $vars['tom_ticket_id'] = $ticket->tom_ticket_id;
            //Abrir novo chamado com mesmo assunto
            $action = new ReopenTomTicket;
            $tom_ticket = $action->execute($vars);
            //Alterar o ID do chamado com o tom_ticket_id novo
            $ticket->tom_ticket_id = $tom_ticket->id_chamado;
            $ticket->reopen_sync = true;
            $ticket->save();
        }

        print "Fim\n";

    }
}