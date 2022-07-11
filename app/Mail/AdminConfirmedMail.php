<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $content;
    protected $object;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content, $object)
    {
        $this->content = $content;
        $this->object = $object;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.admin_confirmed')
            ->with([
                "content" => $this->content,
                "object" => $this->object
            ]);
    }
}
