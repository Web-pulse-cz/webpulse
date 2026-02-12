{{ $subject }}

Dobrý den, {{ $newsletter->addressing }},

Právě byl přidán nový kvíz s názvem "{{ $quiz->name }}". Níže naleznete jeho základní informace:

Název kvízu: {{ $quiz->title }}
Popis: {!! $quiz->description !!}
Počet otázek: {{ $quiz->questions->count() }}

---
© {{ date('Y') }} Web-pulse. Všechna práva vyhrazena.
