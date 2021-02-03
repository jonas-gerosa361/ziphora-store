<?php 

namespace App\Services\Kbs;

use Exception;
use \App\Models\Faqs;

class ListPublicFaqs
{
    function execute($vars=false)
    {          
        //se tiver pesquisa ativa, buscar na ordem 1. key_words, 2. name, 3. message
        if (!empty($vars['search']) ) {
            $faqsKw = Faqs::where('visibility', 'public')
                        ->where('key_words', 'like', "%{$vars['search']}%")
                        ->orderBy('thumbs_up', 'DESC')
                        ->take(10)
                        ->get();
            $faqsName = Faqs::where('visibility', 'public')
                            ->where('name', 'like', "%{$vars['search']}%")
                            ->orderBy('thumbs_up', 'DESC')
                            ->take(10)
                            ->get();
            $faqsMessage = Faqs::where('visibility', 'public')
                                ->where('message', 'like', "%{$vars['search']}%")
                                ->orderBy('thumbs_up', 'DESC')
                                ->take(10)
                                ->get();
            //merge das queries
            $faqs = $faqsKw->merge($faqsName)->merge($faqsMessage);
            $faqs->search = $vars['search'];
            return $faqs;
        }

        $faqs = Faqs::where('visibility', 'public')
                    ->orderBy('thumbs_up', 'DESC')
                    ->take(15)
                    ->get();
        $faqs->search = null;
        return $faqs;
    }
}