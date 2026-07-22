<?php

namespace App\Services;

use App\Models\{Order, Setting};
use Illuminate\Support\Facades\{Http, Mail, Log};

class NotificationService
{
    /**
     * Apply runtime SMTP configuration from settings database
     */
    private function configureSmtp(): bool
    {
        $enabled = Setting::get('mail_enabled', '0');
        if (!$enabled || $enabled === '0' || $enabled === 'false') {
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
     * Send WhatsApp message via FlowKirim.com Gateway API
     */
    public function sendWa(string $toPhone, string $message): bool
    {
        $enabled = Setting::get('wa_gateway_enabled', '0');
        if (!$enabled || $enabled === '0' || $enabled === 'false') {
            return false;
        }

        $url = Setting::get('wa_gateway_url');
        $apiKey = Setting::get('wa_gateway_api_key');
        $sender = Setting::get('wa_gateway_sender');

        if (empty($url) || empty($apiKey)) {
            return false;
        }

        try {
            // Clean phone number format
            $phone = preg_replace('/[^0-9]/', '', $toPhone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            // FlowKirim.com compatible payload format
            $payload = [
                'token' => $apiKey,
                'api_key' => $apiKey,
                'to' => $phone,
                'target' => $phone,
                'phone' => $phone,
                'number' => $phone,
                'message' => $message,
                'text' => $message,
                'sender' => $sender,
                'device_id' => $sender,
            ];

            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->post($url, $payload);
            
            if ($response->failed()) {
                Log::error("FlowKirim WA Gateway Error: " . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("FlowKirim WA Gateway Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Email message
     */
    public function sendEmail(string $toEmail, string $subject, string $bodyContent): bool
    {
        if (!$this->configureSmtp()) {
            return false;
        }

        try {
            Mail::html($bodyContent, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)->subject($subject);
            });
            return true;
        } catch (\Throwable $e) {
            Log::error("SMTP Email Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notification 1: Order Created (Pending Payment)
     */
    public function notifyOrderCreated(Order $order): void
    {
        $customer = $order->customer;
        if (!$customer) return;

        $itemsText = "";
        foreach ($order->items as $item) {
            $itemsText .= "- {$item->ticket_name} (x{$item->quantity})\n";
        }

        $bankInfo = "";
        if ($order->bankAccount) {
            $bankInfo = "Buka Rekening: {$order->bankAccount->bank_name}\n"
                      . "No. Rekening: {$order->bankAccount->account_number}\n"
                      . "Atas Nama: {$order->bankAccount->account_holder}\n";
        }

        $grandTotalFormatted = "Rp " . number_format((float) $order->grand_total, 0, ',', '.');
        $expiryDate = $order->expires_at ? $order->expires_at->format('d M Y, H:i') : '-';

        // --- WhatsApp Text ---
        $waMessage = "Halo Kak *{$customer->name}*,\n\n"
            . "Terima kasih telah melakukan pemesanan di *Masivers Ticketing*! 🎉\n\n"
            . "Berikut adalah rincian pesanan Anda:\n"
            . "📌 Kode Order: *{$order->order_code_full}*\n"
            . "🎟️ Detail Tiket:\n{$itemsText}"
            . "💰 Total Pembayaran: *{$grandTotalFormatted}*\n"
            . "⏰ Batas Waktu Bayar: *{$expiryDate} WIB*\n\n"
            . "Silakan lakukan transfer ke rekening berikut:\n"
            . "{$bankInfo}\n"
            . "Setelah melakukan pembayaran, silakan unggah bukti transfer Anda di link berikut:\n"
            . config('app.frontend_url', config('app.url')) . "/konfirmasi-bayar?search={$order->order_code_full}\n\n"
            . "Terima kasih dan ditunggu kehadirannya!";

        // --- Email HTML ---
        $emailHtml = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <h2 style='color: #e53e3e;'>Pemesanan Tiket Berhasil Dibuat</h2>
            <p>Halo <strong>{$customer->name}</strong>,</p>
            <p>Terima kasih telah memesan tiket di <strong>Masivers Ticketing</strong>. Pesanan Anda telah kami terima dan saat ini menantikan pembayaran.</p>
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Kode Order</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$order->order_code_full}</td></tr>
                <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Total Tagihan</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd; color: #e53e3e; font-weight: bold;'>{$grandTotalFormatted}</td></tr>
                <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Batas Waktu Bayar</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$expiryDate} WIB</td></tr>
            </table>
            <h3>Instruksi Pembayaran:</h3>
            <p>Silakan transfer tepat sejumlah <strong>{$grandTotalFormatted}</strong> ke rekening:</p>
            <div style='background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                <p style='margin: 4px 0;'><strong>Bank:</strong> {$order->bankAccount?->bank_name}</p>
                <p style='margin: 4px 0;'><strong>No. Rekening:</strong> {$order->bankAccount?->account_number}</p>
                <p style='margin: 4px 0;'><strong>Atas Nama:</strong> {$order->bankAccount?->account_holder}</p>
            </div>
            <p style='margin-top: 20px;'>Setelah melakukan transfer, silakan konfirmasi pembayaran Anda di link berikut:</p>
            <p><a href='" . config('app.frontend_url', config('app.url')) . "/konfirmasi-bayar?search={$order->order_code_full}' style='background: #e53e3e; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-block;'>Konfirmasi Pembayaran</a></p>
            <br>
            <p>Salam hangat,<br><strong>Tim Masivers Community</strong></p>
        </div>";

        $this->sendWa($customer->phone, $waMessage);
        $this->sendEmail($customer->email, "Konfirmasi Pesanan {$order->order_code_full} - Masivers Ticketing", $emailHtml);
    }

    /**
     * Notification 2: Payment Proof Uploaded (Waiting Verification)
     */
    public function notifyProofUploaded(Order $order): void
    {
        $customer = $order->customer;
        if (!$customer) return;

        $waMessage = "Halo Kak *{$customer->name}*,\n\n"
            . "Bukti pembayaran untuk order *{$order->order_code_full}* sudah berhasil kami terima! 📩\n\n"
            . "Tim kami sedang melakukan verifikasi pembayaran Anda. Proses ini biasanya membutuhkan waktu maksimal 1x24 jam.\n"
            . "Kami akan menginfokan kembali setelah pembayaran Anda terverifikasi.\n\n"
            . "Cek status pesanan Anda secara berkala di:\n"
            . config('app.frontend_url', config('app.url')) . "/status-order/{$order->order_code_full}\n\n"
            . "Terima kasih atas kesabaran Anda!";

        $emailHtml = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <h2 style='color: #3182ce;'>Bukti Pembayaran Diterima</h2>
            <p>Halo <strong>{$customer->name}</strong>,</p>
            <p>Bukti transfer untuk pesanan <strong>{$order->order_code_full}</strong> telah kami terima.</p>
            <p>Saat ini tim admin sedang memverifikasi pembayaran Anda (maksimal 1x24 jam). Anda akan menerima notifikasi lanjutan setelah verifikasi selesai.</p>
            <p><a href='" . config('app.frontend_url', config('app.url')) . "/status-order/{$order->order_code_full}' style='background: #3182ce; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-block;'>Cek Status Order</a></p>
            <br>
            <p>Salam hangat,<br><strong>Tim Masivers Community</strong></p>
        </div>";

        $this->sendWa($customer->phone, $waMessage);
        $this->sendEmail($customer->email, "Bukti Pembayaran Diterima - {$order->order_code_full}", $emailHtml);
    }

    /**
     * Notification 3: Payment Approved & Tickets Issued (Paid)
     */
    public function notifyPaymentApproved(Order $order): void
    {
        $customer = $order->customer;
        if (!$customer) return;

        $waMessage = "Halo Kak *{$customer->name}*,\n\n"
            . "Selamat! Pembayaran untuk pesanan *{$order->order_code_full}* telah **DIVERIFIKASI & LUNAS**! 🎉✅\n\n"
            . "Mohon klik link berikut untuk melihat data pesanan dan e-tiket Anda:\n"
            . config('app.frontend_url', config('app.url')) . "/status-order/{$order->order_code_full}\n\n"
            . "Simpan e-tiket ini dan tunjukkan QR Code pada saat check-in di lokasi acara.\n"
            . "Sampai jumpa di lokasi acara!";

        $emailHtml = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <h2 style='color: #38a169;'>Pembayaran Berhasil & E-Tiket Terbit!</h2>
            <p>Halo <strong>{$customer->name}</strong>,</p>
            <p>Pembayaran Anda untuk pesanan <strong>{$order->order_code_full}</strong> telah kami terima dan diverifikasi.</p>
            <p>E-tiket Anda kini telah aktif. Silakan klik tombol di bawah untuk melihat rincian pesanan dan e-tiket Anda:</p>
            <p><a href='" . config('app.frontend_url', config('app.url')) . "/status-order/{$order->order_code_full}' style='background: #38a169; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 6px; display: inline-block; font-weight: bold;'>Lihat Pesanan & E-Tiket</a></p>
            <br>
            <p>Sampai jumpa di event!</p>
            <p>Salam hangat,<br><strong>Tim Masivers Community</strong></p>
        </div>";

        $this->sendWa($customer->phone, $waMessage);
        $this->sendEmail($customer->email, "Pembayaran Diterima - E-Tiket {$order->order_code_full} Aktif!", $emailHtml);
    }

    /**
     * Notification 4: Payment Rejected
     */
    public function notifyPaymentRejected(Order $order, string $reason): void
    {
        $customer = $order->customer;
        if (!$customer) return;

        $waMessage = "Halo Kak *{$customer->name}*,\n\n"
            . "Mohon maaf, bukti pembayaran untuk pesanan *{$order->order_code_full}* belum dapat kami verifikasi.\n"
            . "📌 Alasan: _{$reason}_\n\n"
            . "Silakan lakukan upload ulang bukti transfer yang valid melalui link berikut:\n"
            . config('app.frontend_url', config('app.url')) . "/konfirmasi-bayar?search={$order->order_code_full}\n\n"
            . "Jika ada pertanyaan, silakan hubungi tim kami. Terima kasih!";

        $emailHtml = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <h2 style='color: #e53e3e;'>Verifikasi Pembayaran Ditolak</h2>
            <p>Halo <strong>{$customer->name}</strong>,</p>
            <p>Mohon maaf, bukti pembayaran untuk pesanan <strong>{$order->order_code_full}</strong> belum dapat diverifikasi.</p>
            <div style='background: #fff5f5; border-left: 4px solid #e53e3e; padding: 12px; margin: 15px 0;'>
                <strong>Alasan Penolakan:</strong><br>{$reason}
            </div>
            <p>Anda dapat mengunggah kembali bukti transfer yang sesuai pada tombol di bawah:</p>
            <p><a href='" . config('app.frontend_url', config('app.url')) . "/konfirmasi-bayar?search={$order->order_code_full}' style='background: #e53e3e; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-block;'>Upload Ulang Bukti Bayar</a></p>
            <br>
            <p>Salam hangat,<br><strong>Tim Masivers Community</strong></p>
        </div>";

        $this->sendWa($customer->phone, $waMessage);
        $this->sendEmail($customer->email, "Pemberitahuan Verifikasi Pembayaran {$order->order_code_full}", $emailHtml);
    }
}
