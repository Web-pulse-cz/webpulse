<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV</title>
</head>
<style>
    *, *::before, *::after {
        box-sizing: border-box;
        text-wrap: wrap;
        text-align: justify;
    }

    * {
        margin: 0;
    }

    @media (prefers-reduced-motion: no-preference) {
        html {
            interpolate-size: allow-keywords;
        }
    }

    body {
        line-height: 1.4;
        -webkit-font-smoothing: antialiased;
    }

    img, picture, video, canvas, svg {
        display: block;
        max-width: 100%;
    }

    input, button, textarea, select {
        font: inherit;
    }

    p, h1, h2, h3, h4, h5, h6 {
        overflow-wrap: break-word;
    }

    p {
        text-wrap: pretty;
    }

    h1, h2, h3, h4, h5, h6 {
        text-wrap: balance;
    }

    #root, #__next {
        isolation: isolate;
    }

    html, body {
        margin: 0;
        padding: 0;
        width: 210mm;
        height: 297mm;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 64mm;
        height: 297mm;
    }

    .wrapper {
        position: fixed;
        top: 0;
        margin-left: 64mm;
        width: calc(210mm - 64mm);
    }

    .height-fill {
        height: 37mm;
        border-bottom: 1px solid #101828FF;
    }

    .height-fill p {
        color: #45556CFF;
        font-size: 80px;
        font-weight: 64;
    }

    .height-fill h1 {
        color: #0F172BFF;
        font-size: 96px;
        font-weight: 700;
    }

    .block {
        padding-left: 16px;
        padding-right: 16px;
        padding-top: 0;
    }

    .infos {
        height: 297mm;
        border-right: 1px solid #101828FF;
        padding: 32px 64px;
    }

    h2 {
        color: #99A1AFFF;
        font-size: 56px;
    }

    h3 {
        color: #101828FF;
        font-size: 42px;
    }

    p, span, a {
        font-size: 32px;
    }

    a {
        color: #101828FF;
        text-decoration: none;
        display: block;
        margin-bottom: 8px;
    }

    .experience_or_education {
        margin-top: 16px;
        margin-bottom: 64px;
    }

    .date {
        color: #45556CFF;
        font-weight: 600;
        font-size: 36px;
    }

    .main-content {
        padding: 32px 64px;
    }

    .height-fill-main-content {
        padding-left: 64px;
    }

    .height-fill-main-content h1 {
        padding-top: 48px;
    }

    .content {
        margin-top: 12px;
        margin-bottom: 36px;
    }

    ul {
        padding-left: 64px;
    }

    li {
        margin-bottom: 8px;
        font-size: 32px;
    }
    .skill_group {
        margin-bottom: 24px;
    }
</style>
<body>
<main>
    <div class="sidebar">
        <div class="height-fill"></div>
        <div class="block infos">
            <div class="content">
                <h2>Kontaktní údaje</h2>
                @if ($biography->email)
                    <a href="mailto:{{ $biography->email }}">{{ $biography->email }}</a>
                @endif
                @if ($biography->phone_prefix && $biography->phone)
                    <a href="tel:{{ str_replace('+', '', $biography->phone_prefix) . trim($biography->phone) }}">{{ $biography->phone }}</a>
                @elseif($biography->phone)
                    <a href="tel:{{ trim($biography->phone) }}">{{ $biography->phone }}</a>
                @endif
                @if ($biography->linkedin)
                    <a href="{{ $biography->linkedin }}" target="_blank">{{ $biography->linkedin }}</a>
                @endif
                @if ($biography->website)
                    <a href="{{ $biography->website }}" target="_blank">{{ $biography->website }}</a>
                @endif
                @if ($biography->github)
                    <a href="{{ $biography->github }}" target="_blank">{{ $biography->github }}</a>
                @endif
                @if ($biography->address)
                    <p>{{ $biography->address }}</p>
                @endif
            </div>
            @if($biography->skills)
                <div class="content">
                    <h2>Dovednosti</h2>
                    @foreach($biography->skills as $i => $skill_group)
                        <div class="skill_group">
                            <h3>{{ $skill_group['name'] }}</h3>
                            <ul>
                                @foreach($skill_group['skills'] as $skill)
                                    <li>{{ $skill['name'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="wrapper">
        <div class="height-fill height-fill-main-content">
            <h1>{{ $user->firstname . ' ' . $user->lastname }}</h1>
            @if ($biography->job_title)
                <p>{{ $biography->job_title  }}</p>
            @endif
        </div>
        <div class="block main-content">
            @if($biography->summary)
                <div class="content">
                    <h2>Sourhn</h2>
                    <p>{{ $biography->summary }}</p>
                </div>
            @endif
            @if($biography->job_experiences)
                <div class="content">
                    <h2>Pracovní zkušenosti</h2>
                    @foreach($biography->job_experiences as $experience)
                        <div class="experience_or_education">
                            <h3>{{ $experience['position'] }} | {{ $experience['company'] }}</h3>
                            <p class="date">
                                {{ \Carbon\Carbon::parse($experience['start_date'])->format('m/Y') }}
                                -
                                @if($experience['end_date'])
                                    {{\Carbon\Carbon::parse($experience['end_date'])->format('m/Y')}}
                                @else
                                    současnost
                                @endif
                            </p>
                            <p>{{ $experience['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
            @if($biography->education)
                <div class="content">
                    <h2>Vzdělání</h2>
                    @foreach($biography->education as $education)
                        <div class="experience_or_education">
                            <h3>{{ $education['institution'] }}</h3>
                            <p class="date">
                                {{ \Carbon\Carbon::parse($education['start_date'])->format('m/Y') }}
                                -
                                @if($education['end_date'])
                                    {{\Carbon\Carbon::parse($education['end_date'])->format('m/Y')}}
                                @else
                                    současnost
                                @endif
                            </p>
                            <p>{{ $education['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
            @if($biography->about_me)
                <div class="content">
                    <h2>O mně</h2>
                    <p>{{ $biography->about_me }}</p>
                </div>
            @endif
        </div>
    </div>
</main>
</body>
</html>
