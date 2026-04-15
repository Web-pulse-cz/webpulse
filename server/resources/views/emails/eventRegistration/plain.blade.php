{{ $subject }}

Dobrý den,

Děkujeme za přihlášení na akci {{ $eventRegistration->event->name }}.

Informace o akci:
  Název:    {{ $eventRegistration->event->name }}
  Začátek:  {{ \Illuminate\Support\Carbon::parse($eventRegistration->event->start_date)->format('d. m. Y H:i') }}
  Konec:    {{ \Illuminate\Support\Carbon::parse($eventRegistration->event->end_date)->format('d. m. Y H:i') }}
  Místo:    {{ $eventRegistration->event->place }}

Údaje účastníka:
  Jméno:    {{ $eventRegistration->firstname }} {{ $eventRegistration->lastname }}
  E-mail:   {{ $eventRegistration->email }}
  Telefon:  {{ $eventRegistration->phone }}
  Poznámka: {{ $eventRegistration->note }}
  Cena:     {{ $eventRegistration->event->price }},- Kč

---
© {{ date('Y') }} Web-pulse. Všechna práva vyhrazena.
