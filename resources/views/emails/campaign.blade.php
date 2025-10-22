<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? 'Campaign' }}</title>
    <meta content="width=device-width, initial-scale=1"
          name="viewport">
    <style>
        /* Basic email-friendly styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #ffffff;
            color: #222;
        }

        .container {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            padding: 16px;
            box-sizing: border-box;
        }

        a {
            color: #0d6efd;
            text-decoration: underline;
        }

        img {
            max-width: 100%;
            height: auto;
            border: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        {!! $html !!}

        <hr style="margin:24px 0;border:none;border-top:1px solid #ddd;">

        <p style="font-size:12px;color:#666;margin:0 0 12px;">
            Jika Anda tidak ingin menerima email seperti ini lagi,
            <a href="{{ route('unsubscribe', $subscriber->token) }}">berhenti berlangganan</a>.
        </p>

        @if (!empty($receiptId))
            <p style="font-size:12px;color:#666;margin:0 0 12px;">
                Lihat versi web:
                <a href="{{ route('newsletter.webview', ['receipt' => $receiptId, 'token' => $subscriber->token]) }}">buka
                    di browser</a>
            </p>

            <img alt=""
                 height="1"
                 src="{{ route('newsletter.track.open', ['receipt' => $receiptId, 'token' => $subscriber->token]) }}"
                 style="display:none;"
                 width="1">
        @endif
    </div>
</body>

</html>
