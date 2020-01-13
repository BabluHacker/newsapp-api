<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class emailConfirm extends Mailable {

    use Queueable,
        SerializesModels;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
    //build the message.
    public function build() {
        return $this->view('mail-confirm')
            ->with($this->data);
    }
}
