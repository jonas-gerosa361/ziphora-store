<?php

namespace App\Helpers;

class Certbot {

    public static function execute($host)
    {

        try
        {
            shell_exec("echo 'server {
    server_name $host;
    include sites-available/default.conf;
}' > /etc/nginx/sites-available/$host");
    
            // Criar link de sites-available para sites-enabled
            shell_exec("ln -s /etc/nginx/sites-available/$host /etc/nginx/sites-enabled/$host");
            shell_exec("nginx -s reload");
    
            // Criar certificado digital
            shell_exec("certbot --nginx -d $host");

            return ['success' => true, 'message' => "Certificado gerado para o host $host"];

        }catch(Exception $e){
            report($e);
            return ['success' => false, 'message' => $e->getMessage()];
        }
        
    }
    
}