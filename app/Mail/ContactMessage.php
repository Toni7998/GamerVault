<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ContactMessage extends Mailable
{
    public $name;
    public $email;
    public $userMessage;

    public function __construct($name, $email, $userMessage)
    {
        $this->name = $name;
        $this->email = $email;
        $this->userMessage = $userMessage;
    }

    public function build()
    {
        return $this->subject('Nou missatge de contacte')
            ->view('emails.contact')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'userMessage' => $this->userMessage, // nuevo nombre
            ]);
    }
}
