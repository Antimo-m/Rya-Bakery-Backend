<x-guest-layout title="Rya Bakery Admin | Login">
    <div class="admin-login-intro">
        <span>Area riservata</span>
        <h1>Accesso admin</h1>
        <p>Gestisci prodotti, ordini e storico operativo.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form class="admin-login-form" method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="field">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="field">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div>
            <label for="remember_me" class="admin-login-check">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Ricordami</span>
            </label>
        </div>

        <div class="admin-login-actions">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">
                    Password dimenticata?
                </a>
            @endif

            <button class="admin-btn" type="submit">Accedi</button>
        </div>
    </form>
</x-guest-layout>
