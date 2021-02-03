<?php namespace App\Helpers;

use Redirect;

class Crypt {

    static function encrypt($str,$key=false) {

        if(empty($str)) return false;

        $session_id = session()->getId();

        if(!$key) $key = $session_id ? $session_id : env('APP_KEY');
        $result = '';

        for($i=0; $i<strlen($str); $i++) {
            $char = substr($str, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result.=$char;
        }

        $enc_str = Crypt::strToHex(urlencode(base64_encode($result))) . "." .md5($str);
         
        return $enc_str;

    }
      
    static function decrypt($str,$key=false){

        if(empty($str)) return false;

        $session_id = session()->getId();

        if(!$key) $key = $session_id ? $session_id : env('APP_KEY');
 
        $pieces = explode('.',$str);
        $str = $pieces[0];
        $md5 = $pieces[1];

        $str2 = base64_decode(urldecode(Crypt::hexToStr($str)));
        $result = '';
        for($i=0; $i<strlen($str2); $i++) {
            $char = substr($str2, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)-ord($keychar));
            $result.=$char;
        }

        // Se o md5 das variáveis não bater, desloga
        if($md5 != md5($result)) return redirect('/logout');
        
        return $result;

    }

    private static function strToHex($string) {
        $hex='';
        for ($i=0; $i < strlen($string); $i++)
        {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }
    
    private static function hexToStr($hex) {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2)
        {
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
    
}