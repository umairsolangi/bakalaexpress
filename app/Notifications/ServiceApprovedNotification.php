<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceApprovedNotification extends Notification
{
    use Queueable;

    private $serviceName;
    private $serviceId;

    /**
     * Create a new notification instance.
     *
     * @param string $serviceName
     * @param int $serviceId
     * @return void
     */
    public function __construct($serviceName, $serviceId)
    {
        $this->serviceName = $serviceName;
        $this->serviceId = $serviceId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Service Approved')
            ->line("Congratulations! Your service '{$this->serviceName}' has been approved.")
            ->line("Your service is now available for customers to view and order.")
            ->action('View Your Services', url('/seller/panel'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'service_id' => $this->serviceId,
            'service_name' => $this->serviceName,
            'message' => "Your service '{$this->serviceName}' has been approved.",
            'type' => 'service_approved'
        ];
    }
} 