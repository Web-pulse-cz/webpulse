# Webpulse

## Instalace
### /server
1. `cd server`
2. `composer install` (nainstaluje vendor složku)
3. `cp .env.example .env` (zkopíruje .env soubor - přidat vlastní parametry)
4. `php artisan key:generate` (vygeneruje klíč pro šifrování)
5. `php artisan migrate --seed` (vytvoří databázi a naplní ji testovacími daty)
6. `docker-compose build` (připraví Docker kontejnery)
7. `docker-compose up -d` (spustí Docker kontejnery na pozadí)
8. Otevřete prohlížeč a přejděte na `http://localhost:8010`

### /client
- je potřeba mít nainstalovaný [Node.js](https://nodejs.org/en/download/) verze 22+

1. `cd client`
2. `npm install` (nainstaluje node_modules)
3. `cp .env.example .env` (zkopíruje .env soubor - přidat vlastní parametry)
4. `npm run lint:fix` (opraví chyby v kódu)
5. `npm run dev` (spustí vývojový server)
6. Otevřete prohlížeč a přejděte na `http://localhost:3001` (port definovaný v configu .env `PORT=3001`)

### /admin
- je potřeba mít nainstalovaný [Node.js](https://nodejs.org/en/download/) verze 22+

1. `cd client`
2. `npm install` (nainstaluje node_modules)
3. `cp .env.example .env` (zkopíruje .env soubor - přidat vlastní parametry)
4. `npm run lint:fix` (opraví chyby v kódu)
5. `npm run dev` (spustí vývojový server)
6. Otevřete prohlížeč a přejděte na `http://localhost:3000` (port definovaný v configu .env `PORT=3000`)