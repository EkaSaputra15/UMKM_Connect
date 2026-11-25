<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $broadcast->subject }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:'Inter',Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:580px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:28px 32px;color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.9;font-weight:600;">Info &amp; Penawaran Spesial</div>
                            <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;">{{ $broadcast->subject }}</h1>
                            <div style="margin-top:4px;font-size:13px;color:#e0e7ff;">Dari {{ $store->name }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="margin:0 0 16px;font-size:15px;color:#0f172a;font-weight:600;">
                                Halo {{ $customer->name }},
                            </p>

                            <div style="font-size:14px;line-height:1.7;color:#334155;margin-bottom:24px;">
                                {!! nl2br(e($broadcast->message)) !!}
                            </div>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;padding:16px;">
                                <tr>
                                    <td>
                                        <div style="font-size:12px;color:#4338ca;font-weight:600;text-transform:uppercase;">Saldo Poin Member Anda</div>
                                        <div style="margin-top:4px;font-size:18px;font-weight:700;color:#4f46e5;">
                                            {{ number_format($customer->total_points) }} Poin &bull; Tier {{ $customer->loyalty_tier }}
                                        </div>
                                        <div style="margin-top:4px;font-size:12px;color:#475569;">
                                            Tukarkan poin Anda pada kunjungan berikutnya di {{ $store->name }} untuk mendapatkan potongan harga langsung!
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f8fafc;padding:20px 32px;text-align:center;font-size:12px;color:#64748b;border-top:1px solid #e2e8f0;">
                            {{ $store->name }}@if($store->address) &bull; {{ $store->address }}@endif
                            <div style="margin-top:4px;">Dikelola dengan UMKM Connect</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
