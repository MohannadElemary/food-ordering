<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $ingredients;

    public function __construct(array $ingredients)
    {
        $this->ingredients = $ingredients;
    }

    public function build(): static
    {
        return $this->view('emails.stock_alert')
            ->subject('Stock Alert: Ingredients Low')
            ->with(['ingredients' => $this->ingredients]);
    }
}
