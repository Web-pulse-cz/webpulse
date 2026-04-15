@extends('emails._layout.html')

@section('title', $subject)

@section('greeting')
    Dobr&yacute; den, {{ $newsletter->addressing }}
@endsection

@section('body')
    <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0 0 16px; font-weight: 400;">
        Pr&aacute;v&ecaron; byl p&rcaron;id&aacute;n nov&yacute; kv&iacute;z s n&aacute;zvem
        <strong style="color: #0f172a;">{{ $quiz->name }}</strong>.
    </p>

    {{-- Quiz card --}}
    <div style="margin: 24px 0; padding: 24px; background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
        <p style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 8px;">
            {{ $quiz->name }}
        </p>
        @if($quiz->description)
            <p style="font-size: 14px; color: #64748b; line-height: 1.6; margin: 0 0 12px;">
                {!! $quiz->description !!}
            </p>
        @endif
        <p style="font-size: 13px; color: #94a3b8; margin: 0;">
            Po&ccaron;et ot&aacute;zek: <strong style="color: #64748b;">{{ $quiz->questions->count() }}</strong>
        </p>
    </div>
@endsection

@section('action')
    <a href="{{ $quiz->url }}" style="display: inline-block; padding: 14px 32px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 12px; font-size: 14px; font-weight: 700; letter-spacing: 0.01em;">
        Zahr&aacute;t si kv&iacute;z
    </a>
@endsection
