<?php

namespace App\Mail;

use App\Models\Driver;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DriverWelcomePasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public Driver $driver;

    public string $temporaryPassword;

    public function __construct(Driver $driver, string $temporaryPassword)
    {
        $this->driver = $driver;
        $this->temporaryPassword = $temporaryPassword;
    }

    public function build()
    {
        return $this->subject('Your Driver Account Password')
            ->view('emails.driver_welcome_password');
    }
}
