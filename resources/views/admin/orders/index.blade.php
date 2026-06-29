<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Ritiro al banco</span>
            <h1>Ordini</h1>
        </div>
        <button class="admin-btn secondary admin-sound-toggle" type="button" data-order-sound-toggle aria-pressed="false">
            <i class="bi bi-bell-slash" aria-hidden="true" data-sound-icon></i>
            <span data-sound-label>Audio spento</span>
        </button>
    </x-slot>

    <section
        class="admin-order-board"
        data-realtime-orders
        data-accept-url-template="{{ route('admin.orders.accept', ['order' => '__SLUG__']) }}"
        data-cancel-url-template="{{ route('admin.orders.cancel', ['order' => '__SLUG__']) }}"
        data-edit-url-template="{{ route('admin.orders.edit', ['order' => '__SLUG__']) }}"
        data-live-url="{{ route('admin.orders.live') }}"
    >
        <div class="admin-order-list" data-orders-list>
            @forelse ($orders as $order)
                <article data-order-id="{{ $order->id }}" @class(['admin-order-card', 'is-aging' => $order->created_at->lte(now()->subMinutes(10))])>
                    <header class="admin-order-card__header">
                        <div>
                            <span class="badge {{ $order->status }}">{{ $order->statusLabel() }}</span>
                            <h2>{{ $order->customer_name }}</h2>
                            <small>{{ $order->slug }}</small>
                        </div>
                        <strong>€ {{ number_format($order->total_price, 2, ',', '.') }}</strong>
                    </header>
                    <div class="admin-order-card__meta">
                        <span><i class="bi bi-hash" aria-hidden="true"></i> Tavolo {{ $order->table_number }} · riferimento cliente</span>
                        <time datetime="{{ $order->created_at->toIso8601String() }}"><i class="bi bi-clock" aria-hidden="true"></i> {{ $order->created_at->format('d/m/Y H:i') }}</time>
                    </div>
                    <x-admin.order-items-list :items="$order->items" />
                    <footer class="admin-order-card__footer">
                        <span class="admin-order-card__pickup"><i class="bi bi-bag-check" aria-hidden="true"></i> Preparare per il ritiro al banco</span>
                        <div class="admin-actions">
                            @if ($order->status === \App\Models\Order::STATUS_RECEIVED)
                                <form method="POST" action="{{ route('admin.orders.accept', $order) }}" data-confirm="Prendere questo ordine in preparazione?">
                                    @csrf
                                    @method('PATCH')
                                    <button class="admin-btn success" type="submit" title="Accetta">
                                        <i class="bi bi-check-lg" aria-hidden="true"></i><span>Accetta</span>
                                    </button>
                                </form>
                            @endif
                            @if ($order->status === \App\Models\Order::STATUS_PENDING)
                                <form method="POST" action="{{ route('admin.orders.complete', $order) }}" data-confirm="Segnalare al cliente che l ordine e pronto per il ritiro al banco?">
                                    @csrf
                                    @method('PATCH')
                                    <button class="admin-btn success" type="submit" title="Pronto per il ritiro">
                                        <i class="bi bi-bag-check" aria-hidden="true"></i><span>Ordine pronto</span>
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" data-confirm="Annullare o rifiutare questo ordine? Sara spostato nello storico.">
                                @csrf
                                @method('PATCH')
                                <button class="admin-btn danger admin-btn--icon" type="submit" aria-label="Annulla ordine" title="Annulla">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                </button>
                            </form>
                            <a class="admin-btn edit admin-btn--icon" href="{{ route('admin.orders.edit', $order) }}" aria-label="Modifica ordine" title="Modifica">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </a>
                        </div>
                    </footer>
                </article>
            @empty
                <div class="admin-empty-state" data-orders-empty>
                    <i class="bi bi-receipt" aria-hidden="true"></i>
                    <strong>Nessun ordine in attesa</strong>
                    <span>Il banco è libero.</span>
                </div>
            @endforelse
        </div>
    </section>

    <x-admin.pagination :paginator="$orders" label="Paginazione ordini" />
</x-app-layout>
