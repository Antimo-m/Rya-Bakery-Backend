<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Incassi</span>
            <h1>Analisi</h1>
        </div>
    </x-slot>

    <section class="admin-grid stats-grid">
        <article class="admin-card"><span>Incasso giornaliero</span><strong>€ {{ number_format($todayTotal, 2, ',', '.') }}</strong><em>ordini pronti per il ritiro</em></article>
        <article class="admin-card"><span>Giorno selezionato</span><strong>€ {{ number_format($dayTotal, 2, ',', '.') }}</strong><em>{{ \Illuminate\Support\Carbon::parse($filters['day'])->format('d/m/Y') }}</em></article>
        <article class="admin-card"><span>Mese selezionato</span><strong>€ {{ number_format($monthTotal, 2, ',', '.') }}</strong><em>{{ $monthOrdersCount }} ordini pronti</em></article>
    </section>

    <section class="admin-ops-grid admin-ops-grid--single">
        <article class="admin-panel admin-panel--chart">
            <div>
                <span class="admin-kicker">Andamento</span>
                <h2>Incasso per fascia oraria</h2>
            </div>
            @php
                $maxHourlyRevenue = max(1, $hourlyRevenue->max('total'));
            @endphp
            <div class="admin-bar-chart" aria-label="Incasso orario di oggi">
                @foreach ($hourlyRevenue as $slot)
                    <span style="--bar-height: {{ max(6, ($slot['total'] / $maxHourlyRevenue) * 100) }}%">
                        <i></i>
                        <small>{{ $slot['label'] }}</small>
                    </span>
                @endforeach
            </div>
        </article>
    </section>

    <section class="admin-section-title">
        <div>
            <span class="admin-kicker">Dettaglio</span>
            <h2>Ordini del giorno selezionato</h2>
        </div>
        <form class="admin-filters admin-filters--compact admin-filters--inline admin-filters--analysis" method="GET" action="{{ route('admin.analysis.index') }}">
            <label>
                Giorno
                <span class="custom-date-field">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    <input name="day" type="text" inputmode="none" pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD" value="{{ $filters['day'] }}" readonly data-submit-on-select>
                </span>
            </label>
            <button class="admin-btn admin-btn--icon" type="submit" aria-label="Filtra analisi" title="Filtra">
                <i class="bi bi-funnel" aria-hidden="true"></i>
            </button>
            <a class="admin-btn secondary admin-btn--icon" href="{{ route('admin.analysis.index') }}" aria-label="Reset filtri analisi" title="Reset">
                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
            </a>
        </form>
    </section>

    <section class="analysis-detail-grid" aria-label="Dettaglio ordini del giorno">
        @forelse ($dayOrders as $order)
            <article class="analysis-order-card">
                <header>
                    <div>
                        <h3>{{ $order->customer_name }}</h3>
                        <small>{{ $order->slug }}</small>
                    </div>
                    <strong>€ {{ number_format($order->total_price, 2, ',', '.') }}</strong>
                </header>
                <x-admin.order-items-list :items="$order->items" compact />
                <footer>
                    <span><i class="bi bi-hash" aria-hidden="true"></i> Tavolo {{ $order->table_number }}</span>
                    <time datetime="{{ $order->delivered_at?->toIso8601String() }}"><i class="bi bi-bag-check" aria-hidden="true"></i> Pronto {{ $order->delivered_at?->format('H:i') }}</time>
                </footer>
            </article>
        @empty
            <div class="admin-empty-state">
                <i class="bi bi-bar-chart" aria-hidden="true"></i>
                <strong>Nessun ordine nel giorno selezionato</strong>
                <span>Scegli un altro giorno dal calendario.</span>
            </div>
        @endforelse
    </section>
</x-app-layout>
