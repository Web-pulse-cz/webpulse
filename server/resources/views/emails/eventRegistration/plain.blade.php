{{ $subject }}

Dobrý den,

Děkujeme vám za přihlášení na akci {{ $eventRegistration->event->name }}.

Níže jsou detaily:

Název akce: {{ $eventRegistration->event->name }}
Začátek akce: {{ \Illuminate\Support\Carbon::parse($eventRegistration->event->start_date)->format('d. m. Y H:i') }}
Konec akce: {{ \Illuminate\Support\Carbon::parse($eventRegistration->event->end_date)->format('d. m. Y H:i') }}
Místo konání: {{ $eventRegistration->event->place }}

Jméno a příjmení: {{ $eventRegistration->firstname }} {{ $eventRegistration->lastname }}
E-mail: {{ $eventRegistration->email }}
Telefon: {{ $eventRegistration->fullPhone }}
Poznámka: {{ $eventRegistration->note }}
Cena: {{ $eventRegistration->event->price }},- Kč

---
© {{ date('Y') }} Web-pulse. Všechna práva vyhrazena.
