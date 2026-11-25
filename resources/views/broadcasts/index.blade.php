<x-app-layout>
    <div class="space-y-6"
         x-data="{
             subject: @js(old('subject', '')),
             message: @js(old('message', '')),
             targetSegment: @js(old('target_segment', 'all')),
             applyTemplate(tpl) {
                 this.subject = tpl.subject;
                 this.message = tpl.message;
                 this.targetSegment = tpl.segment;
             }
         }">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Broadcast Email Promosi</h1>
            <p class="text-xs text-slate-600 mt-1">Kirim kampanye promo tertarget ke pelanggan berdasarkan segmentasi cerdas melalui antrean latar belakang.</p>
        </div>

        {{-- Segment Overview Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($segmentLabels as $segKey => $segLabel)
                <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-sm shadow-indigo-100/50">
                    <div class="text-xs font-semibold text-slate-500">{{ $segLabel }}</div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold text-indigo-600">{{ number_format($segmentCounts[$segKey] ?? 0) }}</span>
                        <span class="text-[11px] font-medium text-slate-500">penerima email</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Compose Broadcast Card --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-sm shadow-indigo-100/50 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Buat Kampanye Baru</h2>
                    <p class="text-xs text-slate-500">Gunakan template siap pakai atau tulis penawaran khusus Anda.</p>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($templates as $tpl)
                        <button type="button"
                                @click="applyTemplate(@js($tpl))"
                                class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition">
                            Template: {{ Str::limit($tpl['name'], 24) }}
                        </button>
                    @endforeach
                </div>
            </div>

            <form method="POST" action="{{ route('broadcasts.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Segmen Target Pelanggan *</label>
                        <select name="target_segment" x-model="targetSegment" required
                                class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($segmentLabels as $segKey => $segLabel)
                                <option value="{{ $segKey }}">{{ $segLabel }} ({{ $segmentCounts[$segKey] ?? 0 }} email)</option>
                            @endforeach
                        </select>
                        @error('target_segment') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jadwalkan Pengiriman (Opsional)</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-500 mt-1">Kosongkan untuk langsung memasukkan ke antrean kirim sekarang.</p>
                        @error('scheduled_at') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Subjek Email *</label>
                    <input type="text" name="subject" x-model="subject" required placeholder="Contoh: Promo Spesial Akhir Pekan - Diskon 15%!"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('subject') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Isi Pesan Promosi *</label>
                    <textarea name="message" x-model="message" rows="5" required placeholder="Tulis pesan promo menarik untuk pelanggan Anda..."
                              class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    @error('message') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div class="text-xs text-slate-500">
                        Dikirim sebagai: <strong class="text-slate-700">{{ $store?->effectiveSenderName() }}</strong> ({{ $store?->effectiveSenderEmail() }})
                    </div>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 active:scale-[0.99] transition">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Antrekan Broadcast Email
                    </button>
                </div>
            </form>
        </section>

        {{-- Broadcast History --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 shadow-sm shadow-indigo-100/50 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">Riwayat &amp; Jadwal Kampanye</h2>
            </div>

            @if($broadcasts->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Subjek Kampanye</th>
                                <th class="px-5 py-3">Segmen Target</th>
                                <th class="px-5 py-3 text-center">Penerima</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Waktu / Jadwal</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($broadcasts as $b)
                                @php
                                    $statusBadge = match ($b->status) {
                                        'sent' => 'bg-emerald-50 text-emerald-700',
                                        'failed' => 'bg-rose-50 text-rose-700',
                                        default => 'bg-amber-50 text-amber-700',
                                    };
                                @endphp
                                <tr class="hover:bg-indigo-50/50 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900">{{ $b->subject }}</div>
                                        <div class="text-xs text-slate-500 truncate max-w-xs">{{ Str::limit($b->message, 65) }}</div>
                                        @if($b->error_message)
                                            <div class="text-[11px] text-rose-600 mt-1">{{ Str::limit($b->error_message, 100) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-700">{{ $b->segmentLabel() }}</td>
                                    <td class="px-5 py-3.5 text-center font-semibold text-slate-900">{{ number_format($b->recipients_count) }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase {{ $statusBadge }}">
                                            {{ $b->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs text-slate-500 whitespace-nowrap">
                                        @if($b->sent_at)
                                            Terkirim: {{ $b->sent_at->format('d M Y, H:i') }}
                                        @elseif($b->scheduled_at)
                                            Jadwal: {{ $b->scheduled_at->format('d M Y, H:i') }}
                                        @else
                                            {{ $b->created_at?->format('d M Y, H:i') }}
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <form method="POST" action="{{ route('broadcasts.resend', $b) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-indigo-50 hover:text-indigo-600 transition">
                                                Kirim Ulang
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-slate-100">{{ $broadcasts->links() }}</div>
            @else
                <div class="px-5 py-10 text-center text-sm text-slate-500">
                    Belum ada kampanye broadcast email yang dibuat.
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
