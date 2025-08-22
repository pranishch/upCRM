@if ($page_obj->hasPages())
    @if ($page_obj->onFirstPage())
        <button disabled>««</button>
        <button disabled>«</button>
    @else
        <button onclick="window.location.href='?page=1{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'">««</button>
        <button onclick="window.location.href='{{ $page_obj->previousPageUrl() }}{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'">«</button>
    @endif

    @for ($i = 1; $i <= $page_obj->lastPage(); $i++)
        @if ($i == $page_obj->currentPage())
            <span class="page-num active">{{ $i }}</span>
        @elseif ($i > $page_obj->currentPage() - 3 && $i < $page_obj->currentPage() + 3)
            <span class="page-num" onclick="window.location.href='?page={{ $i }}{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'">{{ $i }}</span>
        @endif
    @endfor

    @if ($page_obj->hasMorePages())
        <button onclick="window.location.href='{{ $page_obj->nextPageUrl() }}{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'">»</button>
        <button onclick="window.location.href='?page={{ $page_obj->lastPage() }}{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'">»»</button>
    @else
        <button disabled>»</button>
        <button disabled>»»</button>
    @endif

@endif
