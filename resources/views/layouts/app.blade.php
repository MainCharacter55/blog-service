<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'blog-service') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    darkMode: 'class',
                    theme: {
                        extend: {},
                    },
                };
            </script>
        @endif
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-950 text-slate-100 antialiased">
        <div class="relative">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.22),_transparent_40%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.14),_transparent_35%)]"></div>
            <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-4 py-6 sm:px-6 lg:px-8">
                <header class="sticky top-2 z-30 flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-white/10 bg-slate-950/85 px-5 py-4 backdrop-blur">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-400/15 text-cyan-300 ring-1 ring-cyan-300/30">
                            B
                        </div>
                        <div>
                            <p class="text-lg font-semibold tracking-tight">blog-service</p>
                            <p class="text-xs text-slate-400">Simple blog platform</p>
                        </div>
                    </a>

                    <nav class="flex items-center gap-3 text-sm">
                        <a href="{{ route('home') }}" class="rounded-full px-4 py-2 text-slate-300 transition hover:bg-white/10 hover:text-white">Home</a>
                        <a href="{{ route('posts.recent') }}" class="rounded-full px-4 py-2 text-slate-300 transition hover:bg-white/10 hover:text-white">Recent</a>
                        <a href="{{ route('posts.popular') }}" class="rounded-full px-4 py-2 text-slate-300 transition hover:bg-white/10 hover:text-white">Popular</a>

                        <div class="relative" data-nav-menu-wrapper>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-full border border-white/15 bg-white/5 px-2 py-1 text-slate-200 transition hover:border-cyan-400/40 hover:bg-cyan-400/10 hover:text-cyan-100"
                                data-nav-menu-button
                                aria-expanded="false"
                                aria-controls="header-account-menu"
                            >
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/15 bg-slate-900 text-slate-100">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                                        <path d="M12 12c2.485 0 4.5-2.015 4.5-4.5S14.485 3 12 3 7.5 5.015 7.5 7.5 9.515 12 12 12Z" fill="currentColor"/>
                                        <path d="M12 13.5c-4.142 0-7.5 2.91-7.5 6.5h15c0-3.59-3.358-6.5-7.5-6.5Z" fill="currentColor"/>
                                    </svg>
                                </span>
                                <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-slate-400" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <div
                                id="header-account-menu"
                                class="absolute right-0 z-40 mt-2 hidden min-w-56 overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl shadow-black/40"
                                data-nav-menu
                            >
                                @auth
                                    <div class="border-b border-white/10 px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.2em] text-cyan-300">Logged in</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-100">{{ auth()->user()->name }}</p>
                                    </div>

                                    <button type="button" disabled class="flex w-full cursor-not-allowed items-center gap-3 px-4 py-3 text-left text-sm text-slate-500">
                                        <span>⚙</span>
                                        Settings (coming soon)
                                    </button>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm text-rose-200 transition hover:bg-rose-400/10 hover:text-rose-100">
                                            <span>↪</span>
                                            Logout
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-200 transition hover:bg-cyan-400/10 hover:text-cyan-200">
                                        <span>→</span>
                                        Login
                                    </a>
                                    <a href="{{ route('register') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-200 transition hover:bg-cyan-400/10 hover:text-cyan-200">
                                        <span>＋</span>
                                        Register
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </nav>
                </header>

                @if (session('status'))
                    <div class="mt-5 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif

                <main class="flex-1 py-8">
                    @yield('content')
                </main>

                <footer class="mt-8 rounded-3xl border border-cyan-400/30 bg-slate-950/85 px-6 py-8 backdrop-blur">
                    <div class="grid gap-8 md:grid-cols-3">
                        <section>
                            <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-300">External Links</h2>
                            <ul class="mt-4 space-y-2 text-sm text-slate-300">
                                <li><a href="https://github.com" target="_blank" rel="noreferrer" class="transition hover:text-cyan-200">GitHub</a></li>
                                <li><a href="https://www.linkedin.com" target="_blank" rel="noreferrer" class="transition hover:text-cyan-200">LinkedIn</a></li>
                                <li><a href="https://x.com" target="_blank" rel="noreferrer" class="transition hover:text-cyan-200">X</a></li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-300">System Map</h2>
                            <ul class="mt-4 space-y-2 text-sm text-slate-300">
                                <li><a href="{{ route('home') }}" class="transition hover:text-cyan-200">Home</a></li>
                                <li><a href="{{ route('posts.recent') }}" class="transition hover:text-cyan-200">Recent</a></li>
                                <li><a href="{{ route('posts.popular') }}" class="transition hover:text-cyan-200">Popular</a></li>
                                @guest
                                    <li><a href="{{ route('login') }}" class="transition hover:text-cyan-200">Login</a></li>
                                    <li><a href="{{ route('register') }}" class="transition hover:text-cyan-200">Register</a></li>
                                @endguest
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-300">Receive Data Streams</h2>
                            <p class="mt-4 text-sm text-slate-300">Periodic system updates and product notes.</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.18em] text-slate-500">Mail service unavailable</p>
                            <p class="mt-4 border-b border-cyan-400/40 pb-2 text-sm text-slate-300">Email Address</p>
                        </section>
                    </div>

                    <div class="mt-8 border-t border-white/10 pt-4 text-sm text-slate-400">
                        <p>&copy; {{ now()->year }} blog-service. All rights reserved.</p>
                    </div>
                </footer>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const menuWrapper = document.querySelector('[data-nav-menu-wrapper]');

                if (!menuWrapper) {
                    return;
                }

                const button = menuWrapper.querySelector('[data-nav-menu-button]');
                const menu = menuWrapper.querySelector('[data-nav-menu]');

                if (!button || !menu) {
                    return;
                }

                const closeMenu = () => {
                    menu.classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                };

                button.addEventListener('click', (event) => {
                    event.stopPropagation();

                    const isOpen = !menu.classList.contains('hidden');

                    closeMenu();

                    if (!isOpen) {
                        menu.classList.remove('hidden');
                        button.setAttribute('aria-expanded', 'true');
                    }
                });

                menu.addEventListener('click', (event) => {
                    event.stopPropagation();
                });

                document.addEventListener('click', () => {
                    closeMenu();
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMenu();
                    }
                });
            });
        </script>

        <div class="fixed inset-0 z-50 hidden flex items-center justify-center px-4 py-6" data-guest-auth-warning>
            <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" data-guest-auth-warning-overlay></div>

            <div class="relative w-full max-w-lg overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950 shadow-2xl shadow-black/60">
                <button
                    type="button"
                    class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 text-slate-300 transition hover:border-cyan-400/40 hover:bg-cyan-400/10 hover:text-cyan-100"
                    data-guest-auth-warning-close
                    aria-label="Close warning"
                >
                    ×
                </button>

                <div class="p-6 sm:p-8">
                    <p class="text-xs uppercase tracking-[0.28em] text-cyan-300">Guest action</p>
                    <h2 class="mt-3 pr-12 text-2xl font-semibold text-white" data-guest-auth-warning-title>ログインが必要です</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-300" data-guest-auth-warning-message>この操作を続けるにはログインまたは会員登録が必要です。</p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-cyan-400 px-5 py-3 text-sm font-medium text-slate-950 transition hover:bg-cyan-300" data-guest-auth-warning-login>
                            ログインへ進む
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/10" data-guest-auth-warning-register>
                            会員登録へ進む
                        </a>
                        <button type="button" class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/10" data-guest-auth-warning-close>
                            閉じる
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
