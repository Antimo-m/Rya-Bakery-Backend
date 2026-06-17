<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Catalogo</span>
            <h1>Prodotti</h1>
        </div>
        <a class="admin-btn" href="{{ route('admin.products.create') }}">Nuovo prodotto</a>
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
                                </div>
                            </div>
                        </td>
                        <td>{{ $product->category ?: 'Senza categoria' }}</td>
                        <td>€ {{ number_format($product->price, 2, ',', '.') }}</td>
                        <td><span class="badge {{ $product->is_available ? 'on' : 'off' }}">{{ $product->is_available ? 'Disponibile' : 'Non disponibile' }}</span></td>
                        <td><span class="badge {{ $product->is_active ? 'on' : 'off' }}">{{ $product->is_active ? 'Attivo' : 'Non attivo' }}</span></td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn secondary" href="{{ route('admin.products.edit', $product) }}">Modifica</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Eliminare il prodotto {{ $product->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-btn danger" type="submit">Elimina</button>
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

    <div style="margin-top: 18px">{{ $products->links() }}</div>
</x-app-layout>
