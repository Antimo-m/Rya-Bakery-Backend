<x-app-layout :title="$title">
    <x-slot name="header">
        <h1>{{ $product->exists ? 'Modifica prodotto' : 'Nuovo prodotto' }}</h1>
        <a class="admin-btn secondary" href="{{ route('admin.products.index') }}">Torna ai prodotti</a>
    </x-slot>

    <form class="admin-form" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label for="name">Nome</label>
                <input id="name" name="name" value="{{ old('name', $product->name) }}" required>
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="field">
                <label for="category">Categoria</label>
                <input id="category" name="category" list="categories" value="{{ old('category', $product->category) }}">
                <datalist id="categories">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="field">
                <label for="price">Prezzo</label>
                <input id="price" name="price" type="number" min="0.1" step="0.01" value="{{ old('price', $product->price) }}" required>
                <x-input-error :messages="$errors->get('price')" />
            </div>

            <div class="field">
                <label for="image">Immagine</label>
                <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp">
                <div class="image-preview">
                    <img data-image-preview src="{{ $product->image_url }}" alt="">
                    <span>{{ $product->image_path ? 'Anteprima immagine prodotto' : 'Placeholder prodotto' }}</span>
                </div>
                @if ($product->image_path)
                    <small>Immagine attuale: <a href="{{ $product->image_url }}" target="_blank" rel="noreferrer">apri anteprima</a></small>
                @endif
                <x-input-error :messages="$errors->get('image')" />
            </div>
        </div>

        <div class="field">
            <label for="description">Descrizione</label>
            <textarea id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="check-row">
            <label><input type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available))> Disponibile</label>
            <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Attivo</label>
        </div>

        <div class="admin-actions">
            <button class="admin-btn" type="submit">{{ $product->exists ? 'Salva modifiche' : 'Crea prodotto' }}</button>
        </div>
    </form>
</x-app-layout>
