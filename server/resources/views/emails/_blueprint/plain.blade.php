{{ $subject }}

Dobrý den,

Na webu {{ env('CLIENT_URL') }} byla právě vytvořena nová poptávka pro službu {{ $inquiry->service_name }}.

Níže jsou detaily:

Jméno a příjmení: {{ $inquiry->fullname }}
E-mail: {{ $inquiry->email }}
Telefon: {{ $inquiry->fullPhone }}
Služba: {{ $inquiry->service_name }}
Navrhovaná cena: {{ $inquiry->offered_price }}
Jazyk: {{ app()->getLocale() }}

---
© {{ date('Y') }} Web-pulse. Všechna práva vyhrazena.
