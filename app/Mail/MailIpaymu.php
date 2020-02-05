<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailIpaymu extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $order;

    public function __construct($order)
    {
        
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $locale = $this->order->user->locale?:'en';
        $view = "emails.$locale.accept_purchase";
        if(!view()->exists($view)) {
            $view = "emails.en.accept_purchase";
        }
        return $this->view($view);
    }
}
