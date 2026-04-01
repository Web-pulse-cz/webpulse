<?php
// public/deploy.php

// 1. BEZPEČNOST: Změň tento token na nějaký dlouhý a bezpečný řetězec!
$secretToken = 'moje-super-tajne-heslo-pro-web-pulse-2026';

// 2. Ověření přístupu (ochrana proti náhodným botům a útočníkům)
if (!isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    header('HTTP/1.0 403 Forbidden');
    die('Přístup odepřen.');
}

$output = '';

// 3. Zpracování kliknutí na tlačítko (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Cesta k tvému projektu na VPS (uprav, pokud se liší)
    $projectPath = '/var/www/html/api.web-pulse.cz';

    // Příkazy k provedení
    $commands = [
        "cd {$projectPath}",
        "git fetch origin 2>&1",
        // Používáme reset --hard místo pull, aby nevznikaly merge konflikty, pokud by se na serveru náhodou změnil nějaký soubor
        "git reset --hard origin/main 2>&1", // Změň 'main' na 'master', pokud používáš starší pojmenování větve
        "composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1",
        "php artisan migrate --force 2>&1",
        "php artisan optimize:clear 2>&1",
        "php artisan config:cache 2>&1",
        "php artisan event:cache 2>&1",
        "php artisan route:cache 2>&1",
        "php artisan view:cache 2>&1",
    ];

    $output .= "<pre>";
    foreach ($commands as $command) {
        $output .= "<span style=\"color: #66d9ef;\">$ {$command}</span>\n";
        // Vykonání příkazu
        $result = shell_exec($command);
        $output .= htmlentities(trim($result)) . "\n\n";
    }
    $output .= "</pre>";
    $output .= "<div class='success'>✅ Nasazení bylo úspěšně dokončeno!</div>";
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autodeployer | api.web-pulse.cz</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #121212; color: #e0e0e0; padding: 2rem; max-width: 800px; margin: auto; }
        h1 { color: #fff; }
        .btn { background: #ef4444; color: white; border: none; padding: 15px 30px; font-size: 1.1rem; cursor: pointer; border-radius: 6px; font-weight: bold; transition: background 0.2s; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.3); }
        .btn:hover { background: #dc2626; }
        pre { background: #1e1e1e; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #333; line-height: 1.5; font-family: monospace; }
        .success { color: #10b981; font-weight: bold; font-size: 1.2rem; margin-top: 1rem; border: 1px solid #10b981; padding: 10px; border-radius: 6px; background: rgba(16, 185, 129, 0.1); }
    </style>
</head>
<body>
<h1>🚀 Autodeployer pro api.web-pulse.cz</h1>
<p>Kliknutím na tlačítko stáhneš nejnovější kód z gitu, nainstaluješ balíčky, spustíš migrace databáze a promažeš/obnovíš Laravel cache.</p>

<form method="POST">
    <button class="btn" type="submit">Nasadiť novou verzi</button>
</form>

<?php if ($output): ?>
    <h2>Výstup z terminálu:</h2>
    <?php echo $output; ?>
<?php endif; ?>
</body>
</html>
