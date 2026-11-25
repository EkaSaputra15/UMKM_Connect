<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Digital {{ $transaction->invoice_code }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:'Inter',Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:580px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background-color:#4f46e5;padding:28px 32px;color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.85;font-weight:600;">Nota Digital Resmi</div>
                            <h1 style="margin:6px 0 4px;font-size:22px;font-weight:700;color:#ffffff;">{{ $store->name }}</h1>
                            @if($store->address)
                                <div style="font-size:13px;color:#e0e7ff;">{{ $store->address }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="margin:0 0 16px;font-size:15px;color:#334155;">
                                Halo <strong>{{ $customer->name }}</strong>, terima kasih telah berkunjung dan bertransaksi bersama kami!
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;border-radius:12px;padding:16px;margin-bottom:24px;">
                                <tr>
                                    <td style="font-size:13px;color:#64748b;padding:4px 0;">No. Nota</td>
                                    <td align="right" style="font-size:13px;font-weight:700;color:#0f172a;padding:4px 0;">{{ $transaction->invoice_code }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size:13px;color:#64748b;padding:4px 0;">Tanggal</td>
                                    <td align="right" style="font-size:13px;color:#0f172a;padding:4px 0;">{{ $transaction->created_at?->format('d M Y, H:i') }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size:13px;color:#64748b;padding:4px 0;">Dilayani oleh</td>
                                    <td align="right" style="font-size:13px;color:#0f172a;padding:4px 0;">{{ $cashier?->name ?? 'Kasir' }}</td>
                                </tr>
                            </table>

                            <div style="font-size:13px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:10px;">Rincian Layanan</div>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-bottom:20px;">
                                <thead>
                                    <tr style="border-bottom:1px solid #e2e8f0;">
                                        <th align="left" style="padding:10px 0;font-size:12px;color:#64748b;font-weight:600;">Layanan</th>
                                        <th align="center" style="padding:10px 0;font-size:12px;color:#64748b;font-weight:600;">Qty</th>
                                        <th align="right" style="padding:10px 0;font-size:12px;color:#64748b;font-weight:600;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transaction->service_detail ?? [] as $item)
                                        <tr style="border-bottom:1px solid #f1f5f9;">
                                            <td style="padding:12px 0;font-size:14px;color:#0f172a;font-weight:500;">
                                                {{ $item['name'] ?? '-' }}
                                                <div style="font-size:12px;color:#64748b;">Rp {{ number_format((float)($item['price'] ?? 0), 0, ',', '.') }}</div>
                                            </td>
                                            <td align="center" style="padding:12px 0;font-size:14px;color:#475569;">{{ $item['qty'] ?? 1 }}</td>
                                            <td align="right" style="padding:12px 0;font-size:14px;color:#0f172a;font-weight:600;">Rp {{ number_format((float)($item['subtotal'] ?? 0), 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:24px;">
                                <tr>
                                    <td style="padding:5px 0;font-size:14px;color:#475569;">Subtotal</td>
                                    <td align="right" style="padding:5px 0;font-size:14px;color:#0f172a;">Rp {{ number_format((float)$transaction->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @if($transaction->points_used > 0)
                                    <tr>
                                        <td style="padding:5px 0;font-size:14px;color:#059669;">Diskon Tukar Poin ({{ number_format($transaction->points_used) }} poin)</td>
                                        <td align="right" style="padding:5px 0;font-size:14px;color:#059669;font-weight:600;">- Rp {{ number_format((float)$transaction->discount_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:12px 0 0;font-size:16px;font-weight:700;color:#0f172a;border-top:2px solid #e2e8f0;">Total Bayar</td>
                                    <td align="right" style="padding:12px 0 0;font-size:18px;font-weight:700;color:#4f46e5;border-top:2px solid #e2e8f0;">Rp {{ number_format((float)$transaction->total_paid, 0, ',', '.') }}</td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;padding:16px;">
                                <tr>
                                    <td>
                                        <div style="font-size:12px;color:#4338ca;font-weight:600;text-transform:uppercase;">Poin Loyalitas Anda</div>
                                        <div style="margin-top:4px;font-size:14px;color:#1e1b4b;">
                                            Poin didapat dari transaksi ini: <strong>+{{ number_format($transaction->points_earned) }} poin</strong>
                                        </div>
                                        <div style="margin-top:4px;font-size:16px;font-weight:700;color:#4f46e5;">
                                            Total Saldo Poin: {{ number_format($customer->total_points) }} poin ({{ $customer->loyalty_tier }})
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f8fafc;padding:20px 32px;text-align:center;font-size:12px;color:#64748b;border-top:1px solid #e2e8f0;">
                            Dikirim otomatis oleh <strong>{{ $store->name }}</strong> melalui platform CRM <strong>UMKM Connect</strong>.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
