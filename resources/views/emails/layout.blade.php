<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1"
          name="viewport">
    <title>{{ trim($__env->yieldContent('title', config('app.name'))) }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-container {
                width: 100% !important;
            }

            .email-padding {
                padding: 24px 18px !important;
            }
        }
    </style>
</head>

@php($preheader = trim(\Illuminate\Support\Str::squish($__env->yieldContent('preheader', ''))))
@php($heading = trim($__env->yieldContent('heading', '')))
@php($intro = trim($__env->yieldContent('intro', '')))
@php($footer = trim($__env->yieldContent('footer', '')))
@php($after = trim($__env->yieldContent('after', '')))

<body style="margin:0; padding:0; background-color:#f8f5ec; font-family:'Segoe UI', Arial, sans-serif; color:#1f1a13;">
    @if ($preheader !== '')
        <div
             style="display:none; font-size:1px; color:#f8f5ec; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
            {{ $preheader }}
        </div>
    @endif

    <table cellpadding="0"
           cellspacing="0"
           role="presentation"
           style="background-color:#f8f5ec;"
           width="100%">
        <tr>
            <td align="center"
                style="padding:32px 16px;">
                <table cellpadding="0"
                       cellspacing="0"
                       class="email-container"
                       role="presentation"
                       style="max-width:640px; background-color:#ffffff; border-radius:24px; overflow:hidden; border:1px solid #f0e2b6; box-shadow:0 18px 48px rgba(87, 70, 32, 0.1);"
                       width="100%">
                    <tr>
                        <td class="email-padding"
                            style="padding:32px 32px 16px 32px;">
                            <table cellpadding="0"
                                   cellspacing="0"
                                   role="presentation"
                                   width="100%">
                                <tr>
                                    <td style="padding-bottom:20px;">
                                        <table cellpadding="0"
                                               cellspacing="0"
                                               role="presentation">
                                            <tr>
                                                <td style="padding-bottom:10px;">
                                                    <span
                                                          style="display:inline-block; padding:8px 16px; border-radius:999px; background-color:#d4af37; color:#1f1a13; font-size:12px; letter-spacing:0.12em; font-weight:600; text-transform:uppercase;">
                                                        {{ config('app.name') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @if ($heading !== '')
                                    <tr>
                                        <td style="padding-bottom:12px;">
                                            <h1 style="margin:0; font-size:26px; line-height:1.3; color:#221b0e;">
                                                {{ $heading }}
                                            </h1>
                                        </td>
                                    </tr>
                                @endif
                                @if ($intro !== '')
                                    <tr>
                                        <td style="padding-bottom:8px;">
                                            <div style="font-size:16px; line-height:1.6; color:#4f452c;">
                                                {!! $intro !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-padding"
                            style="padding:0 32px 32px 32px;">
                            <div style="font-size:15px; line-height:1.7; color:#3a3220;">
                                @yield('content')
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #f0e2b6; background-color:#fdfaf3;">
                            <table cellpadding="0"
                                   cellspacing="0"
                                   role="presentation"
                                   width="100%">
                                <tr>
                                    <td class="email-padding"
                                        style="padding:24px 32px; font-size:13px; line-height:1.6; color:#5f532f;">
                                        @if ($footer !== '')
                                            {!! $footer !!}
                                        @else
                                            Terima kasih,<br>
                                            {{ config('app.name') }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                @if ($after !== '')
                    <div style="width:100%; max-width:640px;">
                        {!! $after !!}
                    </div>
                @endif
            </td>
        </tr>
    </table>
</body>

</html>
