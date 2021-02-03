<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

// Specific
use \GuzzleHttp\Exception\GuzzleException;
use \GuzzleHttp\Client;
use App\Jobs\JobAddTicket;
use App\Services\Vendors\TomTicket\AddTomTicket;


class run extends Command
{
    protected $signature = 'run:test';
    protected $description = 'Testes personalizados';

    public function handle()
    {

    }
}