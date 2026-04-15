@extends('emails._layout.html')

@section('title', $subject)
@section('subtitle', 'Pracovn&iacute; pozice: {{ $careerApplication->career->name }}')

@section('body')
    @if($type == 'client')
        <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0 0 16px; font-weight: 400;">
            D&ecaron;kujeme v&aacute;m za pod&aacute;n&iacute; &zcaron;&aacute;dosti o pracovn&iacute; pozici
            <strong style="color: #0f172a;">{{ $careerApplication->career->name }}</strong>.
        </p>
        <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0; font-weight: 400;">
            O jej&iacute;m pr&udblac;b&ecaron;hu v&aacute;s budeme informovat na t&eacute;to e-mailov&eacute; adrese.
        </p>
    @elseif($type == 'admin')
        <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0 0 16px; font-weight: 400;">
            V syst&eacute;mu vznikla nov&aacute; &zcaron;&aacute;dost o pracovn&iacute; pozici
            <strong style="color: #0f172a;">{{ $careerApplication->career->name }}</strong>.
        </p>

        <div style="margin: 24px 0; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; width: 40%; border-bottom: 1px solid #f1f5f9;">Jm&eacute;no</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $careerApplication->firstname }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">P&rcaron;&iacute;jmen&iacute;</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $careerApplication->lastname }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">E-mail</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $careerApplication->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">Telefon</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $careerApplication->phone }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">O&ccaron;ek&aacute;van&aacute; mzda</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $careerApplication->salary_expectation }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background-color: #f8fafc;">Datum n&aacute;stupu</td>
                    <td style="padding: 12px 16px; font-size: 14px; color: #334155;">{{ $careerApplication->realAvailability }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    @endif
@endsection
