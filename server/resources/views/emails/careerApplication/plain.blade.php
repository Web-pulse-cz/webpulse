{{ $subject }}

Dobrý den,
@if($type == 'client')
Děkujeme vám za podání žádosti o pracovní pozici {{ $careerApplication->career->name }}.

O jejím průběhu vás budeme informovat na této e-mailové adrese.
@endif
@if($type == 'admin')
Dovolujeme si vás upozornit, že v systému vznikla nová žádost o pracovní pozici {{ $careerApplication->career->name }}.

Níže zasíláme její podrobnosti:

Jméno: {{ $careerApplication->firstname }}
Příjmení: {{ $careerApplication->lastname }}
E-mail: {{ $careerApplication->email }}
Telefon: {{ $careerApplication->phone }}
Očekávaná mzda: {{ $careerApplication->salary_expectation }}
Datum nástupu: {{ $careerApplication->realAvailability }}

@endif

---
© {{ date('Y') }} Web-pulse. Všechna práva vyhrazena.
