<?php

namespace App\Mail;

use App\Models\Person;
use App\Models\Club;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Person $person,
        public Club $club,
        public string $role,
        public ?string $password = null  // Add password parameter
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->person->email,
            subject: 'Bienvenue au ' . $this->club->name . ' - Vos identifiants',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.WelcomeEmail',  
        );
    }

    public function attachments(): array
    {
        return [];
    }
}