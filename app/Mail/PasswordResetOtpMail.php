<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $name = 'User'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Password Reset OTP - Bakala Express',
        );
    }

    public function content(): Content
    {
        $html = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <h2 style='color: #007bff; text-align: center;'>Bakala Express</h2>
                <p>Hello {$this->name},</p>
                <p>You requested to reset your password. Use the verification OTP code below:</p>
                <div style='font-size: 28px; font-weight: bold; letter-spacing: 5px; color: #111; text-align: center; margin: 25px 0; padding: 15px; background-color: #f7f9fc; border-radius: 6px;'>
                    {$this->otp}
                </div>
                <p>This code will expire in 15 minutes. If you did not make this request, please ignore this email.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #888; font-size: 12px; text-align: center;'>Thank you for using Bakala Express.</p>
            </div>
        ";

        return new Content(
            htmlString: $html,
        );
    }
}
