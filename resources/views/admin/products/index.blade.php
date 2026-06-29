<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Catalogo</span>
            <h1>Prodotti</h1>
        </div>
        <a class="admin-btn admin-btn--icon-primary" href="{{ route('admin.products.create') }}" aria-label="Nuovo prodotto" title="Nuovo prodotto">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
        </a>
    </x-slot>

    <section class="product-data-grid" role="table" aria-label="Catalogo prodotti">
        <div class="product-data-grid__head" role="row">
            <span role="columnheader">Prodotto</span>
            <span role="columnheader">Categoria</span>
            <span role="columnheader">Prezzo</span>
            <span role="columnheader">Disponibilità</span>
            <span role="columnheader">Stato</span>
            <span role="columnheader">Azioni</span>
        </div>
        <div class="product-data-grid__body" role="rowgroup">
            @forelse ($products as $product)
                <article class="product-data-row" role="row">
                    <div class="product-data-row__identity" role="cell" data-label="Prodotto">
                        <img class="admin-thumb" src="{{ $product->image_url }}" alt="">
                        <div>
                            <strong>{{ $product->name }}</strong>
                            <small>{{ $product->slug }}</small>
                            @if ($product->specialBadges() !== [])
                                <div class="admin-special-badges" aria-label="Evidenze prodotto">
                                    @foreach ($product->specialBadges() as $badge)
                                        <span class="admin-special-badge {{ $badge['type'] }}">{{ $badge['label'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div role="cell" data-label="Categoria">{{ $product->category ?: 'Senza categoria' }}</div>
                    <div class="product-data-row__price" role="cell" data-label="Prezzo">€ {{ number_format($product->price, 2, ',', '.') }}</div>
                    <div role="cell" data-label="Disponibilità">
                        <span class="badge {{ $product->is_available ? 'on' : 'off' }}">{{ $product->is_available ? 'Disponibile' : 'Non disponibile' }}</span>
                    </div>
                    <div role="cell" data-label="Stato">
                        <span class="badge {{ $product->is_active ? 'on' : 'off' }}">{{ $product->is_active ? 'Attivo' : 'Non attivo' }}</span>
                    </div>
                    <div class="admin-actions" role="cell" data-label="Azioni">
                        <a class="admin-btn edit admin-btn--icon" href="{{ route('admin.products.edit', $product) }}" aria-label="Modifica {{ $product->name }}" title="Modifica">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Eliminare il prodotto {{ $product->name }}?">
                            @csrf
                            @method('DELETE')
                            <button class="admin-btn danger admin-btn--icon" type="submit" aria-label="Elimina {{ $product->name }}" title="Elimina">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="admin-empty-state">
                    <i class="bi bi-box-seam" aria-hidden="true"></i>
                    <strong>Nessun prodotto presente</strong>
                    <span>Aggiungi il primo prodotto al catalogo.</span>
                </div>
            @endforelse
        </div>
    </section>

    <x-admin.pagination :paginator="$products" label="Paginazione prodotti" />
</x-app-layout>
