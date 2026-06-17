<x-app-layout :title="$title">
    <x-slot name="header">
        <h1>Modifica ordine</h1>
        <a class="admin-btn secondary" href="{{ route('admin.orders.index') }}">Torna agli ordini</a>
    </x-slot>

    <form class="admin-form" method="POST" action="{{ route('admin.orders.update', $order) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="field">
                <label for="customer_name">Nome cliente</label>
                <input id="customer_name" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" required>
            </div>
            <div class="field">
                <label for="table_number">Tavolo</label>
                <input id="table_number" name="table_number" type="number" min="1" value="{{ old('table_number', $order->table_number) }}" required>
            </div>
            <div class="field">
                <label for="status">Stato</label>
                <select id="status" name="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $order->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="notes">Note</label>
                <input id="notes" name="notes" value="{{ old('notes', $order->notes) }}">
            </div>
        </div>

        <div class="order-items">
            <div class="order-items__header">
                <strong>Prodotti ordinati</strong>
                <span>Imposta quantita 0 per rimuovere una riga.</span>
            </div>

            @php
                $rows = $order->items->values();
                $extraRows = max(2, 5 - $rows->count());
            @endphp

            @foreach ($rows as $index => $item)
                <div class="order-item-row">
                    <select name="items[{{ $index }}][product_slug]">
                        @foreach ($products as $product)
                            <option value="{{ $product->slug }}" @selected($item->product->is($product))>{{ $product->name }} · € {{ number_format($product->price, 2, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <input name="items[{{ $index }}][quantity]" type="number" min="0" max="50" value="{{ $item->quantity }}">
                </div>
            @endforeach

            @for ($i = 0; $i < $extraRows; $i++)
                @php $index = $rows->count() + $i; @endphp
                <div class="order-item-row">
                    <select name="items[{{ $index }}][product_slug]">
                        <option value="">Aggiungi prodotto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->slug }}">{{ $product->name }} · € {{ number_format($product->price, 2, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <input name="items[{{ $index }}][quantity]" type="number" min="0" max="50" value="0">
                </div>
            @endfor
        </div>

        <div class="admin-actions">
            <button class="admin-btn" type="submit">Salva ordine</button>
        </div>
    </form>
</x-app-layout>
