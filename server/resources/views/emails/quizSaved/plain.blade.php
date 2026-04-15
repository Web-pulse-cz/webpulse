{{ $subject }}

Dobrý den, {{ $newsletter->addressing }},

Právě byl přidán nový kvíz s názvem "{{ $quiz->name }}".

Název:        {{ $quiz->name }}
Popis:        {!! strip_tags($quiz->description) !!}
Počet otázek: {{ $quiz->questions->count() }}

Zahrát si kvíz: {{ $quiz->url }}

---
© {{ date('Y') }} Web-pulse. Všechna práva vyhrazena.
