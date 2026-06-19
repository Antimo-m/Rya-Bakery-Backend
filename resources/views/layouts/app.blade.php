<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Rya Bakery Admin' }}</title>
        <link rel="icon" type="image/png" href="{{ asset('RyaBakery.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|fraunces:600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/scss/admin.scss', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased admin-body">
        <div class="admin-shell">
            <header class="admin-mobile-bar">
                <a class="admin-mobile-brand" href="{{ route('admin.dashboard') }}">Rya Bakery & Café</a>
                <button class="admin-menu-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Apri menu" title="Apri menu" data-admin-menu-toggle>
                    <iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon>
                </button>
            </header>
            <button class="admin-sidebar-backdrop" type="button" aria-label="Chiudi menu" data-admin-menu-close></button>
            <aside class="admin-sidebar" id="admin-sidebar">
                <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                    <span class="admin-brand-mark">R</span>
                    <span>
                        <strong>Rya Bakery</strong>
                        <small>Gestionale ordini</small>
                    </span>
                </a>

                <nav class="admin-nav" aria-label="Navigazione admin">
                    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><iconify-icon icon="solar:chart-square-bold-duotone"></iconify-icon> Dashboard</a>
                    <a class="{{ request()->routeIs('admin.analysis.*') ? 'active' : '' }}" href="{{ route('admin.analysis.index') }}"><iconify-icon icon="solar:graph-new-up-bold-duotone"></iconify-icon> Analisi</a>
                    <a class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><iconify-icon icon="solar:donut-bitten-bold-duotone"></iconify-icon> Prodotti</a>
                    <a class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><iconify-icon icon="solar:bell-bing-bold-duotone"></iconify-icon> Ordini</a>
                    <a class="{{ request()->routeIs('admin.order-history.*') ? 'active' : '' }}" href="{{ route('admin.order-history.index') }}"><iconify-icon icon="solar:archive-bold-duotone"></iconify-icon> Storico ordini</a>
                    <a class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}"><iconify-icon icon="solar:settings-bold-duotone"></iconify-icon> Impostazioni</a>
                </nav>

                <div class="admin-user">
                    <details>
                        <summary>
                            <span>{{ auth()->user()->name }}</span>
                            <small>{{ auth()->user()->email }}</small>
                        </summary>
                        <div class="admin-user-menu">
                            <a href="{{ route('admin.settings.edit') }}">Impostazioni</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">Logout</button>
                            </form>
                        </div>
                    </details>
                </div>
            </aside>

            <main class="admin-main">
                @isset($header)
                    <header class="admin-page-header">
                        {{ $header }}
                    </header>
                @endisset

                @if (session('success'))
                    <div class="admin-alert success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="admin-alert error">
                        Controlla i campi evidenziati: {{ $errors->first() }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <dialog class="confirm-dialog" data-confirm-dialog>
            <form method="dialog" class="confirm-dialog__panel">
                <h2 data-confirm-title>Conferma azione</h2>
                <p data-confirm-message>Vuoi continuare?</p>
                <div class="confirm-dialog__actions">
                    <button class="admin-btn secondary" type="button" data-confirm-cancel>
                        <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                        Annulla
                    </button>
                    <button class="admin-btn success" type="button" data-confirm-submit>
                        <iconify-icon icon="solar:check-square-bold-duotone"></iconify-icon>
                        Conferma
                    </button>
                </div>
            </form>
        </dialog>
    </body>
</html>
