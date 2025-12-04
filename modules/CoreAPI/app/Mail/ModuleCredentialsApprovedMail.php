<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

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
