# Bezpečnostní audit — WebPulse (2026-09-22)

Kompletní audit backendu (Laravel 12 API, `server/`) a frontend aplikací (Nuxt 4 — `admin/`, `client/`, `client-web-pulse.cz/`, `client-luxuryroses.cz/`, `game/`). Provedeno 6 paralelními audity zaměřenými na: autentizaci/autorizaci, injection/validaci, upload/filemanager, secrets/CORS, frontend XSS/CSRF, multi-tenant IDOR a rate limiting. Všechny nalezené a opravené chyby prošly syntax lintem (`php -l`) a ověřením `artisan route:list` (505 rout bez chyby).

Legenda stavu: **OPRAVENO** / **NEOPRAVENO — vyžaduje rozhodnutí** / **NEOPRAVENO — info/doporučení**.

---

## Critical

### 1. Neautentizovaný self-registration endpoint vytvářející admin účty
**OPRAVENO** — `server/routes/api.php`, `Admin/Auth/RegisterController.php`
`POST /api/admin/register` byl veřejný, kdokoli si mohl bez přihlášení založit plnohodnotný admin účet. Přidán `auth:sanctum` middleware + kontrola, že jen superadmin (`user_group_id === 1`) smí zakládat účty.

### 2. Eskalace oprávnění na superadmina přes `user_group_id`
**OPRAVENO** — `Admin/User/UserController.php::store()`
Libovolný přihlášený uživatel mohl sobě i komukoli nastavit `user_group_id = 1` (superadmin) přes edit endpoint. Přidána kontrola, že jen superadmin smí přiřadit skupinu `1`.

### 3. Command injection (RCE) přes PHP backtick operátor
**OPRAVENO** — `app/Http/Resources/Admin/Career/CareerApplicationResource.php:39`
Kód používal PHP backtick operátor (`` `${...}` ``, spouští shell) místo string interpolace pro spojení jména a příjmení uchazeče. Nahrazeno bezpečnou `"{$var}"` interpolací.

### 4. Neautentizovaný file-upload endpoint chráněný jen hardcoded placeholder klíčem
**OPRAVENO** — `FilemanagerController.php`, `FileManagerService.php`, `routes/api.php`
`/api/filemanager/upload/images` a `/formats` byly "chráněné" jen kontrolou `securityKey === 'your_security_key_here'` — doslovný placeholder viditelný i v admin JS bundlu. Fakticky veřejný upload včetně SSRF přes parametr `url`. Opraveno: `auth:sanctum` middleware na routě, odstraněna placeholder kontrola, přidán SSRF guard (blokace private/loopback/link-local IP), path traversal fix (`basename()` na keepName), SVG sanitizace proti stored XSS.

### 5. Neomezený upload libovolného typu souboru (potenciální RCE)
**OPRAVENO** — `app/Traits/HasFiles.php` (generický file-attachment upload pro Post/Page/Project/Employee)
Validace kontrolovala jen `required|file|max:20480`, žádný MIME whitelist — přihlášený uživatel mohl nahrát `.php`/`.phtml` soubor pod webroot. Přidán content-based MIME whitelist (`mimes:pdf,doc,docx,xls,xlsx,csv,txt,rtf,odt,ppt,pptx,png,jpg,jpeg,gif,webp`), který navíc odhaduje typ z obsahu (ne z přípony) a explicitně blokuje PHP soubory.

### 6. CORS: `allowed_origins => ['*']` + `supports_credentials => true`
**OPRAVENO** — `server/config/cors.php`
Kombinace umožňovala libovolné cizí doméně dělat credentialed požadavky na API (browser echo-uje `Origin` zpět jako `Access-Control-Allow-Origin`). Přepsáno na explicitní whitelist z `CORS_ALLOWED_ORIGINS` env proměnné (výchozí fallback jen `localhost:3000/3001/3002` pro lokální vývoj).
**Akce potřebná před nasazením:** nastavit `CORS_ALLOWED_ORIGINS` v produkčním `.env` na skutečné produkční domény (admin, client-web-pulse.cz, luxuryroses.cz, game, atd.) — nebyly hardcoded, protože jsem si nebyl jistý přesnými produkčními hostnames a nechtěl jsem hádat a riskovat výpadek.

### 7. IDOR — libovolný uživatel mohl číst/upravit/smazat účty z jiného webu (i superadmina)
**OPRAVENO** — `Admin/User/UserController.php` (`show`/`store`/`destroy`)
`index()` filtroval podle `X-Site-Hash`, ale `show`/`store`/`destroy` používaly holé `User::find($id)`. Přidána `userSharesSiteWithActor()` kontrola + ochrana superadmin účtů proti non-superadmin uživatelům.

### 8. `UserGroupController` a `SiteController` bez omezení na superadmina
**OPRAVENO** — `Admin/User/UserGroupController.php`, `Admin/Site/SiteController.php`
Kdokoli přihlášený mohl měnit `permissions` libovolné skupiny (i skupiny superadminů), mazat skupiny, zakládat/mazat celé weby v multi-tenant systému. `store`/`destroy` nyní vyžadují `user_group_id === 1`.

### 9. `ApartmentBlockController` zcela bez site-scope (i čtení)
**OPRAVENO** — `Admin/Apartment/ApartmentBlockController.php`
`index`/`show`/`store`/`destroy` byly od začátku bez jakékoli kontroly webu — kdokoli přihlášený viděl/upravoval bloky apartmánů libovolného webu.

---

## High

### 10. SSRF přes `url` parametr u image-upload-from-URL
**OPRAVENO** — `FileManagerService.php`
`file_get_contents($url)` s libovolnou uživatelskou URL bez omezení schématu/hostitele (možnost dotázat interní služby, cloud metadata endpoint, `file://`). Přidán `assertUrlIsSafeToFetch()` — povoluje jen http/https, blokuje privátní/loopback/link-local IP rozsahy.

### 11. Mass assignment: `is_paid` nastavitelné přes veřejný endpoint
**OPRAVENO** — `Client/Event/EventRegistrationController.php`
Neautentizovaný uživatel mohl přes `fill($request->all())` označit vlastní event registraci jako zaplacenou. Nahrazeno whitelistem polí.

### 12. Mass assignment: `status`/`user_id` nastavitelné přes veřejný endpoint
**OPRAVENO** — `Client/Career/CareerApplicationController.php`
Neautentizovaná žádost o zaměstnání mohla nastavit libovolný `status` nebo `user_id` cizí přihlášky. Nahrazeno whitelistem polí.

### 13. Mass assignment citlivých polí (`invitation_token`, `email_verified_at`) přes user-update endpoint
**OPRAVENO** — `Admin/User/UserController.php`
`fill($request->all())` umožňoval přepsat `invitation_token` (riziko blízké account-takeover) nebo `email_verified_at`. Nahrazeno whitelistem přes `$request->only([...])`.

### 14. Systémový vzor IDOR napříč ~15 controllery: `store`/`destroy` bez site-scope
**OPRAVENO** (vzorek, mirror existujícího `index()`/`show()` patternu):
`Apartment/ApartmentController`, `ApartmentTypeController`, `Building/BuildingController`, `Blog/PostController`, `PostCategoryController`, `DiscGolf/CourseController`, `GameController`, `PlayerController`, `Task/TaskBoardController`, `TaskController`, `Reservation/ReservationController`, `Invoice/InvoiceController` (destroy), `Contract/ContractController` (store/destroy), `PriceOffer/PriceOfferController` (store/accept/reject/destroy).
Přidána nová pomocná metoda `Controller::userAllowedSiteIds()` — nezávislá na `X-Site-Hash` hlavičce (frontend ji nekonzistentně posílá na DELETE/POST), takže oprava nerozbije existující funkčnost, ale striktně vynucuje oprávnění podle `user_group`.

**NEOPRAVENO — stejný vzor nalezen i v cca 30 dalších controllerech** (report-only, mimo scope surgical fixu): `Faq/*`, `Event/*`, `Career/CareerController`, `Client/ClientController`, `Customer/*`, `Employee/*`, `PhotoGallery`, `Review`, `Logo`, `Voucher`, `Newsletter`, `Demand`, `Setting`, `Season`, `Amenity`, `Quiz`, `Shift/*`, `Food/*` (Recipe, Menu, Meal, Foodstuff...), `Restaurant/RestaurantTableController`, `Filemanager` presety, `Service/ServiceController`. Zvlášť prioritně doporučuji `Invoice`/`Contract`/`PriceOffer` (finanční data), i když store/destroy část už opravena — zkontrolovat i `show`. Doporučený postup: aplikovat stejný `userAllowedSiteIds()` vzor.

### 15. Stored XSS — `v-html` bez sanitizace na veřejných webech (WYSIWYG obsah)
**OPRAVENO** — 23 souborů napříč `client/`, `game/`, `admin/`
CMS/WYSIWYG (Quill) obsah editovaný v adminu (FAQ, blog, stránky, recenze, kvízy, služby) se renderoval přes `v-html` bez jakékoli sanitizace na veřejných zákaznických webech — libovolný editor s přístupem do adminu mohl vložit `<script>`/`onerror=` payload a zasáhnout návštěvníky webu. Přidán `dompurify` jako závislost do `client/`, `game/`, `admin/` (package.json), vytvořen composable `useSanitizedHtml.ts` v každé appce, všech 27 `v-html` výskytů obaleno `sanitizeHtml(...)`. Bezpečné výskyty (`DiscGolf` rules, HTML-escapované přes `useHighlight`) ponechány beze změny.

**Doporučení navíc:** zvážit sanitizaci i při ukládání na backendu (defense-in-depth), zejména u PDF šablon (`resources/views/pdf/contract.blade.php`, `emails/quizSaved/html.blade.php`), které raw HTML vypisují přes `{!! !!}` a přes dompdf mohou být vektorem pro SSRF (fetch vzdálených `<img>`/`<link>`) — neopraveno, vyžaduje zavedení HTML purifier balíčku napříč backendem.

---

## Medium

### 16. Uploadované SVG bez sanitizace (stored XSS)
**OPRAVENO** — `FileManagerService.php`
SVG soubory obcházely image processing a ukládaly se 1:1. SVG s `<script>`/`on*` handlery se při přímém otevření renderuje jako spustitelný HTML. Přidána regexová sanitizace (`sanitizeSvg()`) — doporučeno do budoucna nahradit knihovnou `enshrined/svg-sanitize` pro plnohodnotné XML-aware řešení.

### 17. Path traversal v admin-definované cestě filemanager presetu
**OPRAVENO** — `Admin/Filemanager/FilemanagerController.php`
Pole `path` bylo validováno jen jako `string|max:128` — admin mohl nastavit `../../` a nasměrovat zápis mimo `storage/app/public`. Přidán regex zakazující `..` a nepovolené znaky.

### 18. Chybějící validace (zakomentovaná) na veřejném newsletter endpointu
**OPRAVENO** — `Client/Newsletter/NewsletterController.php`
Validátor byl celý zakomentovaný, `fill($request->all())` probíhal bez kontroly formátu e-mailu/duplicity. Obnovena validace.

### 19. Chybějící rate limiting na loginu a veřejných formulářích (brute-force / spam)
**OPRAVENO** — `routes/api.php`
Login, forgot/reset password, register: `throttle:5,1`–`throttle:10,1`. Veřejné formuláře (kontakt, newsletter, event/career/quiz registrace, booking rezervací a služeb): `throttle:10,1`.

### 20. IDOR na filemanager presetech (`show`/`store`/`destroy`)
**NEOPRAVENO — vyžaduje rozhodnutí.** `Admin/Filemanager/FilemanagerController.php`
Na rozdíl od `index()` (filtruje podle `X-Site-Hash`) tyto tři metody neověřují vlastnictví webu. Admin frontend ale u těchto akcí `X-Site-Hash` vůbec neposílá — striktní vynucení by dnes rozbilo delete/save ve `nastaveni/filemanager`. Vyžaduje: buď doplnit `X-Site-Hash` do frontendových volání a přidat backend kontrolu, nebo potvrdit, že jde o záměrně sdílený/globální admin resource.

### 21. `HasFiles` trait — download/delete příloh bez site-scope
**NEOPRAVENO — vyžaduje rozhodnutí.** `app/Traits/HasFiles.php::handleDownloadFile()`, `handleDeleteFile()`
Desítky kontrolerů (Post, Page, Project, Employee, Invoice, Contract, PriceOffer...) používají tento trait pro stahování/mazání příloh bez ověření webu — přihlášený uživatel může stáhnout/smazat přílohu jiného webu při uhodnutí ID. Neopraveno záměrně: GET download linky pravděpodobně nechodí přes `X-Site-Hash`, plošná oprava riskuje rozbití legitimního stahování. Doporučení: ověřit vlastnictví webu proti množině webů přihlášeného uživatele (ne jen proti jedné hlavičce), nebo zavést signed/expiring download URL.

### 22. `CheckUserGroupSiteAccess` middleware — tichý bypass při chybějící `X-Site-Hash`
**NEOPRAVENO — info.** `app/Http/Middleware/CheckUserGroupSiteAccess.php`
Middleware kontrolu úplně přeskočí, pokud request neobsahuje `X-Site-Hash` — endpoint bez této hlavičky obchází omezení skupiny na weby. Nálezy #14/#20/#21 tento problém řeší per-controller. Dlouhodobé doporučení: sjednotit `store`/`destroy` endpointy na group-based přístup nezávislý na klientem posílané hlavičce.

### 23. Necachovaný `v-html` v adminu + token cookie bez `httpOnly`
**OPRAVENO** (v-html sanitizace, viz #15) — zbytek info: `nuxt-auth-sanctum` (`mode: 'token'`) ukládá auth token do cookie bez `httpOnly` (chování knihovny, ne bug v této codebase) — zvyšuje blast radius XSS nálezů, ale samo o sobě to není zranitelnost zavedená tímto kódem.

### 24. Hardcoded Supabase anon key v `game/nuxt.config.ts`
**OPRAVENO** — přesunuto z hardcoded hodnoty do `process.env.SUPABASE_URL`/`SUPABASE_ANON_KEY` (identické fallbacky, žádná změna chování). Nízké riziko samo o sobě (veřejný `anon` klíč chráněný Supabase RLS), ale hygienická oprava. Doporučení: ověřit, že RLS politiky v Supabase projektu skutečně omezují přístup.

---

## Low / Info

### 25. Secret v historii gitu (`admin/.env`, `AUTH_SECRET`)
**NEOPRAVENO — ponecháno dle rozhodnutí uživatele.** Commitnutý a dnes již smazaný `admin/.env` obsahoval `AUTH_SECRET` z opuštěného NextAuth flow (dnes nahrazeno `nuxt-auth-sanctum`, secret se v kódu nepoužívá). `.gitignore` je dnes v pořádku, žádný `.env` není trackovaný. Hodnota zůstává dohledatelná v git historii (`git show 963ffce0:admin/.env`). Rozhodnuto zaznamenat pouze do reportu — přepis historie (`git filter-repo`/BFG + force-push) je destruktivní a rozbil by existující klony repa; riziko zneužití je nízké, jelikož secret se dnes nikde nepoužívá.

### 26. `target="_blank"` bez `rel="noopener noreferrer"` (reverse tabnabbing)
**OPRAVENO** — `admin/app/components/Base/Table.vue`, `admin/app/pages/uzivatele/poptavky/[id].vue`, `admin/app/layouts/default.vue` (2×). Nejreálnější případ byl `item.url` zadané uchazečem v poptávkovém formuláři. Ostatní výskyty v `client/`/`client-luxuryroses.cz/` už `rel="noopener"` měly a jde o statické URL — beze změny.

### 27. Chybí Laravel security headers middleware
**NEOPRAVENO — info.** Žádné `X-Frame-Options`, `Content-Security-Policy`, `X-Content-Type-Options`, `Strict-Transport-Security`. Běžné u API-only backendů, ale doporučeno zvážit — CSP zejména je netriviální změna chování, vhodná k samostatnému review.

### 28. Fakturoid webhook bez ověření podpisu
**NEOPRAVENO — info.** `Admin/Fakturoid/FakturoidWebhookController.php` je veřejný bez ověření webhook secretu. Dopad dnes nízký — `FakturoidService::handleWebhook()` nedůvěřuje datům z requestu, vždy si entitu znovu stáhne autentizovaným API klientem. Navíc `new FakturoidService` je dnes volán bez povinného parametru `Site` — vypadá jako nedokončená/nefunkční feature. Doporučeno ověřit Fakturoid webhook signing secret před uvedením do provozu.

### 29. User enumeration přes reset hesla
**NEOPRAVENO — info.** `Admin/Auth/ForgotPasswordController::forgotPassword()` vrací odlišnou zprávu podle toho, zda e-mail existuje. Sjednocení zprávy mění UX, ponecháno jako produktové rozhodnutí.

### 30. Generické `search`/`orderBy` filtry s libovolným názvem sloupce (~74 kontrolerů)
**NEOPRAVENO — info.** Vzor `explode(':', $search)` → `where($searchString[0], ...)` a `orderBy($request->get('orderBy'), ...)`. Laravel query grammar identifikátor sloupce escapuje, takže nejde o klasickou SQL injection, ale umožňuje dotazovat/řadit dle libovolného (i citlivého) sloupce. Doporučení: whitelist povolených sloupců per resource — mimo rozsah bezpečného surgical fixu (dotýká se ~74 souborů, per-entita rozhodnutí).

### 31. Absolutní serverová cesta v API odpovědi filemanageru
**NEOPRAVENO — info, nízké riziko.** `FileManagerService::serializeRow()` vrací `storage_path(...)` — po fixu #4 (auth) je dopad jen mírný information disclosure pro přihlášené uživatele.

### 32. Chybí defense-in-depth proti PHP exekuci ve `storage/public`
**NEOPRAVENO — info, mimo repo.** Doporučeno na VPS (Apache+php-fpm config) přidat direktivu blokující PHP handler pod `storage/`/`public/storage/` jako druhou vrstvu obrany nad aplikační validací (fix #5).

---

## Shrnutí

| Závažnost | Nalezeno | Opraveno | Vyžaduje rozhodnutí | Jen info/doporučení |
|---|---|---|---|---|
| Critical | 9 | 9 | 0 | 0 |
| High | 6 | 5¹ | 0 | 1 (rozšíření na dalších ~30 kontrolerů) |
| Medium | 9 | 6 | 2 | 1 |
| Low/Info | 8 | 1 | 0 | 7 |

¹ Nález #14 opraven na reprezentativním vzorku ~15 controllerů, stejný systémový vzor zbývá v dalších ~30 (viz detail výše).

## Akce potřebné od vás před nasazením do produkce

1. **Nastavit `CORS_ALLOWED_ORIGINS`** v produkčním `server/.env` na skutečné produkční domény (nález #6) — bez tohoto CORS spadne zpět jen na localhost whitelist.
2. **Rozhodnout o IDOR na filemanager presetech** (#20) a **download/delete přílohách** (#21) — vyžaduje frontend změny nebo potvrzení, že jde o záměrně sdílený resource.
3. Zvážit dopracování zbylých ~30 controllerů se stejným site-scope vzorem (#14), prioritně `Invoice`/`Contract`/`PriceOffer`.
4. Před `npm run build` v `client/`, `game/`, `admin/` ověřit, že nová závislost `dompurify` se nainstalovala (`npm install`) a appky se buildí bez chyb — nebylo v rámci auditu spuštěno.

---
*Audit proveden 2026-09-22 pomocí 8 paralelních Claude Code subagentů (6 audit + 1 XSS fix + CORS fix v hlavní session). Žádné destruktivní operace (DB, git historie) nebyly provedeny.*
