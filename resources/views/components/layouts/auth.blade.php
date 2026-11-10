@props([
    'title' => 'Sign in',
    'headline' => ['Every space. Every gate.', 'One clear view.'],
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }} · {{ config('app.name', 'Parkir Sip') }}</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-white font-sans text-ink antialiased">
    <div class="flex min-h-screen">
        {{-- Form side --}}
        <div class="flex w-full flex-col bg-white px-6 sm:px-10 lg:w-[53.5%] lg:px-12">
            <header class="flex h-[104px] shrink-0 items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2.5">
                    <span class="grid size-9 shrink-0 place-items-center rounded-[10px] bg-brand text-white">
                        <x-icon name="parking" :stroke="2" class="size-[19px]" />
                    </span>
                    <span class="text-[19px] font-semibold tracking-[-0.01em] text-ink">Parkir Sip</span>
                </a>
            </header>

            <main class="flex flex-1 items-center justify-center py-6">
                <div class="w-full max-w-[430px]">
                    {{ $slot }}
                </div>
            </main>

            <footer class="flex shrink-0 items-center justify-end gap-2 pb-8 text-[10.5px] text-ink-muted">
                <a href="#" class="transition-colors hover:text-ink-soft">Privacy policy</a>
                <span class="text-line">·</span>
                <a href="#" class="transition-colors hover:text-ink-soft">Contact support</a>
            </footer>
        </div>

        {{-- Brand side --}}
        <aside class="relative hidden overflow-hidden bg-[#103b3d] lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-[46.5%] lg:flex-col lg:self-start">
            <div class="pointer-events-none absolute -left-28 top-1/3 size-[420px] rounded-full bg-[#2f827c]/20 blur-[110px]"></div>
            <div class="pointer-events-none absolute -right-28 -top-24 size-[380px] rounded-full bg-[#087f75]/25 blur-[100px]"></div>

            <div class="relative flex h-full flex-col overflow-y-auto px-12 pb-8 pt-10 [scrollbar-width:none] [@media(min-height:980px)]:pb-10 [@media(min-height:980px)]:pt-24 [&::-webkit-scrollbar]:hidden">
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-[#31585a] bg-[#1d484a]/70 px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[0.16em] text-[#8fc6ba]">
                    <x-icon name="router" class="size-3" />
                    Built for ESP32 parking systems
                </span>

                <h2 class="mt-7 text-[38px] font-semibold leading-[1.12] tracking-[-0.02em] text-[#f2f7f6] [@media(min-height:980px)]:mt-8">
                    @foreach ($headline as $line)
                        <span class="block">{{ $line }}</span>
                    @endforeach
                </h2>

                <p class="mt-4 max-w-[430px] text-[12.5px] leading-relaxed text-[#acc9c7] [@media(min-height:980px)]:mt-5">
                    Bring slot availability, gate controls, and device health together in one connected workspace.
                </p>

                {{-- Gate & vehicle illustration --}}
                <div class="relative mt-7 shrink-0 overflow-hidden rounded-2xl border border-[#31585a] bg-[#1d484a]/70 p-4 shadow-[0_24px_60px_-24px_rgba(0,0,0,0.55)] [@media(min-height:980px)]:mt-9">
                    <div class="pointer-events-none absolute -right-16 -top-20 size-44 rounded-full bg-[#2f827c]/20 blur-3xl"></div>

                    <div class="relative flex items-center justify-between gap-3">
                        <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-[#8fc6ba]">Gate A · Entry lane</p>

                        <span class="inline-flex items-center gap-1.5 text-[8.5px] font-medium uppercase tracking-[0.12em] text-[#7fa5a1]">
                            <span class="auth-scene-beacon size-1.5 rounded-full bg-[#8fc6ba]"></span>
                            Sensor aktif
                        </span>
                    </div>

                    <svg viewBox="0 0 420 230" class="relative mt-3 h-auto w-full" fill="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="auth-beam" x1="69" y1="14" x2="126" y2="14" gradientUnits="userSpaceOnUse">
                                <stop offset="0" stop-color="#d5b478" stop-opacity="0.45" />
                                <stop offset="1" stop-color="#d5b478" stop-opacity="0" />
                            </linearGradient>
                        </defs>

                        {{-- Road --}}
                        <rect x="12" y="196" width="396" height="26" rx="8" fill="#12373c" />
                        <line x1="22" y1="209" x2="398" y2="209" stroke="#2f827c" stroke-width="2" stroke-dasharray="12 10" opacity="0.45" />

                        {{-- Reader bollard beside the barrier, arrival side --}}
                        <rect x="116" y="172" width="8" height="26" rx="3" fill="#1f4d4d" stroke="#31585a" />
                        <circle cx="120" cy="176" r="3" fill="#35a08f" />

                        {{-- Gate pillar and arm --}}
                        <g>
                            <rect x="240" y="110" width="16" height="88" rx="6" fill="#1f4d4d" stroke="#31585a" />
                            <rect x="236" y="102" width="24" height="14" rx="5" fill="#255955" stroke="#31585a" />
                            <circle class="auth-scene-beacon" cx="248" cy="98" r="4" fill="#8fc6ba" />
                        </g>

                        <g transform="translate(244,164)">
                            <g class="auth-scene-arm">
                                <rect x="-112" y="-4" width="112" height="8" rx="4" fill="#dff0ea" />
                                <g fill="#35a08f">
                                    <rect x="-108" y="-3.5" width="12" height="7" rx="3" />
                                    <rect x="-87" y="-3.5" width="12" height="7" rx="3" />
                                    <rect x="-66" y="-3.5" width="12" height="7" rx="3" />
                                    <rect x="-45" y="-3.5" width="12" height="7" rx="3" />
                                    <rect x="-24" y="-3.5" width="12" height="7" rx="3" />
                                </g>
                                <circle cx="-112" cy="0" r="3" fill="#d5b478" />
                            </g>
                        </g>

                        {{-- Motorcyclist passing through --}}
                        <g transform="translate(0,165)">
                            <g class="auth-scene-car">
                                <polygon points="69,14 126,8 126,24" fill="url(#auth-beam)" />
                                <ellipse cx="40" cy="33" rx="36" ry="5" fill="#087f75" opacity="0.18" />

                                <g fill="#172c32">
                                    <circle cx="16" cy="26" r="7" />
                                    <circle cx="64" cy="26" r="7" />
                                </g>
                                <g fill="#91a5ae" opacity="0.55">
                                    <circle cx="16" cy="26" r="3" />
                                    <circle cx="64" cy="26" r="3" />
                                </g>

                                <path d="M16,26 L34,22 L54,20 L64,24" stroke="#e8f5f1" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                                <ellipse cx="40" cy="19" rx="13" ry="6.5" fill="#e8f5f1" />
                                <rect x="12" y="16" width="14" height="5" rx="2.5" fill="#e8f5f1" />
                                <path d="M52,14 L58,7" stroke="#e8f5f1" stroke-width="3" stroke-linecap="round" />

                                <circle cx="38" cy="5" r="4.5" fill="#e8f5f1" />
                                <path d="M36,9 Q44,12 50,18" stroke="#e8f5f1" stroke-width="5" stroke-linecap="round" fill="none" />

                                <circle cx="66" cy="15.5" r="3" fill="#d5b478" />
                                <circle cx="11" cy="17" r="2" fill="#d5b478" />
                            </g>
                        </g>
                    </svg>
                </div>

                {{-- Features --}}
                <div class="mt-8 grid shrink-0 grid-cols-3 gap-4 border-t border-[#31585a] pt-5 [@media(min-height:980px)]:mt-10">
                    @foreach ([
                        ['icon' => 'grid', 'title' => 'Slot visibility', 'copy' => 'Know which spaces are free in real time.'],
                        ['icon' => 'gate', 'title' => 'Gate control', 'copy' => 'Open and close entry gates remotely.'],
                        ['icon' => 'router', 'title' => 'Device health', 'copy' => 'Stay ahead of offline sensors.'],
                    ] as $feature)
                        <div>
                            <span class="grid size-7 place-items-center rounded-md border border-[#31585a] bg-[#1d484a] text-[#8fc6ba]">
                                <x-icon :name="$feature['icon']" class="size-3.5" />
                            </span>

                            <p class="mt-2 text-[10px] font-semibold text-[#e7efee]">{{ $feature['title'] }}</p>
                            <p class="mt-1 text-[9px] leading-relaxed text-[#7fa5a1]">{{ $feature['copy'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</body>
</html>
