<?php 

namespace App\Services\Kbs;

use Exception;
use \App\Models\Faqs;

class ListFaqs
{
    /**
     * Se não estiver logado retorna as FAQs publicas, caso contrário tras todas as FAQ's externas (FAQ's internas são visiveis somente para o suporte)
     * @var array 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function execute($vars=false): \Illuminate\Database\Eloquent\Collection
    {          
        // se não estiver logado, buscar faqs publicas
        if (!auth()->check()) {
            $action = new ListPublicFaqs;
            return $action->execute($vars);
        } 

        //se tiver pesquisa ativa, buscar na ordem 1. key_words, 2. name, 3. message
        if (!empty($vars['search'])) {
            $faqsKw = Faqs::where('visibility', '!=', 'internal')
                        ->where('key_words', 'like', "%{$vars['search']}%")
                        ->orderBy('thumbs_up', 'DESC')
                        ->take(10)
                        ->get();
            $faqsNm = Faqs::where('visibility' , '!=', 'internal')
                        ->where('name', 'like', "%{$vars['search']}%")
                        ->orderBy('thumbs_up', 'DESC')
                        ->take(10)
                        ->get();
            $faqsMs = Faqs::where('visibility', '!=', 'internal')
                        ->where('message', 'like', "%{$vars['search']}%")
                        ->orderBy('thumbs_up', 'DESC')
                        ->take(10)
                        ->get();
            //merge das 3 queries
            $faqs = $faqsKw->merge($faqsNm)->merge($faqsMs);
            $faqs->search = $vars['search'];
            return $faqs;
        }

        //return default        
        $faqs = Faqs::where('visibility', '!=', 'internal')
                    ->orderBy('thumbs_up', 'DESC')
                    ->take(15)
                    ->get();
        $faqs->search = null;
        return $faqs;
    }
}