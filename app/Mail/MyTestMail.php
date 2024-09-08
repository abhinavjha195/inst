<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MyTestMail extends Mailable
{
    use Queueable, SerializesModels;
	public $details;
	
    public $sub;
    public $emailcontent;
	
	public function __construct($sub, $emailcontent) {
        $this->sub = $sub;
        $this->details = $emailcontent;
    }

    public function build() {
		return $this->subject($this->sub)->view('emails.template');
    }
}
