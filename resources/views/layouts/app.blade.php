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
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/scss/admin.scss', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased admin-body">
        <div class="admin-shell">
            <header class="admin-mobile-bar">
                <a class="admin-mobile-brand" href="{{ route('admin.dashboard') }}">
                    <img src="{{ asset('RyaBakery.png') }}" alt="">
                    <span>Rya Bakery & Café</span>
                </a>
                <button class="admin-menu-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Apri menu" title="Apri menu" data-admin-menu-toggle>
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
            </header>
            <button class="admin-sidebar-backdrop" type="button" aria-label="Chiudi menu" data-admin-menu-close></button>
            <aside class="admin-sidebar" id="admin-sidebar">
                <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                    <span class="admin-brand-mark">
                        <img src="{{ asset('RyaBakery.png') }}" alt="">
                    </span>
                    <span>
                        <strong>Rya Bakery</strong>
                        <small>Gestionale ordini</small>
                    </span>
                </a>

                <nav class="admin-nav" aria-label="Navigazione admin">
                    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard</a>
                    <a class="{{ request()->routeIs('admin.analysis.*') ? 'active' : '' }}" href="{{ route('admin.analysis.index') }}"><i class="bi bi-bar-chart" aria-hidden="true"></i> Analisi</a>
                    <a class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><i class="bi bi-box-seam" aria-hidden="true"></i> Prodotti</a>
                    <a class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><i class="bi bi-receipt" aria-hidden="true"></i> Ordini in arrivo</a>
                    <a class="{{ request()->routeIs('admin.order-history.*') ? 'active' : '' }}" href="{{ route('admin.order-history.index') }}"><i class="bi bi-clock-history" aria-hidden="true"></i> Storico ordini</a>
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
                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                        Annulla
                    </button>
                    <button class="admin-btn success" type="button" data-confirm-submit>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        Conferma
                    </button>
                </div>
            </form>
        </dialog>
    </body>
</html>
