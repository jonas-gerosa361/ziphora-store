<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Vendors\TomTicket\UpdateTomTicket;

class JobUpdateTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $vars;
    private $files;

    public function __construct($vars, $files)
    {
        $this->vars = $vars;
        $this->files = $files;
    }

    function handle()
    {
        $vars = $this->vars;
        $files = $this->files;
        $action = new UpdateTomTicket;
        $action->execute($vars, $files);
    }

}
