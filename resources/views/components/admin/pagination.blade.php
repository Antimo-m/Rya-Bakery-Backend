@props(['paginator', 'label' => 'Paginazione'])

@if ($paginator->hasPages())
    <nav class="admin-pagination" aria-label="{{ $label }}">
        @if ($paginator->onFirstPage())
            <span class="admin-pagination__button is-disabled" aria-disabled="true" aria-label="Pagina precedente">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </span>
        @else
            <a class="admin-pagination__button" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Pagina precedente">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </a>
        @endif

        <span class="admin-pagination__button is-current" aria-current="page" aria-label="Pagina corrente">
            {{ $paginator->currentPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a class="admin-pagination__button" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Pagina successiva">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </a>
        @else
            <span class="admin-pagination__button is-disabled" aria-disabled="true" aria-label="Pagina successiva">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </span>
        @endif
    </nav>
@endif
