<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBroadcastRequest;
use App\Jobs\SendBroadcastEmailJob;
use App\Models\Broadcast;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;
        $vipThreshold = $store?->vip_points_threshold ?? 1000;

        $segmentCounts = [
            Broadcast::SEGMENT_ALL => Customer::query()->emailable()->count(),
            Broadcast::SEGMENT_INACTIVE_30_DAYS => Customer::query()->emailable()->segment(Broadcast::SEGMENT_INACTIVE_30_DAYS, $vipThreshold)->count(),
            Broadcast::SEGMENT_VIP => Customer::query()->emailable()->segment(Broadcast::SEGMENT_VIP, $vipThreshold)->count(),
        ];

        $broadcasts = Broadcast::query()
            ->latest()
            ->paginate(10);

        $templates = [
            [
                'name' => 'Promo Kembali Berkunjung (Pasif >30 Hari)',
                'segment' => Broadcast::SEGMENT_INACTIVE_30_DAYS,
                'subject' => 'Kami Rindu Kunjungan Anda! Ada Diskon Spesial Menanti',
                'message' => "Sudah lebih dari 30 hari sejak kunjungan terakhir Anda.\n\nNikmati potongan spesial 15% untuk semua layanan minggu ini dengan menunjukkan email ini kepada kasir kami. Jangan lupa, poin loyalitas Anda juga masih bisa ditukarkan!",
            ],
            [
                'name' => 'Apresiasi Member VIP',
                'segment' => Broadcast::SEGMENT_VIP,
                'subject' => 'Terima Kasih Member VIP! Klaim Bonus Layanan Eksklusif',
                'message' => "Sebagai pelanggan VIP setia kami, terima kasih atas kepercayaan Anda selama ini.\n\nDapatkan prioritas antrean dan bonus poin 2x lipat untuk transaksi Anda akhir pekan ini!",
            ],
            [
                'name' => 'Promo Akhir Bulan Semua Pelanggan',
                'segment' => Broadcast::SEGMENT_ALL,
                'subject' => 'Promo Spesial Akhir Bulan — Hemat & Kumpulkan Poin!',
                'message' => "Saatnya memanjakan diri dengan promo akhir bulan dari toko kami!\n\nSetiap transaksi minggu ini berhak mendapatkan poin loyalitas yang dapat ditukar menjadi diskon langsung pada kunjungan berikutnya.",
            ],
        ];

        return view('broadcasts.index', [
            'broadcasts' => $broadcasts,
            'segmentCounts' => $segmentCounts,
            'segmentLabels' => Broadcast::segmentLabels(),
            'templates' => $templates,
            'store' => $store,
        ]);
    }

    public function store(StoreBroadcastRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $broadcast = Broadcast::create([
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'target_segment' => $validated['target_segment'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => 'pending',
        ]);

        if (empty($validated['scheduled_at'])) {
            SendBroadcastEmailJob::dispatch($broadcast->id);

            return redirect()
                ->route('broadcasts.index')
                ->with('success', 'Kampanye broadcast email telah dimasukkan ke antrean pengiriman latar belakang.');
        }

        return redirect()
            ->route('broadcasts.index')
            ->with('success', 'Kampanye broadcast email berhasil dijadwalkan untuk ' . $broadcast->scheduled_at->format('d M Y H:i') . '.');
    }

    public function resend(Broadcast $broadcast): RedirectResponse
    {
        $broadcast->update([
            'status' => 'pending',
            'error_message' => null,
        ]);

        SendBroadcastEmailJob::dispatch($broadcast->id);

        return redirect()
            ->route('broadcasts.index')
            ->with('success', "Kampanye \"{$broadcast->subject}\" dijadwalkan ulang untuk dikirim.");
    }
}
