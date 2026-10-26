<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otpCode,
        public string $recipientEmail
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address', 'kafkalyandra@gmail.com');
        $fromName = config('mail.from.name', 'PT Green Pasundan Agriculture');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [
                new Address($fromAddress, $fromName),
            ],
            subject: "{$this->otpCode} adalah kode verifikasi akun AgroOrder GPA Anda",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-otp',
            text: 'emails.registration-otp-text',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
