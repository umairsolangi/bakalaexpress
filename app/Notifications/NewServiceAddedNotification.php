<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;

class NewServiceAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $serviceName;
    private $sellerName;
    private $sellerId;
    private $serviceId;

    public function __construct($serviceName, $sellerName, $sellerId, $serviceId)
    {
        $this->serviceName = $serviceName;
        $this->sellerName = $sellerName;
        $this->sellerId = $sellerId;
        $this->serviceId = $serviceId;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "New service '{$this->serviceName}' added by {$this->sellerName}.",
            'service_url' => url("/sellers/{$this->sellerId}/services")
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line("New service '{$this->serviceName}' added by {$this->sellerName}.")
            ->action('View Service', url("/sellers/{$this->sellerId}/services"));
    }
}