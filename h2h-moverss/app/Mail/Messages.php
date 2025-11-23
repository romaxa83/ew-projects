<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Messages extends Mailable
{
    use Queueable, SerializesModels;

    private $data, $tpl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($tpl, $data)
    {
        $this->tpl = $tpl;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown($this->tpl)
            ->subject($this->data['subject'] ?? 'Sys Message')
            ->with(['data' => $this->data]);
    }

}
