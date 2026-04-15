@extends('emails._layout.html')

@section('title', $subject)
@section('subtitle', 'Uk&aacute;zkov&aacute; &scaron;ablona')

@section('body')
    <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0 0 16px; font-weight: 400;">
        {!! $content ?? 'Toto je uk&aacute;zkov&aacute; e-mailov&aacute; &scaron;ablona syst&eacute;mu Web-pulse.' !!}
    </p>

    {{-- Table --}}
    <div style="margin: 24px 0; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
            <tr>
                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">Produkt</th>
                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">Mno&zcaron;stv&iacute;</th>
                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">Cena</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">Webdesign Basic</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">1</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; font-weight: 600; border-bottom: 1px solid #f1f5f9;">5&nbsp;000 K&ccaron;</td>
            </tr>
            <tr>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155;">Hosting Premium</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155;">12 m&ecaron;s&iacute;c&udblac;</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; font-weight: 600;">2&nbsp;400 K&ccaron;</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection

@section('action')
    <a href="{{ $button_url ?? '#' }}" style="display: inline-block; padding: 14px 32px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 12px; font-size: 14px; font-weight: 700; letter-spacing: 0.01em;">
        {{ $button_text ?? 'Klikn&ecaron;te zde' }}
    </a>
@endsection
