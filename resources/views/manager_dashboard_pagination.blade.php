@if ($page_obj->hasPages())
    @if ($page_obj->hasPrevious())
        <button onclick="window.location.href='?page=1{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'" aria-label="First Page">««</button>
        <button onclick="window.location.href='?page={{ $page_obj->previousPage() }}{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'" aria-label="Previous Page">«</button>
    @else
        <button disabled aria-label="First Page">««</button>
        <button disabled aria-label="Previous Page">«</button>
    @endif
    @foreach ($page_obj->getPageRange(3) as $num)
        @if ($page_obj->currentPage() == $num)
            <span class="page-num active" aria-current="page">{{ $num }}</span>
        @elseif ($num > $page_obj->currentPage() - 3 && $num < $page_obj->currentPage() + 3)
            <span class="page-num" onclick="window.location.href='?page={{ $num }}{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'" aria-label="Page {{ $num }}">{{ $num }}</span>
        @endif
    @endforeach
    @if ($page_obj->hasNext())
        <button onclick="window.location.href='?page={{ $page_obj->nextPage() }}{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'" aria-label="Next Page">»</button>
        <button onclick="window.location.href='?page={{ $page_obj->lastPage() }}{{ $search_query ? '&q=' . urlencode($search_query) . '&search_field=' . $search_field : '' }}'" aria-label="Last Page">»»</button>
    @else
        <button disabled aria-label="Next Page">»</button>
        <button disabled aria-label="Last Page">»»</button>
    @endif
@endif
