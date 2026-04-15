<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!--[if mso]>
    <style>
        * { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background-color: #f1f5f9; color: #0f172a; -webkit-font-smoothing: antialiased;">
<div style="padding: 40px 20px;">
    {{-- Container --}}
    <div style="max-width: 640px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 8px 24px rgba(0, 0, 0, 0.06);">

        {{-- Header --}}
        <div style="padding: 32px 40px 24px; text-align: center; border-bottom: 1px solid #e2e8f0;">
            @hasSection('logo')
                @yield('logo')
            @else
                <div style="display: inline-block; width: 48px; height: 48px; background-color: #4f46e5; border-radius: 12px; line-height: 48px; text-align: center;">
                    <span style="color: #ffffff; font-size: 20px; font-weight: 800;">W</span>
                </div>
            @endif
        </div>

        {{-- Content --}}
        <div style="padding: 40px;">
            {{-- Title --}}
            <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 8px; letter-spacing: -0.025em;">
                @yield('title', $subject)
            </h1>

            @hasSection('subtitle')
                <p style="font-size: 14px; color: #64748b; margin: 0 0 28px; font-weight: 500;">
                    @yield('subtitle')
                </p>
            @else
                <div style="height: 28px;"></div>
            @endif

            {{-- Greeting --}}
            <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0 0 16px; font-weight: 500;">
                @yield('greeting', 'Dobr&yacute; den,')
            </p>

            {{-- Body --}}
            @yield('body')

            {{-- Action button --}}
            @hasSection('action')
                <div style="margin: 32px 0; text-align: center;">
                    @yield('action')
                </div>
            @endif

            {{-- Additional content --}}
            @hasSection('footer_note')
                <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <p style="font-size: 13px; color: #94a3b8; margin: 0; line-height: 1.6;">
                        @yield('footer_note')
                    </p>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div style="background-color: #f8fafc; text-align: center; padding: 20px 40px; border-top: 1px solid #f1f5f9;">
            <p style="font-size: 12px; color: #94a3b8; margin: 0; font-weight: 500;">
                &copy; {{ date('Y') }} Web-pulse &middot; V&scaron;echna pr&aacute;va vyhrazena.
            </p>
        </div>
    </div>

    {{-- Unsubscribe / info under the card --}}
    @hasSection('bottom_note')
        <div style="max-width: 640px; margin: 16px auto 0; text-align: center;">
            <p style="font-size: 11px; color: #94a3b8; margin: 0;">
                @yield('bottom_note')
            </p>
        </div>
    @endif
</div>
</body>
</html>
