@if ($callbacks->hasPages())
    @if ($callbacks->onFirstPage())
        <button disabled aria-label="First Page">««</button>
        <button disabled aria-label="Previous Page">«</button>
    @else
        <button onclick="window.location.href='{{ $callbacks->url(1) }}'" aria-label="First Page">««</button>
        <button onclick="window.location.href='{{ $callbacks->previousPageUrl() }}'" aria-label="Previous Page">«</button>
    @endif
    @foreach ($callbacks->getUrlRange(max(1, $callbacks->currentPage() - 2), min($callbacks->lastPage(), $callbacks->currentPage() + 2)) as $page => $url)
        <span class="page-num {{ $callbacks->currentPage() == $page ? 'active' : '' }}" onclick="window.location.href='{{ $url }}'" aria-label="Page {{ $page }}">{{ $page }}</span>
    @endforeach
    @if ($callbacks->hasMorePages())
        <button onclick="window.location.href='{{ $callbacks->nextPageUrl() }}'" aria-label="Next Page">»</button>
        <button onclick="window.location.href='{{ $callbacks->url($callbacks->lastPage()) }}'" aria-label="Last Page">»»</button>
    @else
        <button disabled aria-label="Next Page">»</button>
        <button disabled aria-label="Last Page">»»</button>
    @endif
@endif