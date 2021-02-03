<?php 

namespace App\Services\Tickets;

use Exception;
use \App\Models\Tickets;

class ListTickets
{
    function execute(?string $situation): \Illuminate\Database\Eloquent\Collection
    {          
        if (empty($situation)) { 
            $tickets = Tickets::where('company_name', auth()->user()->company->name)
                ->where('situation', '!=', 'Finalizado')
                ->take(20)
                ->latest()
                ->get();
            $tickets->situation = 'Em andamento';
            return $tickets;
        } elseif ($situation === 'Todos') {
            $tickets = Tickets::where('company_name', auth()->user()->company->name)
                ->take(20)
                ->latest()
                ->get();
            $tickets->situation = 'Todos';
            return $tickets; 
        } elseif ($situation === 'Em andamento') {
            $tickets = Tickets::where('company_name', auth()->user()->company->name)
                ->where('situation', '!=', 'Finalizado')
                ->take(20)
                ->latest()
                ->get();
            $tickets->situation = 'Em andamento';
            return $tickets;
        } 

        $tickets = Tickets::where('company_name', auth()->user()->company->name)
            ->where('situation', 'Finalizado')
            ->take(100)
            ->latest()
            ->get();
        $tickets->situation = 'Finalizado';
        return $tickets;
    }
}
