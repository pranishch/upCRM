@if ($page_obj->hasPages())
    <div id="pagination">
        @if ($page_obj->onFirstPage())
            <button disabled aria-label="First Page">««</button>
            <button disabled aria-label="Previous Page">«</button>
        @else
            <button onclick="window.loadPage(1)" aria-label="First Page">««</button>
            <button onclick="window.loadPage({{ $page_obj->currentPage() - 1 }})" aria-label="Previous Page">«</button>
        @endif

        @foreach ($page_obj->getUrlRange(1, $page_obj->lastPage()) as $page => $url)
            @if ($page >= $page_obj->currentPage() - 2 && $page <= $page_obj->currentPage() + 2)
                <span class="page-num {{ $page == $page_obj->currentPage() ? 'active' : '' }}"
                      onclick="{{ $page != $page_obj->currentPage() ? 'window.loadPage(' . $page . ')' : '' }}"
                      aria-label="Page {{ $page }}">{{ $page }}</span>
            @endif
        @endforeach

        @if ($page_obj->hasMorePages())
            <button onclick="window.loadPage({{ $page_obj->currentPage() + 1 }})" aria-label="Next Page">»</button>
            <button onclick="window.loadPage({{ $page_obj->lastPage() }})" aria-label="Last Page">»»</button>
        @else
            <button disabled aria-label="Next Page">»</button>
            <button disabled aria-label="Last Page">»»</button>
        @endif
    </div>
@endif