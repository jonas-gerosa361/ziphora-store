<?php namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use \App\Exceptions\ValidatorException;

class Utils  
{
    static function addFile($files) : array
    {
        try {
            $ret = [ ];
            foreach($files as $file) {
                $extension = $file->getClientOriginalExtension();
                if ($extension == 'exe') {
                    throw new ValidatorException ("Não são permitidos arquivos com extensão .exe");
                }
                $tmpName = uniqid() . '.' .$file->extension();
                array_push($ret, ['path' => $file->storeAs('tickets', $tmpName ), 'original_name' => $file->getClientOriginalName()]);
            }
            return $ret;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
        
    }
}
