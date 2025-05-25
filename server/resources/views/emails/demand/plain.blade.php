{{ $subject }}

Dobrý den,

Na webu {{ env('CLIENT_URL') }} byla právě vytvořena nová poptávka pro službu {{ $demand->service ? $demand->service->name : '-' }}.

Níže jsou detaily:

Jméno a příjmení: {{ $demand->fullname }}
E-mail: {{ $demand->email }}
Telefon: {{ $demand->fullPhone }}
Služba: {{ $demand->service ? $demand->service->name : '-' }}
Navrhovaná cena: {{ $demand->offered_price }}
Zpráva: {{ $demand->text }}
Jazyk: {{ $demand->locale }}

---
© {{ date('Y') }} Web-pulse. Všechna práva vyhrazena.
