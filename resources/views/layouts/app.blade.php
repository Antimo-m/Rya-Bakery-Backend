<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Rya Bakery Admin' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/scss/admin.scss', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased admin-body">
        <div class="admin-shell">
            <aside class="admin-sidebar">
                <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                    <span class="admin-brand-mark">R</span>
                    <span>
                        <strong>Rya Bakery</strong>
                        <small>Backoffice ordini</small>
                    </span>
                </a>

                <nav class="admin-nav" aria-label="Navigazione admin">
                    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span>⌂</span> Dashboard</a>
                    <a class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><span>◧</span> Prodotti</a>
                    <a class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><span>◌</span> Ordini</a>
                    <a class="{{ request()->routeIs('admin.order-history.*') ? 'active' : '' }}" href="{{ route('admin.order-history.index') }}"><span>↺</span> Storico ordini</a>
                </nav>

                <div class="admin-user">
                    <details>
                        <summary>
                            <span>{{ auth()->user()->name }}</span>
                            <small>{{ auth()->user()->email }}</small>
                        </summary>
                        <div class="admin-user-menu">
                            <a href="{{ route('admin.profile.edit') }}">Profilo utente</a>
                            <a href="{{ route('admin.profile.edit') }}">Impostazioni</a>
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
                    <button class="admin-btn secondary" type="button" data-confirm-cancel>Annulla</button>
                    <button class="admin-btn danger" type="button" data-confirm-submit>Conferma</button>
                </div>
            </form>
        </dialog>
    </body>
</html>
