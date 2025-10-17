<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1"
          name="viewport">
    <title>Verifikasi Login</title>
</head>

<body style="margin:0; padding:0; background:#f6f6f6; font-family: Arial, Helvetica, sans-serif; color:#111;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; padding:20px;">
        <h1 style="font-size:20px; margin:0 0 12px;">Verifikasi Login</h1>
        <p style="margin:0 0 12px;">Berikut adalah kode OTP Anda. Masukkan kode ini di aplikasi untuk melanjutkan proses
            login.</p>

        <div style="text-align:center; margin:16px 0;">
            <div
                 style="display:inline-block; padding:12px 18px; border:1px solid #ddd; border-radius:8px; font-size:28px; font-weight:bold; letter-spacing:4px; font-family: monospace;">
                {{ $code }}
            </div>
            <div style="margin-top:8px; font-size:14px; color:#555;">
                Kode berlaku hingga
                {{ $expiresAt->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d-m-Y H:i') }}.
            </div>
        </div>

        <p style="margin:0 0 16px;">Jika Anda tidak meminta login, abaikan email ini.</p>

        <p style="margin-top:24px;">Thanks,<br>{!! config('app.name') !!}</p>
    </div>
</body>

</html>
