<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
</head>
<style>
    *, *::before, *::after {
        box-sizing: border-box;
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
        line-height: 1.5;
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
        font-size: 12px;
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 42mm;
        height: 297mm;
        background-color: #022F2EFF;
        color: #F0FDFAFF;
    }

    .sidebar a {
        display: block;
        color: #F0FDFAFF;
        text-decoration: none;
        padding: 5px 10px;
    }

    .wrapper {
        position: fixed;
        top: 0;
        margin-left: 42mm;
        width: calc(210mm - 42mm);
        padding: 10mm;
        height: 100%;
    }

    .height-fill {
        height: 17mm;
    }
</style>
<body>
<main>
    <div class="sidebar">
        <div class="height-fill"></div>
        <div class="informations">
            @if ($biography->email)
                <a href="mailto:{{ $biography->email }}">{{ $biography->email }}</a>
            @endif
            @if ($biography->phone)
                <a href="tel:{{ $biography->phone }}">{{ $biography->phone }}</a>
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
        </div>
    </div>
    <div class="wrapper">
        <div class="height-fill">
            <p>{{ $user->firstname }}</p>
            <p>{{ $user->lastname }}</p>
        </div>
    </div>
</main>
</body>
</html>
