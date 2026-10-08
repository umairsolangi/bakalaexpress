<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewOrderNotification extends Notification
{
    use Queueable;

    protected $sellerName;
    protected $orderId;
    protected $orderDetails;

    public function __construct($sellerName, $orderId, $orderDetails)
    {
        $this->sellerName = $sellerName;
        $this->orderId = $orderId;
        $this->orderDetails = json_decode($orderDetails, true); // Decode JSON to array
    }

    public function toArray($notifiable)
    {
        return [
            'seller_name' => $this->sellerName,
            'order_id' => $this->orderId,
            'order_details' => $this->orderDetails,
            'message' => "Order #{$this->orderId} has been placed by {$this->sellerName}.",
        ];
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; 
    }

    public function toDatabase($notifiable)
    {
        return [
            'seller_name' => $this->sellerName,
            'order_id' => $this->orderId,
            'order_details' => $this->orderDetails,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Order Received')
            ->view('emails.new_order', [
                'sellerName' => $this->sellerName,
                'orderId' => $this->orderId,
                'orderDetails' => is_string($this->orderDetails) 
                    ? json_decode($this->orderDetails, true) 
                    : $this->orderDetails,
            ]);
    }
    
}
