<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order #' . $this->order->id . ' Cancelled — Bakala Express',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_cancelled',
            with: [
                'order' => $this->order->load(['items', 'seller']),
                'userName' => $this->order->user?->name ?? 'Customer',
            ],
        );
    }
}
