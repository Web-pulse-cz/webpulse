@extends('emails._layout.html')

@section('title', $subject)
@section('subtitle', 'Nov&aacute; popt&aacute;vka z webu')

@section('body')
    @if ($demand->service)
        <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0 0 16px; font-weight: 400;">
            Na webu <strong style="color: #0f172a;">{{ env('CLIENT_URL') }}</strong> byla pr&aacute;v&ecaron; vytvo&rcaron;ena nov&aacute; popt&aacute;vka na slu&zcaron;bu
            <strong style="color: #0f172a;">{{ $demand->service->name }}</strong>.
        </p>
    @else
        <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0 0 16px; font-weight: 400;">
            Na webu <strong style="color: #0f172a;">{{ env('CLIENT_URL') }}</strong> byla pr&aacute;v&ecaron; vytvo&rcaron;ena nov&aacute; popt&aacute;vka.
        </p>
    @endif

    <div style="margin: 24px 0; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; width: 40%; border-bottom: 1px solid #f1f5f9;">Jm&eacute;no a p&rcaron;&iacute;jmen&iacute;</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $demand->fullname }}</td>
            </tr>
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">E-mail</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $demand->email }}</td>
            </tr>
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">Telefon</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $demand->fullPhone }}</td>
            </tr>
            @if($demand->service)
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">Slu&zcaron;ba</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $demand->service->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">Navrhovan&aacute; cena</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $demand->offer_price }}</td>
                </tr>
            @endif
            @if($demand->url)
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">URL</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $demand->url }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">Zpr&aacute;va</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $demand->text }}</td>
            </tr>
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc;">Jazyk</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155;">{{ $demand->locale }}</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
