<?php

namespace App\Mail;

use App\Models\ClientModuleCredential;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ModuleCredentialsApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ClientModuleCredential $credential,
        public string $jwtSecret,
        public bool $isRegenerated = false
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isRegenerated
            ? "🔄 {$this->credential->module->module_name} - Secret Đã Được Tạo Lại"
            : "✅ {$this->credential->module->module_name} - Request Đã Được Duyệt";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.module-credentials-approved',
            with: [
                'credential' => $this->credential,
                'jwtSecret' => $this->jwtSecret,
                'isRegenerated' => $this->isRegenerated,
                'moduleName' => $this->credential->module->module_name,
                'clientId' => $this->credential->client_id,
                'baseUrl' => $this->credential->module->base_url,
                'docsUrl' => url($this->credential->module->docs_url),
            ],
        );
    }
}
