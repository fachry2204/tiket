<?php

namespace App\Services;

use App\Models\{Order, Setting};
use Illuminate\Support\Facades\{Http, Mail, Log};

class NotificationService
{
    /**
     * Apply runtime SMTP configuration from settings database
     */
    private function configureSmtp(bool $ignoreEnabled = false): bool
    {
        $enabled = Setting::get('mail_enabled', '0');
        if (!$ignoreEnabled && (!$enabled || $enabled === '0' || $enabled === 'false')) {
            return false;
        }

        $host = Setting::get('mail_host');
        $port = Setting::get('mail_port', 587);
        $username = Setting::get('mail_username');
        $password = Setting::get('mail_password');
        $encryption = Setting::get('mail_encryption', 'tls');
        $fromAddress = Setting::get('mail_from_address', 'noreply@masivers.id');
        $fromName = Setting::get('mail_from_name', 'Masivers Ticketing');

        if (empty($host) || empty($username)) {
            return false;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) $port,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);

        return true;
    }

    /**
     * Send WhatsApp message via Fonnte.com Gateway API
     */
    public function sendWa(string $toPhone, string $message, bool $ignoreEnabled = false): bool
    {
        $enabled = Setting::get('wa_gateway_enabled', '0');
        if (!$ignoreEnabled && (!$enabled || $enabled === '0' || $enabled === 'false')) {
            return false;
        }

        $url = Setting::get('wa_gateway_url', 'https://api.fonnte.com/send');
        $apiKey = Setting::get('wa_gateway_api_key');

        if (empty($url) || empty($apiKey)) {
            return false;
        }

        try {
            // Clean phone number format
            $phone = preg_replace('/[^0-9]/', '', $toPhone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            // Fonnte.com & WA Gateway compatible payload format
            $payload = [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62',
                'token' => $apiKey,
                'api_key' => $apiKey,
                'phone' => $phone,
                'number' => $phone,
                'to' => $phone,
                'text' => $message,
            ];

            // Fonnte uses Authorization: TOKEN
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->timeout(10)->post($url, $payload);
            
            if ($response->failed()) {
                Log::error("Fonnte WA Gateway Error: " . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("Fonnte WA Gateway Exception: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send Email message with optional attachments
     */
    public function sendEmail(string $toEmail, string $subject, string $bodyContent, bool $ignoreEnabled = false, array $attachments = []): bool
    {
        if (!$this->configureSmtp($ignoreEnabled)) {
            return false;
        }

        try {
            Mail::html($bodyContent, function ($message) use ($toEmail, $subject, $attachments) {
                $message->to($toEmail)->subject($subject);
                foreach ($attachments as $att) {
                    $filePath = is_array($att) ? ($att['path'] ?? '') : $att;
                    $fileName = is_array($att) ? ($att['name'] ?? null) : null;
                    if (!empty($filePath) && file_exists($filePath)) {
                        if ($fileName) {
                            $message->attach($filePath, ['as' => $fileName]);
                        } else {
                            $message->attach($filePath);
                        }
                    }
                }
            });
            return true;
        } catch (\Throwable $e) {
            Log::error("SMTP Email Exception: " . $e->getMessage());
            throw $e;
        }
    }

    private function getBaseUrl(): string
    {
        $customUrl = Setting::get('app_frontend_url');
        if (!empty($customUrl)) {
            return rtrim($customUrl, '/');
        }

        try {
            if (request()->hasHeader('Host')) {
                $scheme = request()->getScheme();
                $host = request()->getHost();
                $port = request()->getPort();
                if (in_array($port, [80, 443])) {
                    return "{$scheme}://{$host}";
                }
                return "{$scheme}://{$host}:{$port}";
            }
        } catch (\Throwable $e) {}

        $envFrontend = config('app.frontend_url');
        if (!empty($envFrontend)) {
            return rtrim($envFrontend, '/');
        }

        return rtrim(config('app.url', 'http://localhost'), '/');
    }

    /**
     * Notification 1: Order Created (Pending Payment)
     */
    public function notifyOrderCreated(Order $order): void
    {
        $customer = $order->customer;
        if (!$customer) return;

        $baseUrl = $this->getBaseUrl();

        $itemsText = "";
        foreach ($order->items as $item) {
            $itemsText .= "- {$item->ticket_name} (x{$item->quantity})\n";
        }

        $bankInfo = "";
        if ($order->bankAccount) {
            $bankInfo = "Bank: {$order->bankAccount->bank_name}\n"
                      . "No. Rekening: {$order->bankAccount->account_number}\n"
                      . "Atas Nama: {$order->bankAccount->account_holder_name}\n";
        }

        $grandTotalFormatted = "Rp " . number_format((float) $order->grand_total, 0, ',', '.');
        $expiryDate = $order->expires_at ? $order->expires_at->format('d M Y, H:i') : '-';

        // --- WhatsApp Text ---
        $waMessage = "Halo Kak *{$customer->name}*,\n\n"
            . "Terima kasih telah melakukan pemesanan di *Masivers Ticketing*! 🎉\n\n"
            . "Berikut adalah rincian pesanan Anda:\n"
            . "📌 No Pesanan: *{$order->order_code}*\n"
            . "🎟️ Detail Tiket:\n{$itemsText}"
            . "💰 Total Pembayaran: *{$grandTotalFormatted}*\n"
            . "⏰ Batas Waktu Bayar: *{$expiryDate} WIB*\n\n"
            . "Silakan lakukan transfer ke rekening berikut:\n"
            . "{$bankInfo}\n"
            . "Setelah melakukan pembayaran, silakan unggah bukti transfer Anda di link berikut:\n"
            . "{$baseUrl}/konfirmasi-bayar?search={$order->order_code}\n\n"
            . "Terima kasih dan ditunggu kehadirannya!";

        // --- Email HTML ---
        $emailHtml = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <h2 style='color: #e53e3e;'>Pemesanan Tiket Berhasil Dibuat</h2>
            <p>Halo <strong>{$customer->name}</strong>,</p>
            <p>Terima kasih telah memesan tiket di <strong>Masivers Ticketing</strong>. Pesanan Anda telah kami terima dan saat ini menantikan pembayaran.</p>
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>No Pesanan</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$order->order_code}</td></tr>
                <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Total Tagihan</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd; color: #e53e3e; font-weight: bold;'>{$grandTotalFormatted}</td></tr>
                <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Batas Waktu Bayar</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$expiryDate} WIB</td></tr>
            </table>
            <h3>Instruksi Pembayaran:</h3>
            <p>Silakan transfer tepat sejumlah <strong>{$grandTotalFormatted}</strong> ke rekening:</p>
            <div style='background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                <p style='margin: 4px 0;'><strong>Bank:</strong> {$order->bankAccount?->bank_name}</p>
                <p style='margin: 4px 0;'><strong>No. Rekening:</strong> {$order->bankAccount?->account_number}</p>
                <p style='margin: 4px 0;'><strong>Atas Nama:</strong> {$order->bankAccount?->account_holder_name}</p>
            </div>
            <p style='margin-top: 20px;'>Setelah melakukan transfer, silakan konfirmasi pembayaran Anda di link berikut:</p>
            <p><a href='{$baseUrl}/konfirmasi-bayar?search={$order->order_code}' style='background: #e53e3e; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-block;'>Konfirmasi Pembayaran</a></p>
            <br>
            <p>Salam hangat,<br><strong>Tim Masivers Community</strong></p>
        </div>";

        $this->sendWa($customer->phone, $waMessage);
        $this->sendEmail($customer->email, "Konfirmasi Pesanan {$order->order_code} - Masivers Ticketing", $emailHtml);
    }

    /**
     * Notification 2: Payment Proof Uploaded (Waiting Verification)
     */
    public function notifyProofUploaded(Order $order): void
    {
        $customer = $order->customer;
        if (!$customer) return;

        $baseUrl = $this->getBaseUrl();

        $waMessage = "Halo Kak *{$customer->name}*,\n\n"
            . "Bukti pembayaran untuk No Pesanan *{$order->order_code}* sudah berhasil kami terima! 📩\n\n"
            . "Tim kami sedang melakukan verifikasi pembayaran Anda. Proses ini biasanya membutuhkan waktu maksimal 1x24 jam.\n"
            . "Kami akan menginfokan kembali setelah pembayaran Anda terverifikasi.\n\n"
            . "Cek status pesanan Anda secara berkala di:\n"
            . "{$baseUrl}/status-order/{$order->order_code}\n\n"
            . "Terima kasih atas kesabaran Anda!";

        $emailHtml = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <h2 style='color: #3182ce;'>Bukti Pembayaran Diterima</h2>
            <p>Halo <strong>{$customer->name}</strong>,</p>
            <p>Bukti transfer untuk No Pesanan <strong>{$order->order_code}</strong> telah kami terima.</p>
            <p>Saat ini tim admin sedang memverifikasi pembayaran Anda (maksimal 1x24 jam). Anda akan menerima notifikasi lanjutan setelah verifikasi selesai.</p>
            <p><a href='{$baseUrl}/status-order/{$order->order_code}' style='background: #3182ce; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-block;'>Cek Status Order</a></p>
            <br>
            <p>Salam hangat,<br><strong>Tim Masivers Community</strong></p>
        </div>";

        $this->sendWa($customer->phone, $waMessage);
        $this->sendEmail($customer->email, "Bukti Pembayaran Diterima - {$order->order_code}", $emailHtml);
    }

    /**
     * Notification 3: Payment Approved & Tickets Issued (Paid)
     */
    public function notifyPaymentApproved(Order $order): void
    {
        $customer = $order->customer;
        if (!$customer) return;

        $baseUrl = $this->getBaseUrl();

        // Build list of ticket downloads & attachments
        $ticketDownloadLinksWa = "";
        $ticketDownloadButtonsEmail = "";
        $emailAttachments = [];

        if ($order->eTickets && $order->eTickets->count() > 0) {
            $ticketDownloadLinksWa .= "📥 *DOWNLOAD FILE E-TICKET:* \n";
            foreach ($order->eTickets as $idx => $ticket) {
                $downloadUrl = "{$baseUrl}/api/v1/public/e-tickets/{$ticket->id}/download";
                $num = $idx + 1;
                $ticketDownloadLinksWa .= "{$num}. {$ticket->file_name}:\n{$downloadUrl}\n\n";

                $ticketDownloadButtonsEmail .= "<p style='margin: 8px 0;'><a href='{$downloadUrl}' style='background: #2b6cb0; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px; display: inline-block; font-size: 14px;'>⬇️ Download {$ticket->file_name}</a></p>";

                // Check local file path for email attachment
                try {
                    $realPath = \Illuminate\Support\Facades\Storage::disk('private')->path($ticket->file_path);
                    if (file_exists($realPath)) {
                        $emailAttachments[] = ['path' => $realPath, 'name' => $ticket->file_name];
                    }
                } catch (\Throwable $e) {}
            }
        }

        $waMessage = "Halo Kak *{$customer->name}*,\n\n"
            . "🎉 *E-TICKET ANDA SUDAH BISA DI-DOWNLOAD!* 🎉\n\n"
            . "Selamat! Pembayaran untuk No Pesanan *{$order->order_code}* telah **DIVERIFIKASI & LUNAS**! ✅\n\n"
            . ($ticketDownloadLinksWa ? "{$ticketDownloadLinksWa}" : "")
            . "🔗 *CEK STATUS & QR CODE TIKET:* \n"
            . "{$baseUrl}/status-order/{$order->order_code}\n\n"
            . "Tunjukkan QR Code ini kepada PIC MASIVERS di lokasi untuk penukaran tiket fisik. Anda juga bisa mengecek Email Masuk / Folder Spam Atau Whatsapp Anda secara berkala.\n\n"
            . "Sampai jumpa di lokasi acara!";

        $emailHtml = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <div style='background: #38a169; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;'>
                <h2 style='margin: 0;'>🎉 E-TICKET ANDA SUDAH BISA DI-DOWNLOAD!</h2>
            </div>
            <div style='padding: 20px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px;'>
                <p>Halo <strong>{$customer->name}</strong>,</p>
                <p>Selamat! Pembayaran Anda untuk No Pesanan <strong>{$order->order_code}</strong> telah kami terima dan terverifikasi <strong>LUNAS</strong>. Berkas E-Ticket fisik Anda kini sudah siap di-download!</p>
                
                " . ($ticketDownloadButtonsEmail ? "<div style='background: #f7fafc; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #e2e8f0;'><h3 style='margin-top:0; color: #2d3748;'>📥 Download File E-Ticket:</h3>{$ticketDownloadButtonsEmail}</div>" : "") . "

                <p style='margin-top: 20px;'>Anda juga dapat melihat rincian pesanan dan QR Code tiket pada link Status Order berikut:</p>
                <p><a href='{$baseUrl}/status-order/{$order->order_code}' style='background: #38a169; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 6px; display: inline-block; font-weight: bold;'>🎟️ Lihat Status Order & QR Code Tiket</a></p>
                
                <p style='margin-top: 20px; font-size: 13px; color: #666;'>Tunjukkan QR Code ini kepada PIC MASIVERS di lokasi untuk penukaran tiket fisik.</p>
                <br>
                <p>Sampai jumpa di event!</p>
                <p>Salam hangat,<br><strong>Tim Masivers Community</strong></p>
            </div>
        </div>";

        $this->sendWa($customer->phone, $waMessage);
        $this->sendEmail($customer->email, "🎉 E-Ticket {$order->order_code} Siap Di-download! - Masivers Ticketing", $emailHtml, false, $emailAttachments);
    }

    /**
     * Notification 4: Payment Rejected
     */
    public function notifyPaymentRejected(Order $order, string $reason): void
    {
        $customer = $order->customer;
        if (!$customer) return;

        $baseUrl = $this->getBaseUrl();

        $waMessage = "Halo Kak *{$customer->name}*,\n\n"
            . "Mohon maaf, bukti pembayaran untuk No Pesanan *{$order->order_code}* belum dapat kami verifikasi.\n"
            . "📌 Alasan: _{$reason}_\n\n"
            . "Silakan lakukan upload ulang bukti transfer yang valid melalui link berikut:\n"
            . "{$baseUrl}/konfirmasi-bayar?search={$order->order_code}\n\n"
            . "Jika ada pertanyaan, silakan hubungi tim kami. Terima kasih!";

        $emailHtml = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <h2 style='color: #e53e3e;'>Verifikasi Pembayaran Ditolak</h2>
            <p>Halo <strong>{$customer->name}</strong>,</p>
            <p>Mohon maaf, bukti pembayaran untuk No Pesanan <strong>{$order->order_code}</strong> belum dapat diverifikasi.</p>
            <div style='background: #fff5f5; border-left: 4px solid #e53e3e; padding: 12px; margin: 15px 0;'>
                <strong>Alasan Penolakan:</strong><br>{$reason}
            </div>
            <p>Anda dapat mengunggah kembali bukti transfer yang sesuai pada tombol di bawah:</p>
            <p><a href='{$baseUrl}/konfirmasi-bayar?search={$order->order_code}' style='background: #e53e3e; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-block;'>Upload Ulang Bukti Bayar</a></p>
            <br>
            <p>Salam hangat,<br><strong>Tim Masivers Community</strong></p>
        </div>";

        $this->sendWa($customer->phone, $waMessage);
        $this->sendEmail($customer->email, "Pemberitahuan Verifikasi Pembayaran {$order->order_code}", $emailHtml);
    }
}
