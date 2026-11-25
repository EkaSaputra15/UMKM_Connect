<?php

namespace App\Http\Controllers;

use App\Mail\BroadcastPromoMail;
use App\Models\Broadcast;
use App\Models\Customer;
use App\Services\StoreMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class StoreSettingController extends Controller
{
    public function edit(Request $request): View
    {
        $store = $request->user()->store;

        return view('settings.edit', [
            'store' => $store,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $store = $request->user()->store;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'sender_email' => ['nullable', 'email', 'max:255'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', Rule::in(['tls', 'ssl'])],
            'point_value_idr' => ['required', 'integer', 'min:1', 'max:100000'],
            'vip_points_threshold' => ['required', 'integer', 'min:10', 'max:1000000'],
        ]);

        $smtpHost = isset($validated['smtp_host']) ? trim($validated['smtp_host']) : null;
        $smtpUsername = isset($validated['smtp_username']) ? trim($validated['smtp_username']) : null;

        $data = [
            'name' => trim($validated['name']),
            'business_type' => trim($validated['business_type']),
            'address' => $validated['address'] ?? null,
            'sender_name' => isset($validated['sender_name']) ? trim($validated['sender_name']) : null,
            'sender_email' => isset($validated['sender_email']) ? trim($validated['sender_email']) : null,
            'smtp_host' => $smtpHost,
            'smtp_port' => $validated['smtp_port'] ?? null,
            'smtp_username' => $smtpUsername,
            'smtp_encryption' => $validated['smtp_encryption'] ?? null,
            'point_value_idr' => $validated['point_value_idr'],
            'vip_points_threshold' => $validated['vip_points_threshold'],
        ];

        if (! empty($validated['smtp_password'])) {
            $smtpPassword = trim($validated['smtp_password']);
            if ($smtpHost && str_contains(strtolower($smtpHost), 'gmail.com')) {
                $smtpPassword = preg_replace('/\s+/', '', $smtpPassword) ?? $smtpPassword;
            }
            $data['smtp_password'] = $smtpPassword;
        }

        if ($request->hasFile('logo')) {
            if ($store->logo_path) {
                Storage::disk('public')->delete($store->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('store-logos', 'public');
        }

        $store->update($data);

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Pengaturan profil toko, loyalitas, dan konfigurasi email berhasil disimpan.');
    }

    public function sendTestEmail(Request $request, StoreMailer $mailer): RedirectResponse
    {
        $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        $store = $request->user()->store;

        $dummyBroadcast = new Broadcast([
            'store_id' => $store->id,
            'subject' => 'Uji Koneksi Email SMTP - ' . $store->name,
            'message' => "Selamat! Konfigurasi pengirim email untuk {$store->name} berfungsi dengan baik.\n\nNota digital dan broadcast promosi siap dikirim ke pelanggan Anda.",
            'target_segment' => Broadcast::SEGMENT_ALL,
        ]);
        $dummyBroadcast->setRelation('store', $store);

        $dummyCustomer = new Customer([
            'store_id' => $store->id,
            'name' => $request->user()->name,
            'email' => $request->input('test_email'),
            'total_points' => 500,
        ]);
        $dummyCustomer->setRelation('store', $store);

        try {
            $mailer->send(
                $store,
                $request->input('test_email'),
                new BroadcastPromoMail($dummyBroadcast, $dummyCustomer)
            );

            return back()->with('success', 'Email uji coba berhasil dikirim ke ' . $request->input('test_email') . '.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal mengirim email uji coba: ' . $e->getMessage());
        }
    }
}
