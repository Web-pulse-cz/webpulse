<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', Arial, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .container {
            max-width: 768px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .header {
            padding: 20px 30px;
            text-align: center;
        }
        .logo {
            max-width: 140px;
            height: auto;
        }
        .content {
            padding: 30px;
            font-size: 16px;
            line-height: 1.6;
        }
        .content h1 {
            font-size: 24px;
            color: #06b6d4;
            font-weight: 700;
            margin-top: 0;
        }
        .content p {
            font-weight: 500;
            margin: 12px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background-color: #f1f5f9;
            font-weight: 600;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #06b6d4;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        .footer {
            background-color: #f8fafc;
            text-align: center;
            padding: 15px 20px;
            font-size: 12px;
            color: #64748b;
        }
        @media screen and (max-width: 600px) {
            .container {
                max-width: 600px;
            }
            .content {
                padding: 20px;
                font-size: 14px;
            }
            .content h1 {
                font-size: 20px;
            }
            .button {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div style="padding: 30px;">
    <div class="container">
        <div class="header">
            <img src="https://placehold.co/140x140?text=Webpulse" alt="Web-pulse Logo" class="logo">
        </div>
        <div class="content">
            <h1>{{ $subject }}</h1>
            <p>Dobrý den, {{ $newsletter->ąddressing }}</p>
            <p>Právě byl přidán nový kvíz s názvem <strong>{{ $quiz->name }}</strong>. Níže naleznete jeho základní informace</p>

            <table class="table">
                <tbody>
                <tr>
                    <td>Název kvízu</td>
                    <td>{{ $quiz->name }}</td>
                </tr>
                <tr>
                    <td>Popisek</td>
                    <td>{!! $quiz->description !!}</td>
                </tr>
                <tr>
                    <td>Počet otázek</td>
                    <td>{{ $quiz->questions->count() }}</td>
                </tr>
                </tbody>
            </table>

            <p style="margin-top: 20px;">
                <a href="{{ $quiz->url }}" class="button">{{ 'Zahrát si kvíz' }}</a>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Web-pulse. Všechna práva vyhrazena.
        </div>
    </div>
</div>
</body>
</html>
