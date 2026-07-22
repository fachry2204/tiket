<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json(['data' => $settings]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable',
        ]);

        foreach ($data['settings'] as $key => $value) {
            Setting::set($key, (string) ($value ?? ''));
        }

        return response()->json(['message' => 'Pengaturan berhasil disimpan.']);
    }

    public function testSmtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2 style='color: #38a169;'>Uji Coba SMTP Berhasil! 🎉</h2>
            <p>Pengaturan SMTP email pada sistem <strong>Masivers Ticketing</strong> telah berfungsi dengan baik.</p>
            <p><small>Waktu Uji Coba: " . now()->format('d M Y, H:i:s') . " WIB</small></p>
        </div>";

        $success = $this->notificationService->sendEmail($request->email, "Uji Coba SMTP - Masivers Ticketing", $body);

        if ($success) {
            return response()->json(['message' => 'Email uji coba berhasil dikirim. Silakan periksa kotak masuk/spam Anda.']);
        }

        return response()->json(['message' => 'Gagal mengirim email uji coba. Periksa kembali konfigurasi SMTP Host, Username, dan Password.'], 422);
    }

    public function testWa(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $message = "Halo! Ini adalah pesan uji coba dari *Masivers Ticketing* via FlowKirim.com WhatsApp Gateway. 🎉\n\n"
                 . "Konfigurasi WhatsApp Gateway Anda telah berjalan dengan sukses! ✅\n"
                 . "Waktu: " . now()->format('d M Y, H:i:s') . " WIB";

        $success = $this->notificationService->sendWa($request->phone, $message);

        if ($success) {
            return response()->json(['message' => 'Pesan WhatsApp uji coba berhasil dikirim via FlowKirim.com.']);
        }

        return response()->json(['message' => 'Gagal mengirim WhatsApp uji coba. Periksa kembali URL Endpoint, API Key, dan Nomor Pengirim FlowKirim.'], 422);
    }
}
