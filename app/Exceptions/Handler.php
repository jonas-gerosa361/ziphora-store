<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

//Specific
use Mail;
use Exception;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // $this->reportable(function (Exception $e) {
        //     $this->sendAlert($e);
        // });
    }

    private function sendAlert($exception)
    {
        $user = (object) [ 
            'name' => 'Jonas Gerosa',
            'email' => 'jonas.gerosa@datasafer.com.br'
        ];
        Mail::to($user->email)->send(new \App\Mail\ExceptionFound($exception));
    }
}
