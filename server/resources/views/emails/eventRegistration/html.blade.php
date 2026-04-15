@extends('emails._layout.html')

@section('title', $subject)
@section('subtitle', '{{ $eventRegistration->event->name }}')

@section('body')
    <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0 0 16px; font-weight: 400;">
        D&ecaron;kujeme za p&rcaron;ihl&aacute;&scaron;en&iacute; na akci
        <strong style="color: #0f172a;">{{ $eventRegistration->event->name }}</strong>.
        N&iacute;&zcaron;e zas&iacute;l&aacute;me detaily.
    </p>

    {{-- Event info card --}}
    <div style="margin: 24px 0; padding: 20px; background-color: #eef2ff; border-radius: 12px;">
        <p style="font-size: 13px; font-weight: 700; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px;">Informace o akci</p>
        <p style="font-size: 14px; color: #334155; margin: 0 0 6px;">
            <strong>{{ $eventRegistration->event->name }}</strong>
        </p>
        <p style="font-size: 13px; color: #64748b; margin: 0 0 4px;">
            {{ \Illuminate\Support\Carbon::parse($eventRegistration->event->start_date)->format('d. m. Y H:i') }}
            &ndash;
            {{ \Illuminate\Support\Carbon::parse($eventRegistration->event->end_date)->format('d. m. Y H:i') }}
        </p>
        @if($eventRegistration->event->place)
            <p style="font-size: 13px; color: #64748b; margin: 0;">
                {{ $eventRegistration->event->place }}
            </p>
        @endif
    </div>

    {{-- Attendee details --}}
    <div style="margin: 24px 0; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; width: 40%; border-bottom: 1px solid #f1f5f9;">Jm&eacute;no</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $eventRegistration->firstname }} {{ $eventRegistration->lastname }}</td>
            </tr>
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">E-mail</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $eventRegistration->email }}</td>
            </tr>
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">Telefon</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $eventRegistration->phone }}</td>
            </tr>
            @if($eventRegistration->note)
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">Pozn&aacute;mka</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $eventRegistration->note }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc;">Cena</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #0f172a; font-weight: 700;">{{ $eventRegistration->event->price }},- K&ccaron;</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
