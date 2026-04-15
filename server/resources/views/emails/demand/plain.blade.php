{{ $subject }}

Dobrý den,

@if($demand->service)
Na webu {{ env('CLIENT_URL') }} byla právě vytvořena nová poptávka na službu {{ $demand->service->name }}.
@else
Na webu {{ env('CLIENT_URL') }} byla právě vytvořena nová poptávka.
@endif

Jméno a příjmení: {{ $demand->fullname }}
E-mail:           {{ $demand->email }}
Telefon:          {{ $demand->fullPhone }}
@if($demand->service)
Služba:           {{ $demand->service->name }}
Navrhovaná cena:  {{ $demand->offer_price }}
@endif
@if($demand->url)
URL:              {{ $demand->url }}
@endif
Zpráva:           {{ $demand->text }}
Jazyk:            {{ $demand->locale }}

---
© {{ date('Y') }} Web-pulse. Všechna práva vyhrazena.
