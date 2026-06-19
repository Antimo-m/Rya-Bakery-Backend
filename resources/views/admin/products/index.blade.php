<x-app-layout :title="$title">
    @php
        $currentPage = $products->currentPage();
        $lastPage = $products->lastPage();
    @endphp

    <x-slot name="header">
        <div>
            <span class="admin-kicker">Catalogo</span>
            <h1>Prodotti</h1>
        </div>
        <a class="admin-btn admin-btn--icon-primary" href="{{ route('admin.products.create') }}" aria-label="Nuovo prodotto" title="Nuovo prodotto">
            <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
        </a>
    </x-slot>

    <section class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Prodotto</th>
                    <th>Categoria</th>
                    <th>Prezzo</th>
                    <th>Disponibilita</th>
                    <th>Stato</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <img class="admin-thumb" src="{{ $product->image_url }}" alt="">
                                <div>
                                    <strong>{{ $product->name }}</strong><br>
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
                        </td>
                        <td>{{ $product->category ?: 'Senza categoria' }}</td>
                        <td>€ {{ number_format($product->price, 2, ',', '.') }}</td>
                        <td><span class="badge {{ $product->is_available ? 'on' : 'off' }}">{{ $product->is_available ? 'Disponibile' : 'Non disponibile' }}</span></td>
                        <td><span class="badge {{ $product->is_active ? 'on' : 'off' }}">{{ $product->is_active ? 'Attivo' : 'Non attivo' }}</span></td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn edit admin-btn--icon" href="{{ route('admin.products.edit', $product) }}" aria-label="Modifica {{ $product->name }}" title="Modifica">
                                    <iconify-icon icon="solar:pen-2-bold-duotone"></iconify-icon>
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Eliminare il prodotto {{ $product->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-btn danger admin-btn--icon" type="submit" aria-label="Elimina {{ $product->name }}" title="Elimina">
                                        <iconify-icon icon="solar:trash-bin-minimalistic-bold-duotone"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Nessun prodotto presente.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if ($products->hasPages())
        <footer class="admin-products-footer">
            <nav class="admin-pagination" aria-label="Paginazione prodotti">
            @if ($products->onFirstPage())
                <span class="admin-pagination__button is-disabled" aria-disabled="true">
                    <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                </span>
            @else
                <a class="admin-pagination__button" href="{{ $products->previousPageUrl() }}" rel="prev" aria-label="Pagina precedente">
                    <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                </a>
            @endif

            <span class="admin-pagination__summary" aria-current="page">Pagina {{ $currentPage }} di {{ $lastPage }}</span>

            @if ($products->hasMorePages())
                <a class="admin-pagination__button" href="{{ $products->nextPageUrl() }}" rel="next" aria-label="Pagina successiva">
                    <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                </a>
            @else
                <span class="admin-pagination__button is-disabled" aria-disabled="true">
                    <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                </span>
            @endif
            </nav>
        </footer>
    @endif
</x-app-layout>
