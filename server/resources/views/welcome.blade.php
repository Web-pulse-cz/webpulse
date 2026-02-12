<!DOCTYPE html>

<html class="light" lang="en"><head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>404 Page Not Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#3c83f6",
                        "primary-dark": "#2563eb",
                        "background-light": "#eff6ff", // Blue-50 as requested
                        "background-dark": "#1d2939",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "2xl": "1rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-white transition-colors duration-200">
<div class="relative flex min-h-screen w-full flex-col items-center justify-center overflow-hidden p-6">
    <!-- Large Decorative Background Number -->
    <div class="absolute inset-0 flex items-center justify-center overflow-hidden pointer-events-none select-none z-0">
<span class="text-[20rem] sm:text-[30rem] font-extrabold text-blue-100/50 dark:text-slate-800/30 leading-none tracking-tighter">
                404
            </span>
    </div>
    <div class="relative z-10 flex w-full max-w-3xl flex-col items-center gap-10 text-center">
        <!-- Illustration Area -->
        <div class="relative flex h-64 w-64 items-center justify-center rounded-full bg-white/60 dark:bg-slate-800/60 shadow-xl ring-1 ring-blue-100 dark:ring-slate-700 backdrop-blur-sm">
            <!-- Abstract "Lost Connection" Illustration using SVG shapes and colors -->
            <div class="relative w-40 h-40">
                <!-- Monitor Screen -->
                <div class="absolute inset-0 m-auto w-32 h-24 rounded-lg border-4 border-primary bg-blue-50 dark:bg-slate-900 flex items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-blue-300 dark:text-blue-700">dns</span>
                </div>
                <!-- Disconnected Plug -->
                <div class="absolute -right-6 top-1/2 -translate-y-1/2">
                    <span class="material-symbols-outlined text-5xl text-red-400 rotate-45 transform">power_off</span>
                </div>
                <!-- Floating Question Marks -->
                <div class="absolute -top-4 -left-2 animate-bounce" style="animation-duration: 3s;">
                    <span class="text-3xl font-bold text-primary">?</span>
                </div>
                <div class="absolute -bottom-4 -right-2 animate-bounce" style="animation-duration: 4s; animation-delay: 1s;">
                    <span class="text-2xl font-bold text-blue-300">?</span>
                </div>
            </div>
        </div>
        <!-- Content Area -->
        <div class="flex flex-col items-center gap-4 max-w-lg mx-auto">
            <div class="space-y-2">
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                    Oops! Something went wrong
                </h1>
                <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                    We can't seem to find the page you're looking for. It might have been removed, renamed, or is temporarily unavailable.
                </p>
            </div>
            <!-- Action Buttons -->
            <div class="mt-4 flex w-full flex-col gap-3 sm:flex-row sm:justify-center">
                <!-- Return Home Button (Solid Primary) -->
                <a class="group flex h-12 w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-primary px-6 text-sm font-bold text-white shadow-md shadow-blue-500/20 transition-all hover:bg-primary-dark hover:shadow-lg hover:shadow-blue-500/30 focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900" href="https://web-pulse.cz">
                    <span class="material-symbols-outlined text-[20px]">home</span>
                    <span>Return Home</span>
                </a>
            </div>
        </div>
    </div>
</div>
</body></html>
