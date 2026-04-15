<?php
// public/deploy.php

$secretToken = 'moje-super-tajne-heslo-pro-web-pulse-2026';

if (! isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    header('HTTP/1.0 403 Forbidden');
    exit('Přístup odepřen.');
}

$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Cesty (uprav si podle reality)
    $gitPath = '/var/www/html/api.web-pulse.cz';          // Kde je složka .git
    $laravelPath = '/var/www/html/api.web-pulse.cz/server'; // Kde je Laravel (artisan, composer.json)

    $output .= '<pre>';

    // --- 1. ČÁST: GIT ---
    chdir($gitPath); // Přepneme PHP do složky s gitem
    $output .= "<span style=\"color: #eab308;\">--- Přesun do: {$gitPath} ---</span>\n";

    $gitCommands = [
        'git fetch origin 2>&1',
        'git reset --hard origin/deploy_production 2>&1',
    ];

    foreach ($gitCommands as $command) {
        $output .= "<span style=\"color: #66d9ef;\">$ {$command}</span>\n";
        $output .= htmlentities(trim(shell_exec($command)))."\n\n";
    }

    // --- 2. ČÁST: LARAVEL A COMPOSER ---
    chdir($laravelPath); // Přepneme PHP do složky s Laravelem
    $output .= "<span style=\"color: #eab308;\">--- Přesun do: {$laravelPath} ---</span>\n";

    $laravelCommands = [
        // Přidán COMPOSER_HOME=/tmp
        'COMPOSER_HOME=/tmp composer install --no-interaction --no-ansi --prefer-dist --optimize-autoloader 2>&1',

        // Oprava oprávnění – storage a bootstrap/cache musí být zapisovatelné webserverem
        'chmod -R 777 '.escapeshellarg($laravelPath.'/storage').' 2>&1',
        'chmod -R 777 '.escapeshellarg($laravelPath.'/bootstrap/cache').' 2>&1',

        'php artisan migrate --force 2>&1',
        'php artisan optimize:clear 2>&1',
        'php artisan config:cache 2>&1',
        'php artisan event:cache 2>&1',
        'php artisan route:cache 2>&1',
        'php artisan view:cache 2>&1',
    ];

    foreach ($laravelCommands as $command) {
        $output .= "<span style=\"color: #66d9ef;\">$ {$command}</span>\n";
        $output .= htmlentities(trim(shell_exec($command)))."\n\n";
    }

    $output .= '</pre>';
    $output .= "<div class='success'>✅ Nasazení bylo dokončeno! Zkontroluj výstup výše, jestli tam nejsou errory.</div>";
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Autodeployer</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #121212; color: #e0e0e0; padding: 2rem; max-width: 800px; margin: auto; }
        .btn { background: #ef4444; color: white; border: none; padding: 15px 30px; font-size: 1.1rem; cursor: pointer; border-radius: 6px; font-weight: bold; }
        pre { background: #1e1e1e; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #333; line-height: 1.5; font-family: monospace; }
        .success { color: #10b981; font-weight: bold; font-size: 1.2rem; margin-top: 1rem; border: 1px solid #10b981; padding: 10px; border-radius: 6px; }
    </style>
</head>
<body>
<h1>🚀 Autodeployer pro api.web-pulse.cz</h1>
<form method="POST"><button class="btn" type="submit">Nasadiť novou verzi</button></form>
<?php if ($output) { ?>
    <h2>Výstup z terminálu:</h2>
    <?php echo $output; ?>
<?php } ?>
</body>
</html>
