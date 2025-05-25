<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Poptávka</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f6f6f6;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
        }
        h1 {
            color: #1e90ff;
        }
        p {
            line-height: 1.5;
        }
        .footer {
            font-size: 12px;
            color: #888;
            text-align: center;
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #1e90ff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Ahoj!</h1>
    <p>Děkujeme, že jste se přihlásili k odběru našich e-mailů.</p>
    <p>Toto je ukázková šablona pro odesílání e-mailů pomocí Laravelu.</p>
    <p>{{ $message }}</p>
    <p>{{ $name }}</p>
    <a href="#" class="button">Více informací</a>
    <div class="footer">
        &copy; 2025 Tvá Firma. Všechna práva vyhrazena.
    </div>
</div>
</body>
</html>
