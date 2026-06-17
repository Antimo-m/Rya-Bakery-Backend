<x-app-layout title="Rya Bakery Admin | Profilo utente">
    <x-slot name="header">
        <h1>Profilo utente</h1>
    </x-slot>

    <div class="admin-grid">
            <div class="admin-form">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="admin-form">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="admin-form">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
    </div>
</x-app-layout>
