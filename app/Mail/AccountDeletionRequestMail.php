<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $role,
        public int $accountId,
        public string $accountName,
        public string $accountEmail,
        public ?string $reason = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Action Required] Account Deletion Request - {$this->role} #{$this->accountId}",
        );
    }

    public function content(): Content
    {
        $roleLabel = ucfirst($this->role);
        $reasonText = $this->reason ?: 'No reason provided';

        $html = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <h2 style='color: #dc3545;'>Account Deletion Request</h2>
                <p>An account deletion request has been submitted by a <strong>{$roleLabel}</strong> partner:</p>
                <ul>
                    <li><strong>Role:</strong> {$roleLabel}</li>
                    <li><strong>Account ID:</strong> {$this->accountId}</li>
                    <li><strong>Name:</strong> {$this->accountName}</li>
                    <li><strong>Email:</strong> {$this->accountEmail}</li>
                    <li><strong>Reason:</strong> {$reasonText}</li>
                </ul>
                <p>Please review partner obligations, outstanding balances, and active transactions before processing.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #888; font-size: 12px;'>Bakala Express Admin System Notification</p>
            </div>
        ";

        return new Content(
            htmlString: $html,
        );
    }
}
