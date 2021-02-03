<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExceptionFound extends Mailable
{
    use Queueable, SerializesModels;

    private $exception;

    public function __construct($exception)
    {
        $this->exception = $exception;
    }

    public function build()
    {
        $exception = $this->exception;
        return $this->markdown('mails.exception')
            ->with('exception', $exception);
    }
}
